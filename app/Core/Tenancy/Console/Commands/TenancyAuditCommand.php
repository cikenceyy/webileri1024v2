<?php

namespace App\Core\Tenancy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TenancyAuditCommand extends Command
{
    protected $signature = 'webileri:tenancy:audit {--log= : Path to the audit log file}';

    protected $description = 'Audit tenant tables for missing company scoping or mismatched relationships.';

    public function handle(): int
    {
        $logPath = $this->option('log') ?: storage_path('logs/tenancy_audit.log');

        $tables = $this->tableNames();
        $tenantTables = array_values(array_filter($tables, fn (string $table) => Schema::hasColumn($table, 'company_id')));

        $reportLines = [];
        $reportLines[] = '--- Tenancy audit run at ' . now()->toDateTimeString() . ' ---';

        foreach ($tenantTables as $table) {
            $nullCount = DB::table($table)->whereNull('company_id')->count();
            $reportLines[] = sprintf('%s: %d record(s) with NULL company_id', $table, $nullCount);

            if ($nullCount > 0) {
                $this->warn("{$table}: {$nullCount} record(s) missing company_id");
            }

            foreach ($this->detectRelationColumns($table) as $relationTable => $column) {
                if (! in_array($relationTable, $tenantTables, true)) {
                    continue;
                }

                $mismatch = $this->countCompanyMismatch($table, $relationTable, $column);

                $reportLines[] = sprintf(
                    '  - %s.%s vs %s.company_id mismatches: %d',
                    $table,
                    $column,
                    $relationTable,
                    $mismatch
                );

                if ($mismatch > 0) {
                    $this->error("{$table}.{$column} has {$mismatch} cross-tenant relation(s) with {$relationTable}");
                }
            }
        }

        $reportLines[] = '--- End of tenancy audit ---';

        $this->writeReport($logPath, $reportLines);

        $this->info('Tenancy audit complete. Report stored at: ' . $logPath);

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function tableNames(): array
    {
        $connection = Schema::getConnection();

        try {
            $schemaManager = $connection->getDoctrineSchemaManager();
            $tables = $schemaManager->listTableNames();

            sort($tables);

            return $tables;
        } catch (\Throwable $exception) {
            $driver = $connection->getDriverName();

            if ($driver === 'mysql') {
                $database = $connection->getDatabaseName();
                $rows = $connection->select(
                    'SELECT table_name FROM information_schema.tables WHERE table_schema = ? ORDER BY table_name',
                    [$database]
                );

                return array_map(fn ($row) => $row->table_name ?? $row->TABLE_NAME ?? reset($row), $rows);
            }

            if ($driver === 'pgsql') {
                $rows = $connection->select(
                    "SELECT tablename AS table_name FROM pg_tables WHERE schemaname = current_schema() ORDER BY tablename"
                );

                return array_map(fn ($row) => $row->table_name ?? reset($row), $rows);
            }

            if ($driver === 'sqlite') {
                $rows = $connection->select("SELECT name AS table_name FROM sqlite_master WHERE type = 'table'");

                return array_map(fn ($row) => $row->table_name ?? $row->name ?? reset($row), $rows);
            }

            throw $exception;
        }
    }

    /**
     * @return array<string, string>
     */
    protected function detectRelationColumns(string $table): array
    {
        $columns = Schema::getColumnListing($table);

        $relations = [];

        foreach ($columns as $column) {
            if ($column === 'company_id' || ! str_ends_with($column, '_id')) {
                continue;
            }

            $base = Str::beforeLast($column, '_id');
            $candidates = $this->relationCandidates($base);

            foreach ($candidates as $candidate) {
                if (Schema::hasTable($candidate) && Schema::hasColumn($candidate, 'id')) {
                    $relations[$candidate] = $column;
                    break;
                }
            }
        }

        return $relations;
    }

    /**
     * @return array<int, string>
     */
    protected function relationCandidates(string $base): array
    {
        $studly = Str::studly($base);

        return array_unique(array_filter([
            Str::snake($base),
            Str::snake(Str::pluralStudly($studly)),
            Str::snake($studly),
            Str::snake(Str::plural($base)),
            Str::snake(Str::camel($base)),
        ]));
    }

    protected function countCompanyMismatch(string $table, string $relationTable, string $column): int
    {
        try {
            return (int) DB::table($table)
                ->join($relationTable, $relationTable . '.id', '=', $table . '.' . $column)
                ->whereColumn($table . '.company_id', '!=', $relationTable . '.company_id')
                ->limit(1)
                ->count();
        } catch (\Throwable $exception) {
            $this->warn(sprintf('Skipped relation check for %s.%s -> %s (%s)', $table, $column, $relationTable, $exception->getMessage()));

            return 0;
        }
    }

    protected function writeReport(string $path, array $lines): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
