<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Çoklu ve ağır işlemleri kuyruklayarak izlenebilir hale getiren bulk_jobs tablosunu oluşturur.
 */
return new class
{
    public function up(): void
    {
        Schema::create('bulk_jobs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('module');
            $table->string('action');
            $table->json('params')->nullable();
            $table->enum('status', ['pending', 'running', 'done', 'failed'])->default('pending');
            $table->unsignedInteger('progress')->default(0);
            $table->unsignedInteger('items_total')->default(0);
            $table->unsignedInteger('items_done')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'module']);
            $table->index(['company_id', 'status']);
            $table->unique(['company_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_jobs');
    }
};
