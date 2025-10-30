<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TableKit listeleri için kullanıcıya özel kaydedilmiş filtre tablolarını oluşturur.
 * Amaç: Her şirket ve kullanıcı kombinasyonu için kişisel filtreleri saklayabilmek.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('tablekit_filters', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('table_key');
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->json('payload');
            $table->timestamps();

            $table->unique(['company_id', 'user_id', 'table_key', 'name'], 'tablekit_filter_unique');
            $table->index(['company_id', 'user_id', 'table_key'], 'tablekit_filter_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tablekit_filters');
    }
};
