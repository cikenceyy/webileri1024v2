<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            if (! Schema::hasColumn('companies', 'theme_color')) {
                $table->string('theme_color', 16)->nullable();
            }

            if (! Schema::hasColumn('companies', 'logo_id')) {
                $table->foreignId('logo_id')
                    ->nullable()
                    ->after('domain')
                    ->constrained('media')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            if (Schema::hasColumn('companies', 'logo_id')) {
                $table->dropForeign(['logo_id']);
                $table->dropColumn('logo_id');
            }
        });
    }
};
