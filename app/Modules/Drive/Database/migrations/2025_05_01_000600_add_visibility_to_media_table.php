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
            if (! Schema::hasColumn('media', 'visibility')) {
                $table->string('visibility', 16)->default('private')->after('disk');
                $table->index('visibility');
            }
        });

        DB::table('media')->whereNull('visibility')->update(['visibility' => 'private']);
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            if (Schema::hasColumn('media', 'visibility')) {
                $table->dropIndex(['visibility']);
                $table->dropColumn('visibility');
            }
        });
    }
};
