<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array<int, array{columns: array<int, string>, name: string}>>
     */
    protected array $indexDefinitions = [
        'users' => [
            ['columns' => ['company_id'], 'name' => 'users_company_id_index'],
        ],
        'media' => [
            ['columns' => ['company_id'], 'name' => 'media_company_id_index'],
        ],
        'products' => [
            ['columns' => ['company_id'], 'name' => 'products_company_id_index'],
        ],
        'product_categories' => [
            ['columns' => ['company_id'], 'name' => 'product_categories_company_id_index'],
        ],
        'warehouses' => [
            ['columns' => ['company_id'], 'name' => 'warehouses_company_id_index'],
        ],
        'customers' => [
            ['columns' => ['company_id'], 'name' => 'customers_company_id_index'],
        ],
        'orders' => [
            ['columns' => ['company_id'], 'name' => 'orders_company_id_index'],
        ],
        'shipments' => [
            ['columns' => ['company_id'], 'name' => 'shipments_company_id_index'],
        ],
        'invoices' => [
            ['columns' => ['company_id'], 'name' => 'invoices_company_id_index'],
        ],
        'purchase_orders' => [
            ['columns' => ['company_id'], 'name' => 'purchase_orders_company_id_index'],
        ],
        'work_orders' => [
            ['columns' => ['company_id'], 'name' => 'work_orders_company_id_index'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexDefinitions as $table => $definitions) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($definitions, $table): void {
                foreach ($definitions as $definition) {
                    if ($this->indexExists($table, $definition['name'])) {
                        continue;
                    }

                    $tableBlueprint->index($definition['columns'], $definition['name']);
                }
            });

            if (DB::table($table)->whereNull('company_id')->count() === 0) {
                DB::statement(sprintf('ALTER TABLE `%s` MODIFY `company_id` BIGINT UNSIGNED NOT NULL', $table));
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexDefinitions as $table => $definitions) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $tableBlueprint) use ($definitions, $table): void {
                foreach ($definitions as $definition) {
                    if ($this->indexExists($table, $definition['name'])) {
                        $tableBlueprint->dropIndex($definition['name']);
                    }
                }
            });

            DB::statement(sprintf('ALTER TABLE `%s` MODIFY `company_id` BIGINT UNSIGNED NULL', $table));
        }
    }

    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $schemaBuilder = $connection->getSchemaBuilder();

        if (method_exists($schemaBuilder, 'hasIndex')) {
            return $schemaBuilder->hasIndex($table, $index);
        }

        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $tableName = str_replace("'", "''", $table);
            $results = DB::select("PRAGMA index_list('{$tableName}')");

            foreach ($results as $row) {
                $name = $row->name ?? $row->Name ?? null;

                if ($name === $index) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT COUNT(*) as aggregate FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?',
                [$table, $index]
            );

            return (int) ($result->aggregate ?? 0) > 0;
        }

        $databaseName = $connection->getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(*) as aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$databaseName, $table, $index]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};
