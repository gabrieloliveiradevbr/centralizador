<?php

namespace App\Services;

class EsocialPhaseService
{
    /**
     * Retorna metadados didáticos sobre eventos do eSocial.
     */
    public static function getEventMetadata(string $tabela): array
    {
        $tabela = strtolower($tabela);
        $map = [
            's1000' => [
                'titulo' => 'S-1000: Informações do Empregador',
                'descricao' => 'Evento de cadastro inicial do empregador/contribuinte. Define as características da empresa.'
            ],
            's1010' => [
                'titulo' => 'S-1010: Tabela de Rubricas',
                'descricao' => 'Define as rubricas (verbas) utilizadas na folha de pagamento, como salários, horas extras e descontos.'
            ],
            's1020' => [
                'titulo' => 'S-1020: Tabela de Lotações Tributárias',
                'descricao' => 'Define a estrutura organizacional e a tributação de cada setor da empresa.'
            ],
            's2200' => [
                'titulo' => 'S-2200: Cadastramento Inicial / Admissão',
                'descricao' => 'Evento fundamental para informar a admissão do trabalhador e seus dados contratuais.'
            ],
            's2210' => [
                'titulo' => 'S-2210: Comunicação de Acidente de Trabalho (CAT)',
                'descricao' => 'Informa a ocorrência de acidentes de trabalho ou doenças profissionais.'
            ],
            's2220' => [
                'titulo' => 'S-2220: Monitoramento da Saúde do Trabalhador',
                'descricao' => 'Registra os ASOs (Atestados de Saúde Ocupacional) do trabalhador.'
            ],
            's2240' => [
                'titulo' => 'S-2240: Condições Ambientais do Trabalho',
                'descricao' => 'Informa a exposição do trabalhador a agentes nocivos para fins de aposentadoria especial.'
            ],
            's1200' => [
                'titulo' => 'S-1200: Remuneração do Trabalhador',
                'descricao' => 'Informa a remuneração mensal do trabalhador, detalhando as rubricas pagas.'
            ],
            's1210' => [
                'titulo' => 'S-1210: Pagamentos de Rendimentos',
                'descricao' => 'Informa a data e o valor do pagamento efetuado ao trabalhador.'
            ],
            's3000' => [
                'titulo' => 'S-3000: Exclusão de Eventos',
                'descricao' => 'Utilizado para excluir eventos enviados anteriormente que contenham erros.'
            ],
        ];

        return $map[$tabela] ?? [
            'titulo' => 'Evento ' . strtoupper($tabela),
            'descricao' => 'Informações relacionadas ao evento ' . strtoupper($tabela) . ' do eSocial.'
        ];
    }

    /**
     * Define a categoria oficial do evento/tabela no eSocial.
     */
    public static function getCategoriaPorTabela(string $tabela): string
    {
        $nomeTabela = strtolower($tabela);

        // 1. Evento S-3000 (Exclusão)
        if (preg_match('/^s_?3000(_|$)/', $nomeTabela) || str_contains($nomeTabela, 'exclusao') || str_contains($nomeTabela, 'sem_efeito')) {
            return 'Evento S-3000 (Exclusão)';
        }

        // 2. Eventos de SST (Saúde e Segurança do Trabalho: S-2210, S-2220, S-2240)
        if (preg_match('/^s_?22(10|20|40)(_|$)/', $nomeTabela) || str_contains($nomeTabela, 'sst') || str_contains($nomeTabela, 'cat') || str_contains($nomeTabela, 'aso')) {
            return 'Eventos de SST';
        }

        // 3. Eventos de Tabela (S-1000 a S-1080)
        if (preg_match('/^s_?10\d{2}(_|$)/', $nomeTabela) || str_contains($nomeTabela, 'tabela') || str_contains($nomeTabela, 'rubrica') || str_contains($nomeTabela, 'lotacao')) {
            return 'Eventos de Tabela';
        }

        // 4. Eventos Não Periódicos (S-2190, S-2200, S-2205, S-2206, S-2230, S-2299, S-2300, etc.)
        if (preg_match('/^s_?2\d{3}(_|$)/', $nomeTabela) || str_contains($nomeTabela, 'vinculo') || str_contains($nomeTabela, 'afastamento') || str_contains($nomeTabela, 'admissao') || str_contains($nomeTabela, 'desligamento')) {
            return 'Eventos Não Periódicos';
        }

        // 5. Eventos Periódicos (Folha de Pagamento: S-1200, S-1202, S-1207, S-1210, S-1299, S-5001)
        if (preg_match('/^s_?(12\d{2}|5\d{3})(_|$)/', $nomeTabela) || str_contains($nomeTabela, 'remuneracao') || str_contains($nomeTabela, 'folha') || str_contains($nomeTabela, 'pagamento')) {
            return 'Eventos Periódicos (Folha)';
        }

        return 'Outras Tabelas / Logs';
    }

    /**
     * Retorna a lista ordenada e estruturada das categorias do eSocial.
     */
    public static function agruparTabelasPorFase(array $tabelas): array
    {
        $agrupado = [
            'Eventos de Tabela' => [
                'icone' => 'ri-table-line',
                'badge' => 'S-1000 a S-1080',
                'cor' => 'blue',
                'tabelas' => [],
            ],
            'Eventos Não Periódicos' => [
                'icone' => 'ri-user-shared-line',
                'badge' => 'S-2190 a S-2420',
                'cor' => 'indigo',
                'tabelas' => [],
            ],
            'Eventos de SST' => [
                'icone' => 'ri-heart-pulse-line',
                'badge' => 'S-2210 / S-2220 / S-2240',
                'cor' => 'emerald',
                'tabelas' => [],
            ],
            'Evento S-3000 (Exclusão)' => [
                'icone' => 'ri-delete-bin-line',
                'badge' => 'S-3000',
                'cor' => 'rose',
                'tabelas' => [],
            ],
            'Eventos Periódicos (Folha)' => [
                'icone' => 'ri-money-dollar-circle-line',
                'badge' => 'S-1200 a S-1299',
                'cor' => 'amber',
                'tabelas' => [],
            ],
            'Outras Tabelas / Logs' => [
                'icone' => 'ri-folder-settings-line',
                'badge' => 'Sistema',
                'cor' => 'gray',
                'tabelas' => [],
            ],
        ];

        foreach ($tabelas as $item) {
            $categoria = self::getCategoriaPorTabela($item->table_name);
            if (isset($agrupado[$categoria])) {
                $agrupado[$categoria]['tabelas'][] = $item;
            }
        }

        // Retorna apenas categorias que possuem tabelas no banco de dados ativo
        return array_filter($agrupado, fn($grupo) => !empty($grupo['tabelas']));
    }
}
