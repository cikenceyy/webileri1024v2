<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class CompanyDTO implements Arrayable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $legalTitle,
        public readonly ?string $taxOffice,
        public readonly ?string $taxNumber,
        public readonly ?string $website,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?int $logoMediaId
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'legal_title' => $this->legalTitle,
            'tax_office' => $this->taxOffice,
            'tax_number' => $this->taxNumber,
            'website' => $this->website,
            'email' => $this->email,
            'phone' => $this->phone,
            'logo_media_id' => $this->logoMediaId,
        ];
    }
}
