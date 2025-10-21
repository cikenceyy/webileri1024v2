<?php

namespace App\Core\Orchestrations\Contracts\Dto;

final class O2CState
{
    /**
     * @param  array<string, int|float>  $kpis
     * @param  array<int, array<string, mixed>>  $pipeline
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public array $kpis = [],
        public array $pipeline = [],
        public array $filters = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'kpis' => $this->kpis,
            'pipeline' => $this->pipeline,
            'filters' => $this->filters,
        ];
    }
}
