<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kiracı domain kayıtları için temel tabloyu oluşturur.
     * Host değerleri her zaman küçük harfe normalize edilir ve global tekillik sağlanır.
     */
    public function up(): void
    {
        Schema::create('company_domains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('host')->unique('company_domains_host_unique');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_domains');
    }
};
