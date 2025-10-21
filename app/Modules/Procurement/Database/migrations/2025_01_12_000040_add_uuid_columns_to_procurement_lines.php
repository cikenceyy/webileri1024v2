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
        Schema::table('po_lines', function (Blueprint $table): void {
            if (! Schema::hasColumn('po_lines', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
                $table->unique('uuid', 'po_lines_uuid_unique');
            }
        });

        Schema::table('grn_lines', function (Blueprint $table): void {
            if (! Schema::hasColumn('grn_lines', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id');
                $table->unique('uuid', 'grn_lines_uuid_unique');
            }
        });

        DB::table('po_lines')
            ->select('id', 'uuid')
            ->lazy()
            ->each(function ($row): void {
                if (! $row->uuid) {
                    DB::table('po_lines')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
                }
            });

        DB::table('grn_lines')
            ->select('id', 'uuid')
            ->lazy()
            ->each(function ($row): void {
                if (! $row->uuid) {
                    DB::table('grn_lines')->where('id', $row->id)->update(['uuid' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('po_lines', function (Blueprint $table): void {
            if (Schema::hasColumn('po_lines', 'uuid')) {
                $table->dropUnique('po_lines_uuid_unique');
                $table->dropColumn('uuid');
            }
        });

        Schema::table('grn_lines', function (Blueprint $table): void {
            if (Schema::hasColumn('grn_lines', 'uuid')) {
                $table->dropUnique('grn_lines_uuid_unique');
                $table->dropColumn('uuid');
            }
        });
    }
};
