<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Şirket bazlı yapılandırma anahtarlarını saklayan temel ayar tablosunu oluşturur.
     */
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('key');
            $table->string('type', 20);
            $table->text('value')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'key']);
            $table->index(['company_id', 'key']);
        });
    }

    /**
     * company_settings tablosunu geri siler.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
