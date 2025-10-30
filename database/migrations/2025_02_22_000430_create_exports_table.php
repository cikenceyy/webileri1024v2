<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TableKit veri ihracat isteklerini kuyruk bazlı yönetmek için kayıt tablosu.
 * Amaç: Export ilerlemesini, dosya yolunu ve saklama süresini izlemek.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('exports', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->string('table_key');
            $table->string('format', 10);
            $table->string('status', 20);
            $table->json('params')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('file_path')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('retention_until')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'table_key'], 'exports_lookup');
            $table->index(['status']);
            $table->index(['retention_until'], 'exports_retention_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
