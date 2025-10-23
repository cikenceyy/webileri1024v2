<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('page');
            $table->string('locale', 5);
            $table->string('field');
            $table->text('before')->nullable();
            $table->text('after')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'page', 'locale']);
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_audits');
    }
};
