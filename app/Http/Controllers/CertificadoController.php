<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CertificadoController extends Controller
{
    private function getBancosDisponiveis(): array
    {
        return [
            'cm_anapu'     => 'Câmara de Anapu',
            'cm_carnaubal' => 'Câmara de Carnaubal',
            'cm_jucas'     => 'Câmara de Jucás',
        ];
    }

    /**
     * Renderiza a View de Gestão de Certificados com suporte a procuração
     */
    public function index(Request $request, string $banco = 'cm_jucas')
    {
        $bancosDisponiveis = $this->getBancosDisponiveis();

        if (!array_key_exists($banco, $bancosDisponiveis)) {
            $banco = 'cm_jucas';
        }

        $cnpjEntidade = config("database.connections.{$banco}.entidade");

        $certificados = [];
        try {
            $certificados = DB::connection($banco)
                ->table('esocial.certificados')
                ->get();
        } catch (\Throwable $e) {
            Log::error("Erro ao consultar certificados no banco {$banco}: " . $e->getMessage());
        }

        // Mapeia arquivos físicos no storage
        $arquivosStorage = [];
        $dir = storage_path("app/certificados/{$banco}");
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $arquivosStorage[] = $file;
                }
            }
        }

        return view('certificados.index', [
            'banco'             => $banco,
            'bancosDisponiveis' => $bancosDisponiveis,
            'cnpjEntidade'      => $cnpjEntidade,
            'certificados'      => $certificados,
            'arquivosStorage'   => $arquivosStorage,
            'darkMode'          => $request->cookie('dark_mode') === '1',
        ]);
    }

    /**
     * Realiza o upload garantindo Apenas 1 Certificado ativo por entidade
     */
    public function upload(Request $request, string $banco = 'cm_jucas')
    {
        $bancosDisponiveis = $this->getBancosDisponiveis();
        if (!array_key_exists($banco, $bancosDisponiveis)) {
            $banco = 'cm_jucas';
        }

        $request->validate([
            'tipo_titular' => 'required|in:entidade,procuracao',
            'documento'    => 'nullable|required_if:tipo_titular,procuracao|string',
            'certificado'  => 'required|file|max:2048',
            'senha'        => 'nullable|string',
        ], [
            'tipo_titular.in'       => 'Selecione um tipo de titular válido.',
            'documento.required_if' => 'O documento do procurador é obrigatório.',
            'documento.string'      => 'O documento deve ser um texto válido.',
            'certificado.required'  => 'O arquivo do certificado é obrigatório.',
            'certificado.max'       => 'O arquivo não pode exceder 2MB.',
            'senha.string'          => 'A senha deve ser um texto válido.',
        ]);

        // Validação manual de extensão
        $file = $request->file('certificado');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['pfx', 'p12'])) {
            return redirect()->back()->with('error', 'O arquivo deve ser do tipo .pfx ou .p12.');
        }

        // 1. CNPJ sempre é o CNPJ da Entidade
        $cnpjEntidade = config("database.connections.{$banco}.entidade");
        $cnpjSanitizado = preg_replace('/[^0-9]/', '', $cnpjEntidade ?? '');

        if (strlen($cnpjSanitizado) !== 14) {
            return redirect()->back()->with('error', 'CNPJ da entidade não está configurado corretamente em config/database.php.');
        }

        // 2. Lógica de Procurador vs Entidade para tpinsc / nrinsc
        $nrInsc = null;
        $tpInsc = null;

        if ($request->tipo_titular === 'procuracao') {
            $docProcurador = preg_replace('/[^0-9]/', '', $request->input('documento', ''));

            if (strlen($docProcurador) === 14) {
                $nrInsc = $docProcurador;
                $tpInsc = 1; // 1 = CNPJ no eSocial
            } elseif (strlen($docProcurador) === 11) {
                $nrInsc = $docProcurador;
                $tpInsc = 2; // 2 = CPF no eSocial
            } else {
                return redirect()->back()->with('error', 'O documento do procurador deve ter 11 (CPF) ou 14 (CNPJ) dígitos.');
            }
        }

        try {
            $db = DB::connection($banco);
            $dir = storage_path("app/certificados/{$banco}");

            // =========================================================================
            // REGRA: Apenas 1 certificado por entidade. Limpa anteriores (Disco e DB)
            // =========================================================================
            if (is_dir($dir)) {
                $arquivos = glob("{$dir}/*.*");
                foreach ($arquivos as $arquivo) {
                    if (file_exists($arquivo)) {
                        unlink($arquivo);
                    }
                }
            } else {
                mkdir($dir, 0755, true);
            }

            // Exclui registros anteriores no banco
            $db->table('esocial.certificados')->delete();

            // Salva o novo arquivo nomeado padronizado com o CNPJ da entidade
            $fileName = "{$cnpjSanitizado}_" . time() . ".pfx";
            if (!$file->move($dir, $fileName)) {
                throw new \Exception("Falha ao mover o arquivo para o servidor.");
            }

            // Senha salva em texto puro (sem criptografia)
            $senhaPura = $request->filled('senha') ? $request->input('senha') : null;

            // Insere o novo registro único
            $db->table('esocial.certificados')->insert([
                'cnpj'         => $cnpjSanitizado,
                'tpinsc'       => $tpInsc,
                'nrinsc'       => $nrInsc,
                'senha'        => $senhaPura,
                'criado_por'   => 1,
                'criado_em'    => now(),
                'alterado_por' => 1,
                'alterado_em'  => now(),
            ]);

            return redirect()->route('certificados.index', ['banco' => $banco])
                ->with('success', 'Certificado digital atualizado com sucesso!');
        } catch (\Throwable $e) {
            Log::error("Erro no upload do certificado ({$banco}): " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao processar upload: ' . $e->getMessage());
        }
    }

    /**
     * Faz download do certificado via documento da entidade
     */
    public function download(Request $request, string $banco = 'cm_jucas')
    {
        $bancosDisponiveis = $this->getBancosDisponiveis();
        if (!array_key_exists($banco, $bancosDisponiveis)) {
            $banco = 'cm_jucas';
        }

        $doc = preg_replace('/[^0-9]/', '', $request->query('doc', ''));
        if (empty($doc)) {
            return redirect()->route('certificados.index', ['banco' => $banco])
                ->with('error', 'Documento não informado para download.');
        }

        $dir = storage_path("app/certificados/{$banco}");
        if (!is_dir($dir)) {
            return redirect()->route('certificados.index', ['banco' => $banco])
                ->with('error', 'Diretório de certificados não encontrado.');
        }

        $files = glob("{$dir}/{$doc}_*.pfx");
        if (empty($files)) {
            return redirect()->route('certificados.index', ['banco' => $banco])
                ->with('error', 'Arquivo do certificado não encontrado no servidor.');
        }

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        return response()->download($files[0], "certificate.pfx", [
            'Content-Type' => 'application/x-pkcs12',
        ]);
    }

    /**
     * Exclui registro do banco e arquivos físicos
     */
    public function destroy(Request $request, string $banco = 'cm_jucas')
    {
        $bancosDisponiveis = $this->getBancosDisponiveis();
        if (!array_key_exists($banco, $bancosDisponiveis)) {
            $banco = 'cm_jucas';
        }

        $doc = preg_replace('/[^0-9]/', '', $request->input('documento', ''));
        if (empty($doc)) {
            return redirect()->back()->with('error', 'Documento não informado para exclusão.');
        }

        try {
            // 1. Exclui do Banco
            DB::connection($banco)
                ->table('esocial.certificados')
                ->where('cnpj', $doc)
                ->delete();

            // 2. Exclui arquivos físicos correspondentes
            $dir = storage_path("app/certificados/{$banco}");
            if (is_dir($dir)) {
                $files = glob("{$dir}/{$doc}_*.pfx");
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }

            return redirect()->back()->with('success', 'Certificado digital excluído com sucesso!');
        } catch (\Throwable $e) {
            Log::error("Erro ao excluir certificado ({$banco}): " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir certificado: ' . $e->getMessage());
        }
    }
}
