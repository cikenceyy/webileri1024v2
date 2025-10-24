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
            if (! Schema::hasColumn('media', 'module')) {
                $table->string('module', 64)->default('cms')->after('category');
                $table->index('module');
            }
        });

        DB::table('media')->whereNull('module')->update(['module' => 'cms']);
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            if (Schema::hasColumn('media', 'module')) {
                $table->dropIndex(['module']);
                $table->dropColumn('module');
            }
        });
    }
};
