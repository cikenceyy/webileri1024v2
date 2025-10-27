<?php

namespace App\Core\Support\TableKit;

class Config
{
    protected string $id;
    protected string $route;
    /** @var Column[] */
    protected array $columns = [];
    /** @var array{0:string,1:string} */
    protected array $order;
    /** @var int[] */
    protected array $perPageOptions;

    /**
     * @param Column[] $columns
     * @param int[] $perPageOptions
     */
    public function __construct(
        string $id,
        string $route,
        array $columns,
        array $order = ['created_at', 'desc'],
        array $perPageOptions = [10, 15, 25, 50]
    ) {
        $this->id = $id;
        $this->route = $route;
        $this->columns = $columns;
        $this->order = $order;
        $this->perPageOptions = $perPageOptions;
    }

    public function id(): string { return $this->id; }
    public function route(): string { return $this->route; }

    /** @return Column[] */
    public function columns(): array { return $this->columns; }

    /** @return Column[] */
    public function filterableColumns(): array
    {
        return array_values(array_filter($this->columns, fn(Column $c) => $c->isFilterable()));
    }

    /** @return array{0:string,1:string} */
    public function order(): array { return $this->order; }

    /** @return int[] */
    public function perPageOptions(): array { return $this->perPageOptions; }
}
