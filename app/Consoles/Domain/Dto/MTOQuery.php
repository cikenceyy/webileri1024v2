<?php

namespace App\Consoles\Domain\Dto;

final class MTOQuery
{
    public function __construct(
        public readonly ?string $status,
        public readonly ?int $productId,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            status: $input['status'] ?? null,
            productId: isset($input['product_id']) ? (int) $input['product_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'product_id' => $this->productId,
        ];
    }
}
