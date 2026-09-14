<?php

namespace App\Services\Xml;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\Xml\EsocialEventInterface;

class GenericXmlService implements EsocialEventInterface
{
  protected string $tabela;

  public function __construct(string $tabela)
  {
    $this->tabela = $tabela;
  }

  public function importarXml(string $banco, string $xmlContent): int
  {
    $dados = [];

    // 1. Carrega e valida o XML salvando na variável $xml
    $xml = simplexml_load_string($xmlContent);
    if ($xml === false) {
      throw new \Exception("Conteúdo XML inválido ou malformado.");
    }

    // 2. Obtém as colunas da tabela no PostgreSQL
    $colunas = Schema::connection($banco)->getColumnListing("esocial.{$this->tabela}");

    try {
      // 3. Função auxiliar interna para percorrer todas as tags (mesmo as aninhadas)
      $extrairValores = function ($no) use (&$extrairValores, &$dados, $colunas) {
        foreach ($no->children() as $filho) {
          $nomeDaTag = strtolower($filho->getName());

          // Se a tag existir como coluna na tabela, grava o valor
          if (in_array($nomeDaTag, $colunas)) {
            $dados[$nomeDaTag] = (string) $filho;
          }

          // Se a tag tiver sub-elementos, navega para dentro dela
          if ($filho->count() > 0) {
            $extrairValores($filho);
          }
        }
      };

      // Executa a varredura a partir do nó raiz do XML
      $extrairValores($xml);

      if (empty($dados)) {
        throw new \Exception("Nenhuma tag do XML coincidiu com as colunas da tabela esocial.{$this->tabela}.");
      }

      // 4. Preenche alterado_por para disparar as triggers do PostgreSQL
      $dados['alterado_por'] = 1;

      // 5. Salva no banco e retorna o ID gerado
      return DB::connection($banco)
        ->table("esocial.{$this->tabela}")
        ->insertGetId($dados);
    } catch (\Throwable $th) {
      throw new \Exception("Erro ao processar importação na tabela {$this->tabela}: " . $th->getMessage());
    }
  }
}
