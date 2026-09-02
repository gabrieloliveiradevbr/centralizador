<?php

namespace App\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;

class PostgresMetadataRepository
{
    /**
     * Busca schemas e tabelas permitidos para navegação.
     */
    public function getSchemasAndTables(Connection $db): Collection
    {
        return $db->table('information_schema.tables')
            ->select('table_schema', 'table_name')
            ->whereIn('table_schema', ['esocial', 'public', 'auditoria', 'ponto', 'rh', 'recadastramento'])
            ->orderBy('table_schema')
            ->orderBy('table_name')
            ->get();
    }

    /**
     * Busca todas as colunas de uma tabela específica.
     */
    public function getColumns(Connection $db, string $schema, string $tabela): array
    {
        return $db->table('information_schema.columns')
            ->where('table_schema', $schema)
            ->where('table_name', $tabela)
            ->pluck('column_name')
            ->toArray();
    }

    /**
     * Descobre a chave primária real da tabela.
     */
    public function getPrimaryKey(Connection $db, string $schema, string $tabela): ?string
    {
        return $db->table('information_schema.table_constraints as tc')
            ->join('information_schema.key_column_usage as kcu', function ($join) {
                $join->on('tc.constraint_name', '=', 'kcu.constraint_name')
                    ->on('tc.table_schema', '=', 'kcu.table_schema')
                    ->on('tc.table_name', '=', 'kcu.table_name');
            })
            ->where('tc.table_schema', $schema)
            ->where('tc.table_name', $tabela)
            ->where('tc.constraint_type', 'PRIMARY KEY')
            ->value('kcu.column_name');
    }
}
