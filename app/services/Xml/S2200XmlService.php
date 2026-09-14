<?php

namespace App\Services\Xml;

use Illuminate\Support\Facades\DB;
use App\Services\Xml\EsocialEventInterface;

class S2200XmlService implements EsocialEventInterface
{
  public function importarXml(string $banco, string $xmlContent): int
  {
    // 1. Carrega o XML em memória
    $xml = simplexml_load_string($xmlContent);
    if ($xml === false) {
      throw new \Exception("Conteúdo XML do S-2200 inválido ou malformado.");
    }

    // Resolve o nó principal do evento
    $evt = $xml->evtCadInitial ?? $xml;

    // 2. Abre a transação para garantir atomicidade (Pai + Filhos)
    return DB::connection($banco)->transaction(function () use ($banco, $evt) {
      $db = DB::connection($banco);

      // =========================================================
      // PASSO 1: Inserção na Tabela Pai (esocial.s2200)
      // =========================================================
      $s2200Id = $db->table('esocial.s2200')->insertGetId([
        'idevento'     => (string) ($evt['Id'] ?? 'ID' . time()),
        'indretif'     => (string) ($evt->ideEvento->indRetif ?? '1'),
        'nrrecibo'     => (string) ($evt->ideEvento->nrRecibo ?? null),
        'tpamb'        => (string) ($evt->ideEvento->tpAmb ?? '1'),
        'procemi'      => (string) ($evt->ideEvento->procEmi ?? '1'),
        'verproc'      => (string) ($evt->ideEvento->verProc ?? '1.0'),
        'tpinsc'       => (string) ($evt->ideEmpregador->tpInsc ?? '1'),
        'nrinsc'       => (string) ($evt->ideEmpregador->nrInsc ?? ''),

        // Dados do Trabalhador
        'cpftrab'      => (string) ($evt->trabalhador->cpfTrab ?? ''),
        'nmtrab'       => (string) ($evt->trabalhador->nmTrab ?? ''),
        'sexo'         => (string) ($evt->trabalhador->sexo ?? 'M'),
        'racacor'      => (string) ($evt->trabalhador->racaCor ?? '1'),
        'estciv'       => (string) ($evt->trabalhador->estCiv ?? null),
        'grauinstr'    => (string) ($evt->trabalhador->grauInstr ?? '01'),
        'dtnascto'     => (string) ($evt->trabalhador->dtNascto ?? '1990-01-01'),
        'paisnascto'   => (string) ($evt->trabalhador->paisNascto ?? '105'),
        'paisnac'      => (string) ($evt->trabalhador->paisNac ?? '105'),

        // Endereço
        'dsclograd'    => (string) ($evt->trabalhador->endereco->brasil->dscLograd ?? 'NÃO INFORMADO'),
        'nrlograd'     => (string) ($evt->trabalhador->endereco->brasil->nrLograd ?? 'SN'),
        'cep'          => (string) ($evt->trabalhador->endereco->brasil->cep ?? '00000000'),
        'codmunic'     => (string) ($evt->trabalhador->endereco->brasil->codMunic ?? '0000000'),
        'uf'           => (string) ($evt->trabalhador->endereco->brasil->uf ?? 'CE'),

        // Vínculo
        'matricula'    => (string) ($evt->vinculo->matricula ?? 'MAT' . time()),
        'tpregtrab'    => (string) ($evt->vinculo->tpRegTrab ?? '1'),
        'tpregprev'    => (string) ($evt->vinculo->tpRegPrev ?? '1'),
        'cadini'       => (string) ($evt->vinculo->cadIni ?? 'S'),
        'codcateg'     => (string) ($evt->vinculo->infoContrato->codCateg ?? '101'),

        // Dispara trigger do Postgres
        'alterado_por' => 1,
      ]);

      // =========================================================
      // PASSO 2: Inserção dos Dependentes (esocial.s2200_dependente)
      // =========================================================
      if (isset($evt->trabalhador->dependente)) {
        foreach ($evt->trabalhador->dependente as $dep) {
          $db->table('esocial.s2200_dependente')->insert([
            's2200_id'     => $s2200Id,
            'tpdep'        => (string) $dep->tpDep,
            'nmdep'        => (string) $dep->nmDep,
            'dtnascto'     => (string) $dep->dtNascto,
            'cpfdep'       => (string) ($dep->cpfDep ?? null),
            'depirrf'      => (string) ($dep->depIRRF ?? 'N'),
            'depsf'        => (string) ($dep->depSF ?? 'N'),
            'inctrab'      => (string) ($dep->incTrab ?? 'N'),
            'alterado_por' => 1,
          ]);
        }
      }

      // =========================================================
      // PASSO 3: Trabalhador Substituído (esocial.s2200_idetrabsubstituido)
      // =========================================================
      if (isset($evt->vinculo->infoContrato->duracao->ideTrabSubstituido)) {
        foreach ($evt->vinculo->infoContrato->duracao->ideTrabSubstituido as $sub) {
          $db->table('esocial.s2200_idetrabsubstituido')->insert([
            's2200_id'        => $s2200Id,
            'cpftrabsubst'    => (string) $sub->cpfTrabSubst,
            'alterado_por'    => 1,
          ]);
        }
      }

      // =========================================================
      // PASSO 4: Observações do Contrato (esocial.s2200_observacoes)
      // =========================================================
      if (isset($evt->vinculo->observacoes)) {
        foreach ($evt->vinculo->observacoes as $obs) {
          $db->table('esocial.s2200_observacoes')->insert([
            's2200_id'     => $s2200Id,
            'observacao'   => (string) $obs->observacao,
            'alterado_por' => 1,
          ]);
        }
      }

      // =========================================================
      // PASSO 5: Treinamentos e Capacitações (esocial.s2200_treicap)
      // =========================================================
      if (isset($evt->vinculo->treiCap)) {
        foreach ($evt->vinculo->treiCap as $tc) {
          $db->table('esocial.s2200_treicap')->insert([
            's2200_id'     => $s2200Id,
            'codtreicap'   => (string) $tc->codTreiCap,
            'alterado_por' => 1,
          ]);
        }
      }

      // Retorna a chave primária da tabela principal criada
      return $s2200Id;
    });
  }
}
