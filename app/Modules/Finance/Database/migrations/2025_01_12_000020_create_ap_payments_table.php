<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ap_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ap_invoice_id')->constrained('ap_invoices')->cascadeOnDelete();
            $table->date('paid_at');
            $table->decimal('amount', 12, 2);
            $table->string('method', 50)->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['ap_invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ap_payments');
    }
};
