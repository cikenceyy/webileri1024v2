<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modül şablonunun örnek migration dosyası.
 * Tablo adını ve sütunları gerçek modül ihtiyaçlarına göre düzenleyin.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('example_items', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->timestamps();

            $table->index(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('example_items');
    }
};
