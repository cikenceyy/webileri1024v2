<?php

namespace App\Core\Support\TableKit;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function collect;
use function strip_tags;

class TableConfig implements Arrayable
{
    public const DEFAULT_CLIENT_THRESHOLD = 500;

    /**
     * @var array<int, Column>
     */
    protected array $columns;

    public function __construct(
        array $columns,
        protected int $clientThreshold = self::DEFAULT_CLIENT_THRESHOLD,
        protected ?string $defaultSort = null,
        protected ?int $dataCount = null
    ) {
        $this->columns = $columns;
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<string, mixed>  $options
     */
    public static function make(array $columns, array $options = []): self
    {
        $columnObjects = collect($columns)
            ->map(fn (array $definition) => Column::fromArray($definition))
            ->values()
            ->all();

        return new self(
            $columnObjects,
            (int) Arr::get($options, 'client_threshold', self::DEFAULT_CLIENT_THRESHOLD),
            Arr::get($options, 'default_sort'),
            Arr::get($options, 'data_count')
        );
    }

    /**
     * @return array<int, Column>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    public function clientThreshold(): int
    {
        return $this->clientThreshold;
    }

    public function defaultSort(): ?string
    {
        return $this->defaultSort;
    }

    public function dataCount(): ?int
    {
        return $this->dataCount;
    }

    public function withDataCount(int $count): self
    {
        $clone = clone $this;
        $clone->dataCount = $count;

        return $clone;
    }

    public function filterableColumns(): Collection
    {
        return collect($this->columns)->filter(fn (Column $column) => $column->filterable());
    }

    public function sortableColumns(): Collection
    {
        return collect($this->columns)->filter(fn (Column $column) => $column->sortable());
    }

    public function determineMode(?int $count = null): string
    {
        $count ??= $this->dataCount ?? 0;

        if ($count <= $this->clientThreshold) {
            return 'client';
        }

        return 'server';
    }

    /**
     * @param  iterable<int, array<string, mixed>>|Collection  $rows
     */
    public function prepareDataset(iterable $rows): array
    {
        $rowsCollection = $rows instanceof Collection ? $rows : collect($rows);

        return [
            'columns' => collect($this->columns)->map(fn (Column $column) => $column->toFrontendDefinition())->values()->all(),
            'rows' => $rowsCollection->map(function (array $row, int $index) {
                $cells = Arr::get($row, 'cells', $row);
                $rowId = Arr::get($row, 'id', Str::uuid()->toString());

                $preparedCells = [];

                foreach ($this->columns as $column) {
                    $cellValue = $cells[$column->key()] ?? null;
                    $cellData = $column->prepareCell($cells, $cellValue);
                    $cellData['text'] = trim(strip_tags($cellData['html']));
                    $preparedCells[$column->key()] = $cellData;
                }

                return [
                    'id' => $rowId,
                    'index' => $index,
                    'cells' => $preparedCells,
                ];
            })->values()->all(),
        ];
    }

    public function summarize(iterable $rows): array
    {
        $rowsCollection = $rows instanceof Collection ? $rows : collect($rows);

        return [
            'total' => $rowsCollection->count(),
            'filters' => $this->filterableColumns()->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'columns' => collect($this->columns)->map(fn (Column $column) => $column->toFrontendDefinition())->values()->all(),
            'clientThreshold' => $this->clientThreshold,
            'defaultSort' => $this->defaultSort,
            'dataCount' => $this->dataCount,
        ];
    }
}
