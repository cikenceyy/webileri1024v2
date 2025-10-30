<?php

/**
 * Yetki reddi denemelerini kaydederek hangi kullanıcının hangi kaynağa erişemediğini
 * geçmişte incelememizi sağlar; yalnızca log amaçlıdır.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Yetki denemelerini kayıt altına alarak güvenlik incelemelerine destek verir.
     */
    public function up(): void
    {
        Schema::create('auth_audits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 150);
            $table->string('resource')->nullable();
            $table->enum('result', ['allowed', 'denied'])->default('denied')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Audit tablosunu kaldırır.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_audits');
    }
};
