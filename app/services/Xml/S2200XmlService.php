<?php

namespace App\Services\Xml;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Xml\EsocialEventInterface;

class S2200XmlService implements EsocialEventInterface
{
  public function importarXml(string $banco, string $xmlContent): int
  {
    Log::info("S2200XmlService: Iniciando importação", ['banco' => $banco]);

    // 1. Carrega o XML em memória
    $xml = simplexml_load_string($xmlContent);
    if ($xml === false) {
      Log::error("S2200XmlService: XML malformado", ['banco' => $banco]);
      throw new \Exception("Conteúdo XML do S-2200 inválido ou malformado.");
    }

    // Helper para extração limpa e tratamento nulo de valores (sem defaults artificiais)
    $str = function ($val): ?string {
      if ($val === null) {
        return null;
      }
      $s = trim((string) $val);
      return $s !== '' ? $s : null;
    };

    // =========================================================
    // 1. ESTRUTURA DO XML E NAVEGAÇÃO DE NÓS
    // =========================================================
    $evt = null;

    // Reconhece a estrutura completa: retornoEventoCompleto -> evento -> evtAdmissao (e variantes)
    if (isset($xml->retornoEventoCompleto->evento->evtAdmissao)) {
      $evt = $xml->retornoEventoCompleto->evento->evtAdmissao;
    } elseif (isset($xml->retornoEventoCompleto->evento->eSocial->evtAdmissao)) {
      $evt = $xml->retornoEventoCompleto->evento->eSocial->evtAdmissao;
    } elseif (isset($xml->retornoEventoCompleto->evento->evtCadInitial)) {
      $evt = $xml->retornoEventoCompleto->evento->evtCadInitial;
    } elseif (isset($xml->retornoEventoCompleto->evento->eSocial->evtCadInitial)) {
      $evt = $xml->retornoEventoCompleto->evento->eSocial->evtCadInitial;
    } elseif (isset($xml->evento->evtAdmissao)) {
      $evt = $xml->evento->evtAdmissao;
    } elseif (isset($xml->evento->evtCadInitial)) {
      $evt = $xml->evento->evtCadInitial;
    } elseif (isset($xml->evtAdmissao)) {
      $evt = $xml->evtAdmissao;
    } elseif (isset($xml->evtCadInitial)) {
      $evt = $xml->evtCadInitial;
    }

    // Fallback recursivo para assegurar a captura mesmo com envelopes não padronizados
    if ($evt === null) {
      $localizarEvt = function ($node) use (&$localizarEvt) {
        $nome = strtolower($node->getName());
        if ($nome === 'evtadmissao' || $nome === 'evtcadinitial') {
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
      $evt = $localizarEvt($xml);
    }

    if ($evt === null) {
      throw new \Exception("Nó do evento S-2200 (evtAdmissao / evtCadInitial) não foi localizado no XML.");
    }

    // Extração do Id do evento sem fallbacks temporais
    $idEvento = $str($evt['Id'] ?? $evt->attributes()?->Id ?? $evt['id'] ?? $evt->attributes()?->id);
    if (empty($idEvento)) {
      throw new \Exception("ID do evento (idevento) não foi localizado nos atributos do nó " . $evt->getName() . ".");
    }

    // Extração do número do recibo (retornoEventoCompleto -> recibo -> eSocial -> retornoEvento -> recibo -> nrRecibo ou ideEvento->nrRecibo)
    $nrRecibo = null;
    if (isset($xml->retornoEventoCompleto->recibo->eSocial->retornoEvento->recibo->nrRecibo)) {
      $nrRecibo = (string) $xml->retornoEventoCompleto->recibo->eSocial->retornoEvento->recibo->nrRecibo;
    } elseif (isset($xml->retornoEventoCompleto->recibo->retornoEvento->recibo->nrRecibo)) {
      $nrRecibo = (string) $xml->retornoEventoCompleto->recibo->retornoEvento->recibo->nrRecibo;
    } elseif (isset($evt->ideEvento->nrRecibo)) {
      $nrRecibo = (string) $evt->ideEvento->nrRecibo;
    } else {
      $reciboNodes = $xml->xpath('//nrRecibo');
      if (!empty($reciboNodes)) {
        $nrRecibo = (string) $reciboNodes[0];
      }
    }
    $nrRecibo = $str($nrRecibo);

    // =========================================================
    // 2. SUPORTE A TABELAS FILHAS COM TRANSAÇÃO ATÔMICA
    // =========================================================
    return DB::connection($banco)->transaction(function () use ($banco, $evt, $idEvento, $nrRecibo, $str) {
      Log::info("S2200XmlService: Iniciando transação no banco", ['banco' => $banco]);
      $db = DB::connection($banco);

      // =========================================================
      // PASSO 1: Mapeamento e Inserção na Tabela Pai (esocial.s2200)
      // =========================================================
      $dados = [
        // Identificação do Evento
        'idevento'     => $idEvento,
        'indretif'     => $str($evt->ideEvento?->indRetif),
        'nrrecibo'     => $nrRecibo,
        'tpamb'        => $str($evt->ideEvento?->tpAmb),
        'procemi'      => $str($evt->ideEvento?->procEmi),
        'verproc'      => $str($evt->ideEvento?->verProc),

        // Empregador
        'tpinsc'       => $str($evt->ideEmpregador?->tpInsc),
        'nrinsc'       => $str($evt->ideEmpregador?->nrInsc),

        // Dados do Trabalhador
        'cpftrab'      => $str($evt->trabalhador?->cpfTrab),
        'nmtrab'       => $str($evt->trabalhador?->nmTrab),
        'sexo'         => $str($evt->trabalhador?->sexo),
        'racacor'      => $str($evt->trabalhador?->racaCor),
        'estciv'       => $str($evt->trabalhador?->estCiv),
        'grauinstr'    => $str($evt->trabalhador?->grauInstr),
        'nmsoc'        => $str($evt->trabalhador?->nmSoc),

        // Nascimento via sub-tag <nascimento>
        'dtnascto'     => $str($evt->trabalhador?->nascimento?->dtNascto ?? $evt->trabalhador?->dtNascto),
        'paisnascto'   => $str($evt->trabalhador?->nascimento?->paisNascto ?? $evt->trabalhador?->paisNascto),
        'paisnac'      => $str($evt->trabalhador?->nascimento?->paisNac ?? $evt->trabalhador?->paisNac),

        // Endereço (Brasil ou Exterior) sem defaults fictícios
        'tplograd'     => $str($evt->trabalhador?->endereco?->brasil?->tpLograd),
        'dsclograd'    => $str($evt->trabalhador?->endereco?->brasil?->dscLograd ?? $evt->trabalhador?->endereco?->exterior?->dscLograd),
        'nrlograd'     => $str($evt->trabalhador?->endereco?->brasil?->nrLograd ?? $evt->trabalhador?->endereco?->exterior?->nrLograd),
        'complemento'  => $str($evt->trabalhador?->endereco?->brasil?->complemento ?? $evt->trabalhador?->endereco?->exterior?->complemento),
        'bairro'       => $str($evt->trabalhador?->endereco?->brasil?->bairro ?? $evt->trabalhador?->endereco?->exterior?->bairro),
        'cep'          => $str($evt->trabalhador?->endereco?->brasil?->cep),
        'codmunic'     => $str($evt->trabalhador?->endereco?->brasil?->codMunic),
        'uf'           => $str($evt->trabalhador?->endereco?->brasil?->uf),

        // Trabalhador Imigrante
        'tmpresid'     => $str($evt->trabalhador?->trabImig?->tmpResid),
        'conding'      => $str($evt->trabalhador?->trabImig?->condIng),

        // Informações de Deficiência
        'deffisica'                  => $str($evt->trabalhador?->infoDeficiencia?->defFisica),
        'defvisual'                  => $str($evt->trabalhador?->infoDeficiencia?->defVisual),
        'defauditiva'                => $str($evt->trabalhador?->infoDeficiencia?->defAuditiva),
        'defmental'                  => $str($evt->trabalhador?->infoDeficiencia?->defMental),
        'defintelectual'             => $str($evt->trabalhador?->infoDeficiencia?->defIntelectual),
        'reabreadap'                 => $str($evt->trabalhador?->infoDeficiencia?->reabReadap),
        'infocota'                   => $str($evt->trabalhador?->infoDeficiencia?->infoCota),
        'observacao_infodeficiencia' => $str($evt->trabalhador?->infoDeficiencia?->observacao),

        // Contato
        'foneprinc'    => $str($evt->trabalhador?->contato?->fonePrinc),
        'emailprinc'   => $str($evt->trabalhador?->contato?->emailPrinc),

        // Vínculo Principal
        'matricula'    => $str($evt->vinculo?->matricula),
        'tpregtrab'    => $str($evt->vinculo?->tpRegTrab),
        'tpregprev'    => $str($evt->vinculo?->tpRegPrev),
        'cadini'       => $str($evt->vinculo?->cadIni),

        // Informações do Contrato
        'codcateg'          => $str($evt->vinculo?->infoContrato?->codCateg),
        'dtadm'             => $str($evt->vinculo?->infoContrato?->dtAdm),
        'tpadmissao'        => $str($evt->vinculo?->infoContrato?->tpAdmissao),
        'indadmissao'       => $str($evt->vinculo?->infoContrato?->indAdmissao),
        'nrproctrab'        => $str($evt->vinculo?->infoContrato?->nrProcTrab),
        'tpregjor'          => $str($evt->vinculo?->infoContrato?->tpRegJor),
        'natatividade'      => $str($evt->vinculo?->infoContrato?->natAtividade),
        'dtbase'            => $str($evt->vinculo?->infoContrato?->dtBase),
        'cnpjsindcategprof' => $str($evt->vinculo?->infoContrato?->cnpjSindCategProf),

        // Regime Celetista / FGTS / Trabalho Temporário / Aprendiz
        'dtopcfgts'            => $str($evt->vinculo?->infoRegimeTrab?->infoCeletista?->dtOpcFGTS ?? $evt->vinculo?->infoContrato?->dtOpCfgts),
        'hipleg'               => $str($evt->vinculo?->infoRegimeTrab?->infoCeletista?->trabTemp?->hipLeg ?? $evt->vinculo?->infoContrato?->duracao?->hipLeg),
        'justcontr'            => $str($evt->vinculo?->infoRegimeTrab?->infoCeletista?->trabTemp?->justContr ?? $evt->vinculo?->infoContrato?->duracao?->justContr),
        'tpinsc_ideestabvinc'  => $str($evt->vinculo?->infoRegimeTrab?->infoCeletista?->trabTemp?->ideEstabVinc?->tpInsc),
        'nrinsc_ideestabvinc'  => $str($evt->vinculo?->infoRegimeTrab?->infoCeletista?->trabTemp?->ideEstabVinc?->nrInsc),
        'tpinsc_aprend'        => $str($evt->vinculo?->infoRegimeTrab?->infoCeletista?->aprend?->tpInsc ?? $evt->vinculo?->infoContrato?->aprend?->tpInsc),
        'nrinsc_aprend'        => $str($evt->vinculo?->infoRegimeTrab?->infoCeletista?->aprend?->nrInsc ?? $evt->vinculo?->infoContrato?->aprend?->nrInsc),

        // Regime Estatutário
        'tpprov'        => $str($evt->vinculo?->infoRegimeTrab?->infoEstatutario?->tpProv),
        'dtexercicio'   => $str($evt->vinculo?->infoRegimeTrab?->infoEstatutario?->dtExercicio),
        'tpplanrp'      => $str($evt->vinculo?->infoRegimeTrab?->infoEstatutario?->tpPlanRP),
        'indtetorgps'   => $str($evt->vinculo?->infoRegimeTrab?->infoEstatutario?->indTetoRGPS),
        'indabonoperm'  => $str($evt->vinculo?->infoRegimeTrab?->infoEstatutario?->indAbonoPerm),
        'dtiniabono'    => $str($evt->vinculo?->infoRegimeTrab?->infoEstatutario?->dtIniAbono),

        // Cargo e Função (respeitando Case-Sensitivity e caminhos específicos)
        'nmcargo'       => $str($evt->vinculo?->infoContrato?->nmCargo ?? $evt->vinculo?->infoContrato?->cargoFuncao?->nmCargo),
        'cbocargo'      => $str($evt->vinculo?->infoContrato?->CBOCargo ?? $evt->vinculo?->infoContrato?->cargoFuncao?->CBOCargo ?? $evt->vinculo?->infoContrato?->cargoFuncao?->cboCargo ?? $evt->vinculo?->infoContrato?->cboCargo),
        'dtingrcargo'   => $str($evt->vinculo?->infoContrato?->cargoFuncao?->dtIngrCargo ?? $evt->vinculo?->infoContrato?->dtIngrCargo),
        'nmfuncao'      => $str($evt->vinculo?->infoContrato?->nmFuncao ?? $evt->vinculo?->infoContrato?->cargoFuncao?->nmFuncao),
        'cbofuncao'     => $str($evt->vinculo?->infoContrato?->CBOFuncao ?? $evt->vinculo?->infoContrato?->cargoFuncao?->CBOFuncao ?? $evt->vinculo?->infoContrato?->cargoFuncao?->cboFuncao ?? $evt->vinculo?->infoContrato?->cboFuncao),
        'acumcargo'     => $str($evt->vinculo?->infoContrato?->cargoFuncao?->acumCargo ?? $evt->vinculo?->infoContrato?->acumCargo),

        // Remuneração
        'vrsalfx'       => $str($evt->vinculo?->infoContrato?->remuneracao?->vrSalFx),
        'undsalfixo'    => $str($evt->vinculo?->infoContrato?->remuneracao?->undSalFixo),
        'dscsalvar'     => $str($evt->vinculo?->infoContrato?->remuneracao?->dscSalVar),

        // Duração do Contrato
        'tpcontr'       => $str($evt->vinculo?->infoContrato?->duracao?->tpContr),
        'dtterm'        => $str($evt->vinculo?->infoContrato?->duracao?->dtTerm),
        'clauassec'     => $str($evt->vinculo?->infoContrato?->duracao?->clauAssec),
        'objdet'        => $str($evt->vinculo?->infoContrato?->duracao?->objDet),

        // Local de Trabalho (Geral)
        'tpinsc_localtrabgeral'   => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTrabGeral?->tpInsc),
        'nrinsc_localtrabgeral'   => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTrabGeral?->nrInsc),
        'desccomp_localtrabgeral' => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTrabGeral?->descComp),

        // Local de Trabalho (Temporário / Domicílio)
        'tplograd_localtempdom'    => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTempDom?->tpLograd),
        'dsclograd_localtempdom'   => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTempDom?->dscLograd),
        'nrlograd_localtempdom'    => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTempDom?->nrLograd),
        'complemento_localtempdom' => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTempDom?->complemento),
        'bairro_localtempdom'      => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTempDom?->bairro),
        'cep_localtempdom'         => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTempDom?->cep),
        'codmunic_localtempdom'    => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTempDom?->codMunic),
        'uf_localtempdom'          => $str($evt->vinculo?->infoContrato?->localTrabalho?->localTempDom?->uf),

        // Horário Contratual
        'qtdhrssem'     => $str($evt->vinculo?->infoContrato?->horContratual?->qtdHrsSem),
        'tpjornada'     => $str($evt->vinculo?->infoContrato?->horContratual?->tpJornada),
        'tmpparc'       => $str($evt->vinculo?->infoContrato?->horContratual?->tmpParc),
        'hornoturno'    => $str($evt->vinculo?->infoContrato?->horContratual?->horNoturno),
        'dscjorn'       => $str($evt->vinculo?->infoContrato?->horContratual?->dscJorn),

        // Alvará Judicial
        'nrprocjud'     => $str($evt->vinculo?->infoContrato?->alvaraJudicial?->nrProcJud),

        // Sucessão de Vínculo
        'tpinsc_sucessaovinc'     => $str($evt->vinculo?->sucessaoVinc?->tpInsc),
        'nrinsc_sucessaovinc'     => $str($evt->vinculo?->sucessaoVinc?->nrInsc),
        'matricant_sucessaovinc'  => $str($evt->vinculo?->sucessaoVinc?->matricAnt),
        'dttransf_sucessaovinc'   => $str($evt->vinculo?->sucessaoVinc?->dtTransf),
        'observacao_sucessaovinc' => $str($evt->vinculo?->sucessaoVinc?->observacao),

        // Mudança de CPF
        'cpfant'                 => $str($evt->vinculo?->mudancaCPF?->cpfAnt),
        'matricant'              => $str($evt->vinculo?->mudancaCPF?->matricAnt),
        'dtaltcpf'               => $str($evt->vinculo?->mudancaCPF?->dtAltCPF),
        'observacao_mudancacpf'  => $str($evt->vinculo?->mudancaCPF?->observacao),

        // Afastamento / Desligamento / Cessão
        'dtiniafast'    => $str($evt->vinculo?->afastamento?->dtIniAfast),
        'codmotafast'   => $str($evt->vinculo?->afastamento?->codMotAfast),
        'dtdeslig'      => $str($evt->vinculo?->desligamento?->dtDeslig),
        'dtinicessao'   => $str($evt->vinculo?->cessao?->dtIniCessao),

        // Auditoria e Triggers PL/pgSQL
        'criado_por'    => 1,
        'alterado_por'  => 1,
      ];

      // 3. LOGS E RASTREABILIDADE: Log imediatamente antes do insert na tabela esocial.s2200
      Log::info("S2200XmlService: Inserindo registro na tabela esocial.s2200", [
        'idevento'  => $dados['idevento'],
        'nrrecibo'  => $dados['nrrecibo'],
        'cpftrab'   => $dados['cpftrab'],
        'matricula' => $dados['matricula'],
      ]);

      $s2200Id = $db->table('esocial.s2200')->insertGetId($dados);

      // =========================================================
      // PASSO 2: Inserção dos Dependentes (esocial.s2200_dependente)
      // =========================================================
      if (isset($evt->trabalhador?->dependente)) {
        Log::info("S2200XmlService: Inserindo dependentes");
        foreach ($evt->trabalhador->dependente as $dep) {
          $db->table('esocial.s2200_dependente')->insert([
            's2200_id'     => $s2200Id,
            'tpdep'        => $str($dep->tpDep),
            'nmdep'        => $str($dep->nmDep),
            'dtnascto'     => $str($dep->dtNascto),
            'cpfdep'       => $str($dep->cpfDep),
            'depirrf'      => $str($dep->depIRRF),
            'depsf'        => $str($dep->depSF),
            'inctrab'      => $str($dep->incTrab),
            'alterado_por' => 1,
          ]);
        }
      }

      // =========================================================
      // PASSO 3: Trabalhador Substituído (esocial.s2200_idetrabsubstituido)
      // =========================================================
      if (isset($evt->vinculo?->infoContrato?->duracao?->ideTrabSubstituido)) {
        Log::info("S2200XmlService: Inserindo trabalhador substituído");
        foreach ($evt->vinculo->infoContrato->duracao->ideTrabSubstituido as $sub) {
          $db->table('esocial.s2200_idetrabsubstituido')->insert([
            's2200_id'     => $s2200Id,
            'cpftrabsubst' => $str($sub->cpfTrabSubst),
            'alterado_por' => 1,
          ]);
        }
      }

      // =========================================================
      // PASSO 4: Observações do Contrato (esocial.s2200_observacoes)
      // =========================================================
      if (isset($evt->vinculo?->observacoes)) {
        Log::info("S2200XmlService: Inserindo observações");
        foreach ($evt->vinculo->observacoes as $obs) {
          $db->table('esocial.s2200_observacoes')->insert([
            's2200_id'     => $s2200Id,
            'observacao'   => $str($obs->observacao),
            'alterado_por' => 1,
          ]);
        }
      }

      // =========================================================
      // PASSO 5: Treinamentos e Capacitações (esocial.s2200_treicap)
      // =========================================================
      if (isset($evt->vinculo?->treiCap)) {
        Log::info("S2200XmlService: Inserindo treinamentos");
        foreach ($evt->vinculo->treiCap as $tc) {
          $db->table('esocial.s2200_treicap')->insert([
            's2200_id'     => $s2200Id,
            'codtreicap'   => $str($tc->codTreiCap),
            'alterado_por' => 1,
          ]);
        }
      }

      Log::info("S2200XmlService: Importação concluída com sucesso", ['id' => $s2200Id]);
      return $s2200Id;
    });
  }
}
