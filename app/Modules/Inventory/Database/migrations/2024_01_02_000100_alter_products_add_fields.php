<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('company_id')->constrained('product_categories');
            }

            if (! Schema::hasColumn('products', 'barcode')) {
                $table->string('barcode', 64)->nullable()->after('sku');
            }

            if (! Schema::hasColumn('products', 'base_unit_id')) {
                $table->foreignId('base_unit_id')->nullable()->after('unit')->constrained('units');
            }

            if (! Schema::hasColumn('products', 'reorder_point')) {
                $table->decimal('reorder_point', 14, 3)->default(0)->after('unit');
            }

            if (! Schema::hasColumn('products', 'status')) {
                $table->string('status', 16)->default('active')->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }

            if (Schema::hasColumn('products', 'barcode')) {
                $table->dropColumn('barcode');
            }

            if (Schema::hasColumn('products', 'base_unit_id')) {
                $table->dropConstrainedForeignId('base_unit_id');
            }

            if (Schema::hasColumn('products', 'reorder_point')) {
                $table->dropColumn('reorder_point');
            }

            if (Schema::hasColumn('products', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
