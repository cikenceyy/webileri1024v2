<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Eğer doğru tablo zaten varsa, dokunma
        if (Schema::hasTable('product_galleries')) {
            return;
        }

        // Eski/yanlış isimle oluşturulduysa yeniden adlandır
        if (Schema::hasTable('product_gallery')) {
            Schema::rename('product_gallery', 'product_galleries');
            return;
        }

        // Hiç yoksa doğru tabloyu oluştur
        Schema::create('product_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['company_id','product_id','media_id'], 'pg_company_product_media_unique');
            $table->index(['product_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('product_galleries')) {
            Schema::drop('product_galleries');
        }
    }
};
