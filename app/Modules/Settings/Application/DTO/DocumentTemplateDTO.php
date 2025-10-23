<?php

namespace App\Modules\Settings\Application\DTO;

use Illuminate\Contracts\Support\Arrayable;

class DocumentTemplateDTO implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly ?string $printHeaderHtml,
        public readonly ?string $printFooterHtml,
        public readonly ?string $watermarkText
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'print_header_html' => $this->printHeaderHtml,
            'print_footer_html' => $this->printFooterHtml,
            'watermark_text' => $this->watermarkText,
        ];
    }
}
