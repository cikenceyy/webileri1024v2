<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('key', 32);
            $table->unsignedBigInteger('current')->default(0);
            $table->string('prefix', 32)->nullable();
            $table->unsignedTinyInteger('padding')->default(4);
            $table->enum('reset_period', ['none', 'yearly', 'monthly'])->default('yearly');
            $table->string('scope', 64)->nullable();
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'key', 'scope']);
            $table->index(['company_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequences');
    }
};
