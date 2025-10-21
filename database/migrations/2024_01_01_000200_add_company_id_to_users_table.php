<?php

use App\Core\Support\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            if ($this->indexExists('users', 'users_email_unique')) {
                Schema::table('users', function (Blueprint $table): void {
                    $table->dropUnique('users_email_unique');
                });
            }

            if (! Schema::hasColumn('users', 'company_id')) {
                Schema::table('users', function (Blueprint $table): void {
                    $table->foreignId('company_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('companies')
                        ->cascadeOnDelete();
                });
            }

            if (Schema::hasColumn('users', 'company_id')) {
                $companyCount = Company::query()->count();

                if ($companyCount === 1) {
                    $companyId = Company::query()->value('id');

                    if ($companyId) {
                        DB::table('users')
                            ->whereNull('company_id')
                            ->update(['company_id' => $companyId]);
                    }
                }

                if (! $this->indexExists('users', 'users_company_email_unique')) {
                    Schema::table('users', function (Blueprint $table): void {
                        $table->unique(['company_id', 'email'], 'users_company_email_unique');
                    });
                }

                if (! DB::table('users')->whereNull('company_id')->exists()) {
                    DB::statement('ALTER TABLE `users` MODIFY `company_id` BIGINT UNSIGNED NOT NULL');
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'company_id')) {
                if ($this->indexExists('users', 'users_company_email_unique')) {
                    $table->dropUnique('users_company_email_unique');
                }

                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }

            if (! $this->indexExists('users', 'users_email_unique')) {
                $table->unique('email');
            }
        });
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
