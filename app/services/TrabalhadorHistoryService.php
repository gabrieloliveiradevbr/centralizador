<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\Connection;

class TrabalhadorHistoryService
{
    /**
     * Mapeamento de status do eSocial para texto amigável e estilização visual.
     */
    private const STATUS_MAP = [
        'P' => [
            'texto' => 'Processado com sucesso',
            'tipo'  => 'success',
            'cor'   => 'emerald',
            'icone' => 'ri-checkbox-circle-line'
        ],
        'E' => [
            'texto' => 'Erro de processamento',
            'tipo'  => 'error',
            'cor'   => 'rose',
            'icone' => 'ri-error-warning-line'
        ],
        'A' => [
            'texto' => 'Aguardando processamento',
            'tipo'  => 'warning',
            'cor'   => 'amber',
            'icone' => 'ri-time-line'
        ],
        'R' => [
            'texto' => 'Rejeitado pelo eSocial',
            'tipo'  => 'error',
            'cor'   => 'rose',
            'icone' => 'ri-close-circle-line'
        ],
    ];

    /**
     * Busca o registro do historico e o registro correspondente na tabela do evento usando o idevento.
     *
     * @param string $banco Nome da conexão do banco de dados.
     * @param string $idevento O idevento do eSocial (ex: "ID1042937690000002021120211554000001").
     * @return Collection
     */
    public function getHistorico(string $banco, string $idevento): Collection
    {
        $db = DB::connection($banco);
        $idevento = trim($idevento);

        if (empty($idevento)) {
            return collect();
        }

        try {
            // 1. SELECT * FROM esocial.historico WHERE idevento = :idevento
            $historicoRecords = $db->table('esocial.historico')
                ->where('idevento', $idevento)
                ->orderBy('criado_em', 'ASC')
                ->get();

            if ($historicoRecords->isEmpty()) {
                return collect();
            }

            // Pega o nome da tabela do evento (ex: 'S1000' -> 's1000')
            $primeiroHist = $historicoRecords->first();
            $tabelaEvento = strtolower(trim((string) $primeiroHist->evento));

            // 2. SELECT * FROM esocial.{evento} WHERE idevento = :idevento
            $dadosEvento = null;
            if (!empty($tabelaEvento)) {
                try {
                    $dadosEvento = $db->table("esocial.{$tabelaEvento}")
                        ->where('idevento', $idevento)
                        ->first();
                } catch (\Throwable $e) {
                    // Caso a tabela específica do evento não exista ou ocorra erro
                    $dadosEvento = null;
                }
            }

            // 3. Monta e formata o resultado combinado
            return $this->formatResults($historicoRecords, $dadosEvento, $tabelaEvento);

        } catch (\Throwable $e) {
            return collect();
        }
    }

    /**
     * Formata o resultado consolidado para exibição na interface.
     */
    private function formatResults(Collection $historicoRecords, ?object $dadosEvento, string $tabelaEvento): Collection
    {
        return $historicoRecords->map(function ($item) use ($dadosEvento, $tabelaEvento) {
            $code = trim(strtoupper((string) ($item->status ?? '')));
            $statusInfo = self::STATUS_MAP[$code] ?? [
                'texto' => $item->status ?: 'Status Desconhecido',
                'tipo'  => 'default',
                'cor'   => 'gray',
                'icone' => 'ri-information-line'
            ];

            $eventoCode = strtoupper((string) ($item->evento ?? $tabelaEvento));
            $meta = EsocialPhaseService::getEventMetadata($eventoCode);

            return [
                'historico_id'  => $item->id ?? null,
                'idevento'      => $item->idevento ?? null,
                'evento'        => $eventoCode,
                'titulo'        => $meta['titulo'] ?? $eventoCode,
                'status_codigo' => $code,
                'status'        => $statusInfo['texto'],
                'status_tipo'   => $statusInfo['tipo'],
                'status_cor'    => $statusInfo['cor'],
                'status_icone'  => $statusInfo['icone'],
                'data'          => $item->criado_em ? date('d/m/Y H:i:s', strtotime($item->criado_em)) : 'N/A',
                'protocolo'     => $item->protocolo ?: null,
                'nr_recibo'     => $item->nr_recibo ?: null,
                'cnpj'          => $item->cnpj ?: null,
                'message'       => $item->message ?: null,
                // Dados detalhados retornados do SELECT * na tabela do evento
                'dados_evento'  => $dadosEvento ? (array) $dadosEvento : null,
            ];
        });
    }
}
