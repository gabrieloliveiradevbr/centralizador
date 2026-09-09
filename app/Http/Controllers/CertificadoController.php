<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
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

        // Mapeia arquivos físicos no storage para evitar dependência de finfo
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
            'arquivosStorage'    => $arquivosStorage,
            'darkMode'          => $request->cookie('dark_mode') === '1',
        ]);
    }

    /**
     * Realiza o upload do certificado digital (Titular ou Procurador)
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
            'tipo_titular.in' => 'Selecione um tipo de titular válido.',
            'documento.required_if' => 'O documento do procurador é obrigatório.',
            'documento.string' => 'O documento deve ser um texto válido.',
            'certificado.required' => 'O arquivo do certificado é obrigatório.',
            'certificado.max' => 'O arquivo não pode exceder 2MB.',
            'senha.string' => 'A senha deve ser um texto válido.',
        ]);

        // Validação manual de extensão para evitar crash de php_fileinfo
        $file = $request->file('certificado');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['pfx', 'p12'])) {
            return redirect()->back()->with('error', 'O arquivo deve ser do tipo .pfx ou .p12.');
        }

        // Define documento base
        if ($request->tipo_titular === 'entidade') {
            $documento = config("database.connections.{$banco}.entidade");
        } else {
            $documento = $request->input('documento');
        }

        $docSanitizado = preg_replace('/[^0-9]/', '', $documento ?? '');
        $len = strlen($docSanitizado);

        if ($len !== 11 && $len !== 14) {
            return redirect()->back()->with('error', 'O documento deve conter 11 (CPF) ou 14 (CNPJ) dígitos.');
        }

        try {
            $dir = storage_path("app/certificados/{$banco}");
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new \Exception("Não foi possível criar o diretório de armazenamento.");
                }
            }

            $fileName = "{$docSanitizado}_" . time() . ".pfx";
            if (!$file->move($dir, $fileName)) {
                throw new \Exception("Falha ao mover o arquivo para o servidor.");
            }

            $senha = $request->filled('senha')
                ? $request->input('senha')
                : null;


            $db = DB::connection($banco);
            $exists = $db->table('esocial.certificados')->where('cnpj', $docSanitizado)->exists();

            if ($exists) {
                $db->table('esocial.certificados')->where('cnpj', $docSanitizado)->update([
                    'senha'        => $senha,
                    'alterado_por' => 1,
                    'alterado_em'  => now(),
                ]);
            } else {
                $db->table('esocial.certificados')->insert([
                    'cnpj'         => $docSanitizado,
                    'senha'        => $senha,
                    'criado_por'   => 1,
                    'criado_em'    => now(),
                    'alterado_por' => 1,
                    'alterado_em'  => now(),
                ]);
            }

            return redirect()->route('certificados.index', ['banco' => $banco])
                ->with('success', 'Certificado digital salvo com sucesso!');
        } catch (\Throwable $e) {
            Log::error("Erro no upload do certificado ({$banco}): " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao processar upload: ' . $e->getMessage());
        }
    }

    /**
     * Faz download do certificado via documento
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

        // Busca arquivo que comece com o documento
        $files = glob("{$dir}/{$doc}_*.pfx");
        if (empty($files)) {
            return redirect()->route('certificados.index', ['banco' => $banco])
                ->with('error', 'Arquivo do certificado não encontrado para este documento.');
        }

        // Retorna o arquivo mais recente
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        return response()->download($files[0], "certificado_{$doc}.pfx", [
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
                    unlink($file);
                }
            }

            return redirect()->back()->with('success', 'Certificado digital excluído com sucesso!');
        } catch (\Throwable $e) {
            Log::error("Erro ao excluir certificado ({$banco}): " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir certificado: ' . $e->getMessage());
        }
    }
}
