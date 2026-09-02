<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TrabalhadorHistoryService;

class TrabalhadorHistoryServiceTest extends TestCase
{
    public function test_get_historico_returns_events_for_worker()
    {
        $service = new TrabalhadorHistoryService();
        $results = $service->getHistorico('pgsql_jucas', '06801364367');

        $this->assertNotEmpty($results);
        $first = $results->first();
        $this->assertArrayHasKey('evento', $first);
        $this->assertArrayHasKey('status', $first);
        $this->assertEquals('Erro de processamento', $first['status']);
        $this->assertEquals('error', $first['status_tipo']);
        $this->assertEquals('rose', $first['status_cor']);
        $this->assertArrayHasKey('data', $first);
        $this->assertArrayHasKey('protocolo', $first);
        $this->assertArrayHasKey('status_tipo', $first);
        $this->assertArrayHasKey('data', $first);
        $this->assertArrayHasKey('protocolo', $first);
        $this->assertArrayHasKey('nr_recibo', $first);
    }

    public function test_get_historico_resolves_via_idevento()
    {
        $service = new TrabalhadorHistoryService();
        $results = $service->getHistorico('pgsql_jucas', 'ID1042937690000002023050311102300001');

        $this->assertNotEmpty($results);
    }

    public function test_get_historico_resolves_via_matricula()
    {
        $service = new TrabalhadorHistoryService();
        $results = $service->getHistorico('pgsql_jucas', '0000058');

        $this->assertNotEmpty($results);
    }

    public function test_get_historico_with_invalid_identifier_returns_empty()
    {
        $service = new TrabalhadorHistoryService();
        $results = $service->getHistorico('pgsql_jucas', 'nao_existe_xyz');

        $this->assertEmpty($results);
    }
}
