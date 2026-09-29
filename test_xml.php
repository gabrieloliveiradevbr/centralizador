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
$tagsFound = [];

$extrairTags = function ($no) use (&$extrairTags, &$tagsFound) {
    foreach ($no->children() as $filho) {
        $tagsFound[] = $filho->getName();
        $extrairTags($filho);
    }
};

$extrairTags($xml);
print_r($tagsFound);
