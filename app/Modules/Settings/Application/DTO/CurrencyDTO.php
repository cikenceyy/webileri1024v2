<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class CurrencyDTO implements Arrayable
{
    public function __construct(
        public readonly string $baseCurrency,
        public readonly int $precisionPrice,
        public readonly string $exchangePolicy
    ) {
    }

    public function toArray(): array
    {
        return [
            'base_currency' => $this->baseCurrency,
            'precision_price' => $this->precisionPrice,
            'exchange_policy' => $this->exchangePolicy,
        ];
    }
}
