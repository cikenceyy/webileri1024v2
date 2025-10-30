<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drive dosyalarını modül kayıtlarına iliştirmek için pivot tablo oluşturur.
 * Her kayıt şirket bazında tutulur ve cache etiketleriyle temizlenebilir.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('drive_attachments', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->unsignedBigInteger('media_id');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'attachable_type', 'attachable_id'], 'drive_attach_lookup');
            $table->index(['company_id', 'media_id']);
            $table->unique(['company_id', 'attachable_type', 'attachable_id', 'media_id'], 'drive_attach_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drive_attachments');
    }
};
