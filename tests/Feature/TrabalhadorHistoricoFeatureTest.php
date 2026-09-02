<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrabalhadorHistoricoFeatureTest extends TestCase
{
    public function test_historico_route_returns_json()
    {
        $response = $this->get('/pgsql_jucas/trabalhador/06801364367/historico');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'historico_id',
                    'evento',
                    'titulo',
                    'status',
                    'status_codigo',
                    'status_tipo',
                    'status_cor',
                    'data',
                    'protocolo',
                    'nr_recibo',
                    'message'
                ]
            ]
        ]);
        $response->assertJson(['success' => true]);
    }
}
