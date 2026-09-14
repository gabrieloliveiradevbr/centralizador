<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\EsocialPhaseService;
use App\Services\Xml\XmlProcessorFactory;

class XmlController extends Controller
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
   * Exibe a tela de importação de XML com seleção de evento.
   */
  public function importView(string $banco)
  {
    Log::info("Acessando importView", ['banco' => $banco]);
    $bancosDisponiveis = $this->getBancosDisponiveis();
    if (!array_key_exists($banco, $bancosDisponiveis)) {
      $banco = 'cm_jucas';
    }

    try {
      $tabelas = DB::connection($banco)
        ->table('information_schema.tables')
        ->select('table_name')
        ->where('table_schema', 'esocial')
        ->orderBy('table_name')
        ->get()
        ->map(fn($t) => [
          'valor' => $t->table_name,
          'label' => EsocialPhaseService::getEventMetadata($t->table_name)['titulo'] ?? $t->table_name
        ]);

      return view('xml.import', [
        'banco'             => $banco,
        'eventos'           => $tabelas,
        'bancosDisponiveis' => $bancosDisponiveis
      ]);
    } catch (\Throwable $e) {
      Log::error("Erro ao carregar eventos para importação ({$banco}): " . $e->getMessage());
      return redirect()->back()->with('error', 'Erro ao carregar a lista de eventos: ' . $e->getMessage());
    }
  }

  /**
   * Processa o upload e faz a importação do XML para salvar no banco
   */
  public function import(Request $request, string $banco)
  {
    Log::info("Iniciando importação de XML", ['banco' => $banco, 'tabela' => $request->input('tabela')]);

    $bancosDisponiveis = $this->getBancosDisponiveis();
    if (!array_key_exists($banco, $bancosDisponiveis)) {
      $banco = 'cm_jucas';
    }

    $request->validate([
      'xml_file' => 'required|file|max:5120',
      'tabela'   => 'required|string',
    ]);

    $tabela = $request->input('tabela');
    $file = $request->file('xml_file');

    if (strtolower($file->getClientOriginalExtension()) !== 'xml') {
      return redirect()->back()->with('error', 'O arquivo deve ser exclusivamente do tipo .xml');
    }

    try {
      $xmlContent = file_get_contents($file->getRealPath());

      // A Factory resolve se usa S2200XmlService ou GenericXmlService
      $processor = XmlProcessorFactory::make($tabela);
      $novoId = $processor->importarXml($banco, $xmlContent);

      return redirect()->back()->with('success', "Evento {$tabela} importado com sucesso! (ID: #{$novoId})");
    } catch (\Throwable $e) {
      Log::error("Erro na importação de XML ({$banco}/{$tabela}): " . $e->getMessage());
      return redirect()->back()->with('error', 'Erro ao importar XML: ' . $e->getMessage());
    }
  }

  /**
   * Exporta um registro para XML (Funcionalidade pendente)
   */
  public function export(string $banco, string $tabela, string $id)
  {
    return redirect()->back()->with('error', 'A funcionalidade de exportação de XML ainda não foi implementada.');
  }
}
