<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issued_numbers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('key', 32);
            $table->string('idempotency_key', 64);
            $table->string('number', 64);
            $table->string('context_hash', 128)->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'key', 'idempotency_key']);
            $table->index(['company_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_numbers');
    }
};
