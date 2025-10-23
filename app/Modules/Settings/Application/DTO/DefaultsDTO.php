<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class DefaultsDTO implements Arrayable
{
    public function __construct(
        public readonly ?int $defaultWarehouseId,
        public readonly ?int $defaultTaxId,
        public readonly ?string $defaultPaymentTerms,
        public readonly ?string $defaultPrintTemplate,
        public readonly ?string $defaultCountry,
        public readonly ?string $defaultCity,
        public readonly array $logisticsDefaults,
        public readonly array $financeDefaults
    ) {
    }

    public function toArray(): array
    {
        return [
            'default_warehouse_id' => $this->defaultWarehouseId,
            'default_tax_id' => $this->defaultTaxId,
            'default_payment_terms' => $this->defaultPaymentTerms,
            'default_print_template' => $this->defaultPrintTemplate,
            'default_country' => $this->defaultCountry,
            'default_city' => $this->defaultCity,
            'logistics_defaults' => $this->logisticsDefaults,
            'finance_defaults' => $this->financeDefaults,
        ];
    }
}
