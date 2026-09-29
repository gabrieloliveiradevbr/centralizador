<?php

namespace App\Services\Xml;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
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
    Log::info("GenericXmlService: Iniciando importação", ['banco' => $banco, 'tabela' => $this->tabela]);
    $dados = [];

    // 1. Carrega e valida o XML salvando na variável $xml
    $xml = simplexml_load_string($xmlContent);
    if ($xml === false) {
      Log::error("GenericXmlService: XML malformado", ['banco' => $banco]);
      throw new \Exception("Conteúdo XML inválido ou malformado.");
    }

    // 2. Obtém as colunas da tabela no PostgreSQL
    Log::info("GenericXmlService: Buscando colunas da tabela", ['tabela' => "esocial.{$this->tabela}"]);
    $colunas = Schema::connection($banco)->getColumnListing("esocial.{$this->tabela}");

    try {
      // Resolve o nó do evento de forma recursiva (independente de envelope)
      $localizarEvt = function ($node) use (&$localizarEvt) {
        $nome = $node->getName();
        if (str_starts_with(strtolower($nome), 'evt')) {
          return $node;
        }
        foreach ([$node->children(), $node->children(null, true)] as $children) {
          foreach ($children as $child) {
            $encontrado = $localizarEvt($child);
            if ($encontrado) {
              return $encontrado;
            }
          }
        }
        return null;
      };
      $evt = $localizarEvt($xml) ?? $xml;

      // Se houver atributo Id no nó do evento e a tabela tiver idevento
      if (isset($evt['Id']) && in_array('idevento', $colunas)) {
        $dados['idevento'] = (string) $evt['Id'];
      }

      // 3. Função auxiliar recursiva com rastreamento hierárquico de nós
      $extrairValores = function ($no, array $ancestrais = []) use (&$extrairValores, &$dados, $colunas) {
        // 1. Captura Atributos da tag atual
        foreach ($no->attributes() as $nomeAtrib => $valorAtrib) {
          $nomeAtribLower = strtolower($nomeAtrib);
          if ($nomeAtribLower === 'id' && in_array('idevento', $colunas) && !isset($dados['idevento'])) {
            $dados['idevento'] = (string) $valorAtrib;
          } elseif ($nomeAtribLower !== 'id' && in_array($nomeAtribLower, $colunas) && !isset($dados[$nomeAtribLower])) {
            $dados[$nomeAtribLower] = (string) $valorAtrib;
          }
        }

        // 2. Captura Filhos (Recursivo)
        $filhos = $no->children();
        if ($filhos->count() === 0) {
          $filhos = $no->children(null, true);
        }

        foreach ($filhos as $filho) {
          $nomeTag = $filho->getName();
          $nomeTagLower = strtolower($nomeTag);

          $subFilhos = $filho->children();
          if ($subFilhos->count() === 0) {
            $subFilhos = $filho->children(null, true);
          }
          $hasChildren = $subFilhos->count() > 0;

          if (!$hasChildren) {
            $valor = trim((string) $filho);
            if ($valor !== '') {
              // Gera candidatos em ordem de especificidade (ancestral mais próximo ao mais distante)
              $candidatos = [];
              $revAncestrais = array_reverse($ancestrais);
              foreach ($revAncestrais as $anc) {
                $ancLower = strtolower($anc);
                $candidatos[] = "{$nomeTagLower}_{$ancLower}";
                $candidatos[] = "{$nomeTagLower}{$ancLower}";
                $candidatos[] = "{$ancLower}_{$nomeTagLower}";
                $candidatos[] = "{$ancLower}{$nomeTagLower}";
              }
              $candidatos[] = $nomeTagLower;

              // Associa à primeira coluna válida do banco
              foreach ($candidatos as $cand) {
                if (in_array($cand, $colunas) && $cand !== 'id') {
                  // Se for composto, sempre prioriza. Se for simples, não sobrescreve se já preenchido anteriormente
                  if ($cand !== $nomeTagLower || !isset($dados[$cand])) {
                    $dados[$cand] = $valor;
                    break;
                  }
                }
              }
            }
          } else {
            $novosAncestrais = array_merge($ancestrais, [$nomeTag]);
            $extrairValores($filho, $novosAncestrais);
          }
        }
      };

      // Executa a varredura a partir do nó do evento
      $extrairValores($evt);
      Log::info("GenericXmlService: Varredura concluída", ['campos_encontrados' => array_keys($dados)]);

      if (empty($dados)) {
        Log::warning("GenericXmlService: Nenhum campo compatível encontrado", ['tabela' => $this->tabela]);
        throw new \Exception("Nenhuma tag do XML coincidiu com as colunas da tabela esocial.{$this->tabela}.");
      }

      // 4. Preenche campos de auditoria para disparar as triggers do PostgreSQL
      if (in_array('criado_por', $colunas) && !isset($dados['criado_por'])) {
        $dados['criado_por'] = 1;
      }
      if (in_array('alterado_por', $colunas)) {
        $dados['alterado_por'] = 1;
      }

      // Evita inserir na chave primária auto-increment
      unset($dados['id']);

      Log::info("GenericXmlService: Inserindo no banco", ['banco' => $banco]);

      // 5. Salva no banco e retorna o ID gerado
      return DB::connection($banco)
        ->table("esocial.{$this->tabela}")
        ->insertGetId($dados);
    } catch (\Throwable $th) {
      throw new \Exception("Erro ao processar importação na tabela {$this->tabela}: " . $th->getMessage());
    }
  }
}

