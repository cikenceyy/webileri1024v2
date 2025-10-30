<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cold rapor çıktılarının meta bilgilerini saklayan snapshot tablosunu kurar.
 */
return new class
{
    public function up(): void
    {
        Schema::create('report_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('report_key');
            $table->string('params_hash');
            $table->enum('status', ['pending', 'running', 'ready', 'failed'])->default('pending');
            $table->unsignedInteger('rows')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->string('storage_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'report_key', 'params_hash']);
            $table->index(['company_id', 'report_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_snapshots');
    }
};
