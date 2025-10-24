<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_ledger') && DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE stock_ledger MODIFY COLUMN reason ENUM('transfer','count_adjust','manual','wo_issue','wo_receipt')");
        }

        if (! Schema::hasTable('boms')) {
            Schema::create('boms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('code', 64);
                $table->unsignedInteger('version')->default(1);
                $table->decimal('output_qty', 18, 4)->default(1);
                $table->boolean('is_active')->default(true);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'code']);
                $table->unique(['company_id', 'product_id', 'variant_id', 'version']);
            });
        }

        if (! Schema::hasTable('bom_items')) {
            Schema::create('bom_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('bom_id')->constrained('boms')->cascadeOnDelete();
                $table->foreignId('component_product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('component_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->decimal('qty_per', 18, 6);
                $table->decimal('wastage_pct', 5, 2)->default(0);
                $table->foreignId('default_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('default_bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('work_orders')) {
            Schema::create('work_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('doc_no', 64);
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->foreignId('bom_id')->constrained('boms');
                $table->decimal('target_qty', 18, 4);
                $table->string('uom', 16)->default('pcs');
                $table->enum('status', ['draft', 'released', 'in_progress', 'completed', 'closed', 'cancelled'])->default('draft');
                $table->date('due_date')->nullable();
                $table->dateTime('started_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->string('source_type', 80)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'doc_no']);
                $table->index(['company_id', 'status']);
            });
        }

        if (! Schema::hasTable('work_order_issues')) {
            Schema::create('work_order_issues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
                $table->foreignId('component_product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('component_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->decimal('qty', 18, 4);
                $table->dateTime('posted_at');
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('work_order_receipts')) {
            Schema::create('work_order_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses');
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->decimal('qty', 18, 4);
                $table->dateTime('posted_at');
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('work_order_operations')) {
            Schema::create('work_order_operations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
                $table->string('name', 80);
                $table->text('notes')->nullable();
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_operations');
        Schema::dropIfExists('work_order_receipts');
        Schema::dropIfExists('work_order_issues');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('boms');

        if (Schema::hasTable('stock_ledger') && DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE stock_ledger MODIFY COLUMN reason ENUM('transfer','count_adjust','manual')");
        }
    }
};
