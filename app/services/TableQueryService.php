<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Connection;
use App\Repositories\PostgresMetadataRepository;

class TableQueryService
{
    public function __construct(
        private PostgresMetadataRepository $metadataRepo
    ) {}

    public function buildAndPaginate(Connection $db, string $schema, string $tabela, Request $request): array
    {
        // 1. Obtém as colunas da tabela via Repositório
        $colunas = $this->metadataRepo->getColumns($db, $schema, $tabela);

        $tabelaCompleta = "{$schema}.{$tabela}";
        $query = $db->table($tabelaCompleta);

        $termoBusca = $request->input('search');
        $sortColuna = $request->input('sort');
        $direction = strtolower($request->input('direction')) === 'desc' ? 'desc' : 'asc';
        $filtrosColuna = array_filter($request->input('filters', []) ?? []);
        $colunaDuplicada = $request->input('duplicate_column');

        // 2. Detecção de Registros Duplicados
        if ($colunaDuplicada && in_array($colunaDuplicada, $colunas)) {
            $query->whereIn($colunaDuplicada, function ($sub) use ($tabelaCompleta, $colunaDuplicada) {
                $sub->select($colunaDuplicada)
                    ->from($tabelaCompleta)
                    ->whereNotNull($colunaDuplicada)
                    ->groupBy($colunaDuplicada)
                    ->havingRaw('COUNT(*) > 1');
            });

            // Se o usuário não definiu ordenação explícita, agrupa por coluna duplicada
            if (!$sortColuna) {
                $query->orderBy($colunaDuplicada, 'asc');
            }
        }

        // 3. Ordenação
        if ($sortColuna && in_array($sortColuna, $colunas)) {
            $query->orderBy($sortColuna, $direction);
        }

        // 4. Busca Global
        if (!empty($termoBusca) && !empty($colunas)) {
            $query->where(function ($q) use ($colunas, $termoBusca) {
                foreach ($colunas as $coluna) {
                    $q->orWhereRaw("CAST(\"{$coluna}\" AS TEXT) ILIKE ?", ["%{$termoBusca}%"]);
                }
            });
        }

        // 5. Filtros por Coluna
        foreach ($filtrosColuna as $col => $val) {
            if (in_array($col, $colunas) && $val !== '') {
                $query->whereRaw("CAST(\"{$col}\" AS TEXT) ILIKE ?", ["%{$val}%"]);
            }
        }

        // 6. Checagem de redirecionamento para última página (DESC)
        if ($request->input('direction') === 'desc' && !$request->has('page')) {
            $perPage = 15;
            $totalRegistros = (clone $query)->count();
            $ultimaPagina = (int) ceil($totalRegistros / $perPage);

            if ($ultimaPagina > 1) {
                return ['redirect_page' => $ultimaPagina];
            }
        }

        // 7. Paginação final
        $dados = $query->paginate(15)->appends($request->query());

        return [
            'dados' => $dados,
            'colunas' => $colunas,
            'primaryKey' => $this->metadataRepo->getPrimaryKey($db, $schema, $tabela),
            'termoBusca' => $termoBusca,
            'sortColuna' => $sortColuna,
            'direction' => $direction,
            'filtrosColuna' => $filtrosColuna,
            'colunaDuplicada' => $colunaDuplicada,
        ];
    }
}
