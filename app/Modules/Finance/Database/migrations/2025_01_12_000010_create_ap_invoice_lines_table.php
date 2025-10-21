<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ap_invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ap_invoice_id')->constrained('ap_invoices')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('qty', 12, 3)->default(0);
            $table->string('unit', 20)->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('source_type', 50)->nullable();
            $table->uuid('source_uuid')->nullable();
            $table->timestamps();

            $table->index(['ap_invoice_id']);
            $table->index(['source_type', 'source_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_invoice_lines');
    }
};
