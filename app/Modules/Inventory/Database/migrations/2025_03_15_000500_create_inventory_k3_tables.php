<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('warehouses', 'is_active')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('name');
            });
        }

        if (! Schema::hasTable('warehouse_bins')) {
            Schema::create('warehouse_bins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->string('code', 32);
                $table->string('name');
                $table->timestamps();

                $table->unique(['company_id', 'warehouse_id', 'code']);
            });
        }

        if (! Schema::hasTable('stock_ledger')) {
            Schema::create('stock_ledger', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->decimal('qty_in', 18, 4)->default(0);
                $table->decimal('qty_out', 18, 4)->default(0);
                $table->enum('reason', ['transfer', 'count_adjust', 'manual']);
                $table->string('ref_type', 64)->nullable();
                $table->unsignedBigInteger('ref_id')->nullable();
                $table->string('doc_no', 64)->nullable();
                $table->dateTime('dated_at');
                $table->timestamps();

                $table->index(['company_id', 'product_id', 'dated_at']);
            });
        }

        if (! Schema::hasTable('stock_transfers')) {
            Schema::create('stock_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('doc_no', 64);
                $table->foreignId('from_warehouse_id')->constrained('warehouses');
                $table->foreignId('from_bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->foreignId('to_warehouse_id')->constrained('warehouses');
                $table->foreignId('to_bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->enum('status', ['draft', 'posted'])->default('draft');
                $table->dateTime('posted_at')->nullable();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['company_id', 'doc_no']);
                $table->index(['company_id', 'status']);
            });
        }

        if (! Schema::hasTable('stock_transfer_lines')) {
            Schema::create('stock_transfer_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('qty', 18, 4);
                $table->string('note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stock_counts')) {
            Schema::create('stock_counts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('doc_no', 64);
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->enum('status', ['draft', 'counted', 'reconciled'])->default('draft');
                $table->dateTime('counted_at')->nullable();
                $table->dateTime('reconciled_at')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'doc_no']);
                $table->index(['company_id', 'status']);
            });
        }

        if (! Schema::hasTable('stock_count_lines')) {
            Schema::create('stock_count_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('count_id')->constrained('stock_counts')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->decimal('qty_expected', 18, 4)->nullable();
                $table->decimal('qty_counted', 18, 4);
                $table->decimal('diff_cached', 18, 4)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('product_categories', 'slug')) {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
                $table->boolean('is_active')->default(true)->after('slug');
            });

            DB::table('product_categories')
                ->select('id', 'code', 'name')
                ->chunkById(100, function ($categories) {
                    foreach ($categories as $category) {
                        $slugSource = $category->code ?: ($category->name ?: 'kategori-' . $category->id);
                        $slug = Str::slug($slugSource);

                        DB::table('product_categories')->where('id', $category->id)->update([
                            'slug' => $slug ?: 'kategori-' . $category->id,
                        ]);
                    }
                });

            Schema::table('product_categories', function (Blueprint $table) {
                $table->unique(['company_id', 'parent_id', 'slug'], 'product_categories_company_parent_slug_unique');
            });
        }

        if (! Schema::hasTable('variant_attributes')) {
            Schema::create('variant_attributes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 32);
                $table->timestamps();

                $table->unique(['company_id', 'code']);
            });
        }

        if (! Schema::hasTable('variant_attribute_values')) {
            Schema::create('variant_attribute_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('attribute_id')->constrained('variant_attributes')->cascadeOnDelete();
                $table->string('value');
                $table->string('code', 32);
                $table->timestamps();

                $table->unique(['company_id', 'attribute_id', 'code']);
            });
        }

        if (! Schema::hasTable('product_variant_values')) {
            Schema::create('product_variant_values', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->foreignId('attribute_id')->constrained('variant_attributes')->cascadeOnDelete();
                $table->foreignId('value_id')->constrained('variant_attribute_values')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_variant_id', 'attribute_id', 'value_id'], 'product_variant_attr_value_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('variant_attribute_values');
        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('stock_count_lines');
        Schema::dropIfExists('stock_counts');
        Schema::dropIfExists('stock_transfer_lines');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_ledger');
        Schema::dropIfExists('warehouse_bins');

        if (Schema::hasColumn('product_categories', 'slug')) {
            Schema::table('product_categories', function (Blueprint $table) {
                $table->dropUnique('product_categories_company_parent_slug_unique');
                $table->dropColumn(['slug', 'is_active']);
            });
        }

        if (Schema::hasColumn('warehouses', 'is_active')) {
            Schema::table('warehouses', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
