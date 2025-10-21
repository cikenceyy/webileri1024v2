<?php

namespace App\Consoles\Domain\Dto;

final class O2CQuery
{
    public function __construct(
        public readonly ?string $status,
        public readonly ?int $customerId,
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
            customerId: isset($input['customer_id']) ? (int) $input['customer_id'] : null,
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
            'customer_id' => $this->customerId,
            'search' => $this->search,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
