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
$xml = simplexml_load_string($xmlContent);

echo "--- children() ---\n";
$tags1 = [];
$rec1 = function($no) use (&$rec1, &$tags1) {
    foreach($no->children() as $c) {
        $tags1[] = $c->getName();
        $rec1($c);
    }
};
$rec1($xml);
print_r($tags1);

echo "\n--- children(null, true) ---\n";
$tags2 = [];
$rec2 = function($no) use (&$rec2, &$tags2) {
    foreach($no->children(null, true) as $c) {
        $tags2[] = $c->getName();
        $rec2($c);
    }
};
$rec2($xml);
print_r($tags2);
