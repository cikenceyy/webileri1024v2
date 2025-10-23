<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class TaxDTO implements Arrayable
{
    /** @param TaxRateDTO[] $rates */
    public function __construct(
        public readonly ?int $defaultVatId,
        public readonly array $rates
    ) {
    }

    public function toArray(): array
    {
        return [
            'default_vat_id' => $this->defaultVatId,
            'rates' => array_map(fn (TaxRateDTO $rate) => $rate->toArray(), $this->rates),
        ];
    }
}
