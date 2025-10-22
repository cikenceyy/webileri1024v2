<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            if (! Schema::hasColumn('companies', 'drive_storage_limit_bytes')) {
                $table->unsignedBigInteger('drive_storage_limit_bytes')->default(1_073_741_824)->after('logo_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            if (Schema::hasColumn('companies', 'drive_storage_limit_bytes')) {
                $table->dropColumn('drive_storage_limit_bytes');
            }
        });
    }
};
