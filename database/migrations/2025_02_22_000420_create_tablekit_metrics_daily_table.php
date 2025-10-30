<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TableKit günlük özet metriklerini saklayan tabloyu oluşturur.
 * Amaç: Tenant bazında p95 süre ve cache oranlarını raporlamak.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tablekit_metrics_daily', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('table_key');
            $table->date('date');
            $table->unsignedInteger('request_count');
            $table->unsignedInteger('avg_total_time_ms');
            $table->unsignedInteger('p95_total_time_ms');
            $table->unsignedInteger('avg_query_count');
            $table->unsignedInteger('avg_db_time_ms');
            $table->unsignedDecimal('cache_hit_ratio', 5, 2);
            $table->timestamps();

            $table->unique(['company_id', 'table_key', 'date'], 'tablekit_metrics_daily_unique');
            $table->index(['company_id', 'date'], 'tablekit_metrics_daily_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tablekit_metrics_daily');
    }
};
