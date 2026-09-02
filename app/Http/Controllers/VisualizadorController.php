<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TableQueryService;
use App\Services\EsocialPhaseService;
use App\Services\TrabalhadorHistoryService;
use App\Repositories\PostgresMetadataRepository;
use App\DTOs\DashboardData;

class VisualizadorController extends Controller
{
    public function __construct(
        private PostgresMetadataRepository $metadataRepo
    ) {}

    private function getBancosDisponiveis(): array
    {
        return [
            'pgsql_anapu' => 'Câmara de Anapu',
            'pgsql_carnaubal' => 'Câmara de Carnaubal',
            'pgsql_jucas' => 'Câmara de Jucás',
        ];
    }

    public function index(Request $request, TableQueryService $tableQueryService, $banco = 'pgsql_jucas', $schema = 'esocial', $tabela = null)
    {
        try {
            $bancosDisponiveis = $this->getBancosDisponiveis();

            // Segurança do Banco
            if (!array_key_exists($banco, $bancosDisponiveis) || !config("database.connections.{$banco}")) {
                $banco = 'pgsql_jucas';
            }

            // Sanitização rigorosa
            $schema = preg_replace('/[^a-zA-Z0-9_]/', '', $schema ?? '') ?: 'esocial';
            $tabela = $tabela ? preg_replace('/[^a-zA-Z0-9_]/', '', $tabela) : null;

            $db = DB::connection($banco);

            // Sidebar Geral via Repositório
            $menuSchemas = $this->metadataRepo->getSchemasAndTables($db)->groupBy('table_schema');

            if (!$tabela) {
                if (isset($menuSchemas[$schema]) && $menuSchemas[$schema]->isNotEmpty()) {
                    $tabelasDisponiveis = $menuSchemas[$schema]->pluck('table_name')->toArray();
                    $tabela = in_array('s1000', $tabelasDisponiveis) ? 's1000' :
                             (in_array('s2200', $tabelasDisponiveis) ? 's2200' :
                             $menuSchemas[$schema]->first()->table_name);
                }
            }

            // Processa os dados da tabela
            $queryResult = [
                'dados' => null,
                'colunas' => [],
                'primaryKey' => null,
                'termoBusca' => $request->input('search'),
                'sortColuna' => $request->input('sort'),
                'direction' => $request->input('direction', 'asc'),
                'filtrosColuna' => [],
                'colunaDuplicada' => null,
            ];

            if ($tabela) {
                $queryResult = $tableQueryService->buildAndPaginate($db, $schema, $tabela, $request);

                if (isset($queryResult['redirect_page'])) {
                    return redirect()->route('visualizar', array_merge($request->query(), [
                        'banco' => $banco,
                        'schema' => $schema,
                        'tabela' => $tabela,
                        'page' => $queryResult['redirect_page']
                    ]));
                }
            }

            // Contexto eSocial
            $menuFasesEsocial = [];
            $eventTitle = null;
            $eventDesc = null;

            if ($schema === 'esocial') {
                $tabelasEsocial = $db->table('information_schema.tables')
                    ->select('table_name')
                    ->where('table_schema', 'esocial')
                    ->orderBy('table_name')
                    ->get();

                $menuFasesEsocial = EsocialPhaseService::agruparTabelasPorFase($tabelasEsocial->toArray());

                if ($tabela) {
                    $meta = EsocialPhaseService::getEventMetadata($tabela);
                    $eventTitle = $meta['titulo'];
                    $eventDesc = $meta['descricao'];
                }
            }

            // Unifica tudo no DTO
            $dashboardData = new DashboardData(
                banco: $banco,
                schema: $schema,
                tabela: $tabela,
                colunas: $queryResult['colunas'],
                dados: $queryResult['dados'],
                primaryKey: $queryResult['primaryKey'],
                termoBusca: $queryResult['termoBusca'],
                sortColuna: $queryResult['sortColuna'],
                direction: $queryResult['direction'],
                filtrosColuna: $queryResult['filtrosColuna'],
                colunaDuplicada: $queryResult['colunaDuplicada'],
                menuSchemas: $menuSchemas,
                menuFasesEsocial: $menuFasesEsocial,
                bancosDisponiveis: $bancosDisponiveis,
                darkMode: $request->cookie('dark_mode') === '1',
                eventTitle: $eventTitle,
                eventDescription: $eventDesc
            );

            return view('dashboard', array_merge(
                ['dashboard' => $dashboardData],
                $dashboardData->toArray() // Mantém compatibilidade com variáveis soltas na View
            ));

        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao carregar dados da tabela: ' . $e->getMessage());
            return redirect()->route('visualizar', ['banco' => $banco, 'schema' => $schema]);
        }
    }

    /**
     * Atualiza um registro específico na tabela.
     */
    public function update(Request $request, string $banco, string $schema, string $tabela)
    {
        try {
            $bancosDisponiveis = $this->getBancosDisponiveis();
            if (!array_key_exists($banco, $bancosDisponiveis)) {
                return redirect()->back()->with('error', 'Banco inválido.');
            }

            $db = DB::connection($banco);
            $tabelaCompleta = "{$schema}.{$tabela}";

            $primaryKey = $request->input('_primary_key_name');
            $primaryKeyValue = $request->input('_primary_key_value');

            if (!$primaryKey || !$primaryKeyValue) {
                return redirect()->back()->with('error', 'Identificador do registro não encontrado.');
            }

            $dadosAtualizacao = $request->except(['_token', '_method', '_primary_key_name', '_primary_key_value']);

            foreach ($dadosAtualizacao as $coluna => $valor) {
                if ($valor === '') {
                    $dadosAtualizacao[$coluna] = null;
                }
            }

            $db->table($tabelaCompleta)
                ->where($primaryKey, $primaryKeyValue)
                ->update($dadosAtualizacao);

            return redirect()->back()->with('success', 'Registro atualizado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao atualizar registro: ' . $e->getMessage());
        }
    }

    /**
     * Remove um registro da tabela.
     */
    public function destroy(Request $request, string $banco, string $schema, string $tabela)
    {
        try {
            $bancosDisponiveis = $this->getBancosDisponiveis();
            if (!array_key_exists($banco, $bancosDisponiveis)) {
                return redirect()->back()->with('error', 'Banco inválido.');
            }

            $db = DB::connection($banco);
            $tabelaCompleta = "{$schema}.{$tabela}";

            $primaryKey = $request->input('_primary_key_name');
            $primaryKeyValue = $request->input('_primary_key_value');

            if (!$primaryKey || !$primaryKeyValue) {
                return redirect()->back()->with('error', 'Identificador do registro não encontrado.');
            }

            $db->table($tabelaCompleta)
                ->where($primaryKey, $primaryKeyValue)
                ->delete();

            return redirect()->back()->with('success', 'Registro excluído com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao excluir registro: ' . $e->getMessage());
        }
    }

    /**
     * Retorna o histórico de eventos de um trabalhador em formato JSON.
     */
    public function historicoTrabalhador(Request $request, string $banco, string $identificador, TrabalhadorHistoryService $historyService)
    {
        try {
            $bancosDisponiveis = $this->getBancosDisponiveis();
            if (!array_key_exists($banco, $bancosDisponiveis)) {
                return response()->json(['error' => 'Banco inválido.'], 400);
            }

            $historico = $historyService->getHistorico($banco, $identificador);

            return response()->json([
                'success' => true,
                'data' => $historico
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao carregar histórico: ' . $e->getMessage()
            ], 500);
        }
    }
}
