<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class SequenceDTO implements Arrayable
{
    public function __construct(
        public readonly string $docType,
        public readonly ?string $prefix,
        public readonly int $zeroPad,
        public readonly int $nextNo,
        public readonly string $resetRule,
        public readonly string $preview
    ) {
    }

    public function toArray(): array
    {
        return [
            'doc_type' => $this->docType,
            'prefix' => $this->prefix,
            'zero_pad' => $this->zeroPad,
            'next_no' => $this->nextNo,
            'reset_rule' => $this->resetRule,
            'preview' => $this->preview,
        ];
    }
}
