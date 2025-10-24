<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sequence_numbers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('key', 64);
            $table->string('prefix', 32);
            $table->unsignedTinyInteger('padding')->default(6);
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'key', 'year']);
            $table->index(['company_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_numbers');
    }
};
