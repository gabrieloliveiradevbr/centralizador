<?php

namespace App\DTOs;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DashboardData
{
    public function __construct(
        public string $banco,
        public string $schema,
        public ?string $tabela,
        public array $colunas = [],
        public mixed $dados = null,
        public ?string $primaryKey = null,
        public ?string $termoBusca = null,
        public ?string $sortColuna = null,
        public string $direction = 'asc',
        public array $filtrosColuna = [],
        public ?string $colunaDuplicada = null,
        public Collection $menuSchemas = new Collection(),
        public array $menuFasesEsocial = [],
        public array $bancosDisponiveis = [],
        public bool $darkMode = false,
        public ?string $eventDescription = null,
        public ?string $eventTitle = null
    ) {
        // Fallback for dados if not provided
        if ($this->dados === null) {
            $this->dados = new LengthAwarePaginator([], 0, 15);
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
