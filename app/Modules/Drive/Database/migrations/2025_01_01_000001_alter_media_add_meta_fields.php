<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            if (! Schema::hasColumn('media', 'sha256')) {
                $table->string('sha256', 64)->nullable()->after('size');
            }

            if (! Schema::hasColumn('media', 'width')) {
                $table->unsignedInteger('width')->nullable()->after('sha256');
            }

            if (! Schema::hasColumn('media', 'height')) {
                $table->unsignedInteger('height')->nullable()->after('width');
            }

            if (! Schema::hasColumn('media', 'thumb_path')) {
                $table->string('thumb_path')->nullable()->after('height');
            }

            if (! Schema::hasColumn('media', 'size')) {
                $table->unsignedBigInteger('size')->default(0)->after('ext');
            }

            if (! Schema::hasColumn('media', 'original_name')) {
                $table->string('original_name')->after('path');
            }

            if (! Schema::hasColumn('media', 'mime')) {
                $table->string('mime', 128)->after('original_name');
            }

            if (! Schema::hasColumn('media', 'ext')) {
                $table->string('ext', 16)->after('mime');
            }

            $table->index('size');
            $table->index('sha256');
        });

        $driver = Schema::getConnection()->getDriverName();

        try {
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE media MODIFY size BIGINT UNSIGNED NOT NULL DEFAULT 0');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE media ALTER COLUMN size SET DEFAULT 0');
                DB::statement('UPDATE media SET size = 0 WHERE size IS NULL');
                DB::statement('ALTER TABLE media ALTER COLUMN size SET NOT NULL');
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        DB::table('media')->whereNotNull('ext')->update(['ext' => DB::raw('LOWER(ext)')]);
        DB::table('media')->whereNotNull('mime')->update(['mime' => DB::raw('LOWER(mime)')]);
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            if (Schema::hasColumn('media', 'thumb_path')) {
                $table->dropColumn('thumb_path');
            }

            if (Schema::hasColumn('media', 'height')) {
                $table->dropColumn('height');
            }

            if (Schema::hasColumn('media', 'width')) {
                $table->dropColumn('width');
            }

            if (Schema::hasColumn('media', 'sha256')) {
                $table->dropColumn('sha256');
            }

            $table->dropIndex(['size']);
            $table->dropIndex(['sha256']);
        });
    }
};
