<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TableKit istekleri için anlık ölçüm kayıtlarını tutar.
 * Amaç: Her liste talebinin süre, sorgu sayısı ve cache başarısını izlemek.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tablekit_metrics', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('table_key');
            $table->timestamp('ts');
            $table->unsignedInteger('rows');
            $table->unsignedInteger('query_count');
            $table->unsignedInteger('db_time_ms');
            $table->unsignedInteger('total_time_ms');
            $table->boolean('cache_hit');
            $table->string('filters_hash', 64);
            $table->timestamps();

            $table->index(['company_id', 'table_key', 'ts'], 'tablekit_metric_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tablekit_metrics');
    }
};
