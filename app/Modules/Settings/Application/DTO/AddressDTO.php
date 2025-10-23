<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class AddressDTO implements Arrayable
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $country,
        public readonly ?string $city,
        public readonly ?string $district,
        public readonly ?string $addressLine,
        public readonly ?string $postalCode,
        public readonly bool $isDefault
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'country' => $this->country,
            'city' => $this->city,
            'district' => $this->district,
            'address_line' => $this->addressLine,
            'postal_code' => $this->postalCode,
            'is_default' => $this->isDefault,
        ];
    }
}
