<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * E-posta gönderim özetlerini şirket bazında arşivleyen log tablosunu oluşturur.
     */
    public function up(): void
    {
        Schema::create('company_email_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('subject')->nullable();
            $table->string('status', 20);
            $table->json('recipients');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    /**
     * E-posta log tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_email_logs');
    }
};
