<?php
$xmlContent = '
<root xmlns="http://ns1">
    <child1>A</child1>
    <child2 xmlns="http://ns2">B</child2>
</root>
';
$xml = simplexml_load_string($xmlContent);

echo "Default children:\n";
foreach($xml->children() as $c) echo $c->getName() . "\n";

echo "\nChildren(null, true):\n";
foreach($xml->children(null, true) as $c) echo $c->getName() . "\n";
