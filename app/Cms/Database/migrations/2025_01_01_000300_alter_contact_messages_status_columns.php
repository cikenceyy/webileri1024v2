<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
            if (!Schema::hasColumn('contact_messages', 'responded_at')) {
                $table->timestamp('responded_at')->nullable()->after('read_at');
            }
            if (!Schema::hasColumn('contact_messages', 'status')) {
                $table->string('status')->default('new')->after('responded_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            if (Schema::hasColumn('contact_messages', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('contact_messages', 'responded_at')) {
                $table->dropColumn('responded_at');
            }
            if (Schema::hasColumn('contact_messages', 'read_at')) {
                $table->dropColumn('read_at');
            }
        });
    }
};
