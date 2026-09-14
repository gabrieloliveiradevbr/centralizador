<?php

namespace App\Services\Xml;

use App\Services\Xml\EsocialEventInterface;
use App\Services\Xml\GenericXmlService;
use App\Services\Xml\S2200XmlService;

class XmlProcessorFactory
{
  /**
   * Retorna o serviço correto de processamento de XML com base na tabela/evento.
   *
   * Para eventos com lógica específica (ex: S-2200), retorna o serviço dedicado.
   * Para todos os outros eventos, retorna o GenericXmlService.
   */
  public static function make(string $tabela): EsocialEventInterface
  {
    return match (strtolower($tabela)) {
      's2200' => new S2200XmlService(),
      default => new GenericXmlService($tabela),
    };
  }
}
