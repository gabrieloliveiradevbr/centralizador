<?php
$xmlContent = '
<eSocial xmlns="http://www.esocial.gov.br/schema/eventoCompleto/retornoEventoCompleto/v1_0_0">
<retornoEventoCompleto>
<evento>
<eSocial xmlns="http://www.esocial.gov.br/schema/evt/evtAdmissao/v_S_01_02_00">
<evtAdmissao Id="ID1059957660000002024062715071000598">
<ideEvento>
<indRetif>1</indRetif>
</ideEvento>
</evtAdmissao>
</eSocial>
</evento>
</retornoEventoCompleto>
</eSocial>
';

$colunas = ['cpftrab', 'nmtrab', 'indretif'];
$dados = [];

$xml = simplexml_load_string($xmlContent);

$extrairValores = function ($no) use (&$extrairValores, &$dados, $colunas) {
    foreach ($no->children(null, true) as $filho) {
        $nomeDaTag = strtolower($filho->getName());
        if (in_array($nomeDaTag, $colunas)) {
            $dados[$nomeDaTag] = (string) $filho;
        }
        $extrairValores($filho);
    }
};

$extrairValores($xml);
print_r($dados);
