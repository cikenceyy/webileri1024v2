<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class TaxRateDTO implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $rate,
        public readonly bool $active
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rate' => $this->rate,
            'active' => $this->active,
        ];
    }
}
