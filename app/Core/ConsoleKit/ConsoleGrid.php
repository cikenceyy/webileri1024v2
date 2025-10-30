<?php

namespace App\Core\ConsoleKit;

/**
 * Console ekranlarında kullanılacak grid konfigürasyonunu taşır.
 */
class ConsoleGrid
{
    /**
     * @param  array<int, array<string, mixed>>  $columns
     */
    public function __construct(
        public readonly string $tableKey,
        public readonly array $columns,
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     */
    public static function make(string $tableKey, array $columns): self
    {
        return new self($tableKey, $columns);
    }
}
