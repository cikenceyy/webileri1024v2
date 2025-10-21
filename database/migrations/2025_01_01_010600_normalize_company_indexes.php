<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'attachments',
            'customer_addresses',
            'customer_contacts',
            'customers',
            'media',
            'notes',
            'opportunities',
            'orders',
            'price_list_items',
            'price_lists',
            'product_categories',
            'product_gallery',
            'product_variants',
            'products',
            'shipments',
            'stock_items',
            'stock_movements',
            'warehouses',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
                continue;
            }

            if (! $this->hasIndex($table, $table . '_company_id_index')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->index('company_id');
                });
            }

            if (! $this->hasForeignKey($table, 'company_id')) {
                Schema::table($table, function (Blueprint $blueprint): void {
                    $blueprint->foreign('company_id')
                        ->references('id')
                        ->on('companies')
                        ->cascadeOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        // Bu düzeltme geri alınmaz.
    }

    private function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $schemaBuilder = $connection->getSchemaBuilder();

        if (method_exists($schemaBuilder, 'hasIndex')) {
            return $schemaBuilder->hasIndex($table, $index);
        }

        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $results = DB::select("PRAGMA index_list('{$table}')");

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
                'SELECT COUNT(*) AS aggregate FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?',
                [$table, $index]
            );

            return (int) ($result->aggregate ?? 0) > 0;
        }

        $database = $connection->getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $index]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $results = DB::select("PRAGMA foreign_key_list('{$table}')");

            foreach ($results as $row) {
                $from = $row->from ?? $row->From ?? null;

                if ($from === $column) {
                    return true;
                }
            }

            return false;
        }

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS aggregate FROM information_schema.constraint_column_usage WHERE table_name = ? AND column_name = ? AND constraint_name IN (
                    SELECT constraint_name FROM information_schema.table_constraints WHERE table_name = ? AND constraint_type = ?
                )',
                [$table, $column, $table, 'FOREIGN KEY']
            );

            return (int) ($result->aggregate ?? 0) > 0;
        }

        $database = $connection->getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.KEY_COLUMN_USAGE WHERE table_schema = ? AND table_name = ? AND column_name = ? AND referenced_table_name IS NOT NULL',
            [$database, $table, $column]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};
