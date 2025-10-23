<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'collection_lane')) {
                $table->string('collection_lane', 32)->default('today')->after('status');
                $table->index(['company_id', 'collection_lane']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'collection_lane')) {
                $table->dropIndex(['company_id', 'collection_lane']);
                $table->dropColumn('collection_lane');
            }
        });
    }
};
