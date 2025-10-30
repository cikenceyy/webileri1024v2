<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company domain kayıtlarını normalize eder ve host sütun adını günceller.
     */
    public function up(): void
    {
        Schema::table('company_domains', function (Blueprint $table): void {
            if (Schema::hasColumn('company_domains', 'domain') && ! Schema::hasColumn('company_domains', 'host')) {
                $table->string('host')->after('company_id')->nullable();
            }
        });

        if (Schema::hasColumn('company_domains', 'domain')) {
            DB::table('company_domains')->orderBy('id')->lazyById()->each(function ($record): void {
                $host = strtolower(trim((string) ($record->domain ?? '')));
                $host = rtrim($host, '.');

                DB::table('company_domains')
                    ->where('id', $record->id)
                    ->update(['host' => $host]);
            });
        }

        $indexExists = fn (string $index): bool => $this->indexExists('company_domains', $index);

        Schema::table('company_domains', function (Blueprint $table) use ($indexExists): void {
            if (Schema::hasColumn('company_domains', 'domain')) {
                if ($indexExists('company_domains_domain_unique')) {
                    $table->dropUnique('company_domains_domain_unique');
                }

                $table->dropColumn('domain');
            }

            $table->string('host')->nullable(false)->change();

            if (! $indexExists('company_domains_host_unique')) {
                $table->unique('host', 'company_domains_host_unique');
            }
        });
    }

    /**
     * Geri dönüşte host sütununu domain adına çevirir.
     */
    public function down(): void
    {
        $indexExists = fn (string $index): bool => $this->indexExists('company_domains', $index);

        Schema::table('company_domains', function (Blueprint $table) use ($indexExists): void {
            if (! Schema::hasColumn('company_domains', 'domain')) {
                $table->string('domain')->nullable()->after('company_id');
            }
        });

        if (Schema::hasColumn('company_domains', 'host')) {
            DB::table('company_domains')->orderBy('id')->lazyById()->each(function ($record): void {
                $domain = strtolower(trim((string) ($record->host ?? '')));
                $domain = rtrim($domain, '.');

                DB::table('company_domains')
                    ->where('id', $record->id)
                    ->update(['domain' => $domain]);
            });
        }

        Schema::table('company_domains', function (Blueprint $table) use ($indexExists): void {
            if (Schema::hasColumn('company_domains', 'host')) {
                if ($indexExists('company_domains_host_unique')) {
                    $table->dropUnique('company_domains_host_unique');
                }

                $table->dropColumn('host');
            }

            $table->string('domain')->nullable(false)->change();

            if (! $indexExists('company_domains_domain_unique')) {
                $table->unique('domain', 'company_domains_domain_unique');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();

        if (method_exists($connection->getSchemaBuilder(), 'hasIndex')) {
            return $connection->getSchemaBuilder()->hasIndex($table, $index);
        }

        $driver = $connection->getDriverName();

        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                'SELECT COUNT(*) as aggregate FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ? AND indexname = ?',
                [$table, $index]
            );

            return (int) ($result->aggregate ?? 0) > 0;
        }

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

        $databaseName = $connection->getDatabaseName();
        $result = DB::selectOne(
            'SELECT COUNT(*) as aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$databaseName, $table, $index]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};
