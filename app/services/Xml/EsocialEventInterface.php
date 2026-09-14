<?php

namespace App\Services\Xml;

interface EsocialEventInterface
{
  public function importarXml(string $banco, string $xmlContent): int;
}
