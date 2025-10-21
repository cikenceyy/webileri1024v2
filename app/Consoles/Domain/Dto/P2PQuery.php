<?php

namespace App\Consoles\Domain\Dto;

final class P2PQuery
{
    public function __construct(
        public readonly ?string $status,
        public readonly ?int $supplierId,
        public readonly ?string $search,
        public readonly ?string $from,
        public readonly ?string $to,
    ) {
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            status: $input['status'] ?? null,
            supplierId: isset($input['supplier_id']) ? (int) $input['supplier_id'] : null,
            search: $input['search'] ?? null,
            from: $input['from'] ?? null,
            to: $input['to'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'supplier_id' => $this->supplierId,
            'search' => $this->search,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
