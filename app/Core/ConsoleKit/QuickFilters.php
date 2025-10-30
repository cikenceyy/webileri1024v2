<?php

namespace App\Core\ConsoleKit;

use Illuminate\Support\Arr;

/**
 * Konsol ekranlarında "tek tık" filtrelerin durumunu hesaplar.
 */
class QuickFilters
{
    /**
     * @param  array<int, array<string, mixed>>  $filters
     */
    public function __construct(private array $filters)
    {
    }

    /**
     * @param  array<int, array<string, mixed>>  $filters
     */
    public static function make(array $filters): self
    {
        return new self($filters);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return collect($this->filters)
            ->map(function (array $filter) {
                $filter['id'] ??= Arr::get($filter, 'field');
                $filter['label'] ??= $filter['id'];
                $filter['payload'] ??= [];

                return $filter;
            })
            ->values()
            ->all();
    }
}
