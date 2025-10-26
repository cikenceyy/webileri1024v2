<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            if (! Schema::hasColumn('media', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('disk');
            }

            if (! Schema::hasColumn('media', 'category')) {
                $table->string('category', 64)->after('company_id');
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE media MODIFY category VARCHAR(64)');
                DB::statement('ALTER TABLE media MODIFY uuid CHAR(36) NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE media ALTER COLUMN category TYPE VARCHAR(64)');
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        DB::table('media')
            ->whereNull('uuid')
            ->orderBy('id')
            ->chunkById(100, function ($items): void {
                foreach ($items as $item) {
                    DB::table('media')
                        ->where('id', $item->id)
                        ->update(['uuid' => Str::uuid()->toString()]);
                }
            });

        $indexes = $this->getIndexes('media');

        Schema::table('media', function (Blueprint $table) use ($indexes): void {
            if (! in_array('media_uuid_unique', $indexes, true)) {
                $table->unique('uuid', 'media_uuid_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            if (Schema::hasColumn('media', 'uuid')) {
                $table->dropUnique('media_uuid_unique');
                $table->dropColumn('uuid');
            }
        });
    }

    private function getIndexes(string $table): array
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        try {
            if ($driver === 'mysql') {
                return collect(DB::select('SHOW INDEX FROM ' . $connection->getTablePrefix() . $table))
                    ->pluck('Key_name')
                    ->unique()
                    ->values()
                    ->all();
            }

            if ($driver === 'pgsql') {
                return collect(DB::select('SELECT indexname FROM pg_indexes WHERE tablename = ?', [$table]))
                    ->pluck('indexname')
                    ->unique()
                    ->values()
                    ->all();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return [];
    }
};
