<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 64);
            $table->string('name', 255);
            $table->decimal('price', 12, 2)->default(0);
            $table->string('unit', 16)->default('pcs');
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('status', 16)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'status']);
            $table->index('media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
