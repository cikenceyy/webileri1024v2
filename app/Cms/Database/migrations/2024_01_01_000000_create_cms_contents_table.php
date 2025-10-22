<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('page');
            $table->string('locale', 5)->default('tr');
            $table->json('data')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'page', 'locale']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_contents');
    }
};
