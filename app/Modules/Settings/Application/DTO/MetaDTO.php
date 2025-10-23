<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class MetaDTO implements Arrayable
{
    public function __construct(
        public readonly int $version,
        public readonly ?string $updatedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'version' => $this->version,
            'updated_at' => $this->updatedAt,
        ];
    }
}
