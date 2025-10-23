<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class SettingsDTO implements Arrayable
{
    /**
     * @param array<string, AddressDTO[]> $addresses
     * @param array<string, SequenceDTO> $sequences
     * @param DocumentTemplateDTO[] $documents
     */
    public function __construct(
        public readonly CompanyDTO $company,
        public readonly array $addresses,
        public readonly TaxDTO $tax,
        public readonly CurrencyDTO $currency,
        public readonly array $sequences,
        public readonly DefaultsDTO $defaults,
        public readonly array $documents,
        public readonly MetaDTO $meta
    ) {
    }

    public function toArray(): array
    {
        return [
            'company' => $this->company->toArray(),
            'addresses' => collect($this->addresses)
                ->map(fn (array $items) => array_map(fn (AddressDTO $address) => $address->toArray(), $items))
                ->all(),
            'tax' => $this->tax->toArray(),
            'currency' => $this->currency->toArray(),
            'sequences' => collect($this->sequences)
                ->map(fn (SequenceDTO $sequence) => $sequence->toArray())
                ->all(),
            'defaults' => $this->defaults->toArray(),
            'documents' => array_map(fn (DocumentTemplateDTO $document) => $document->toArray(), $this->documents),
            'meta' => $this->meta->toArray(),
        ];
    }
}
