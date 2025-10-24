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
            DB::statement("ALTER TABLE stock_ledger MODIFY COLUMN reason ENUM('transfer','count_adjust','manual','wo_issue','wo_receipt','shipment','grn')");
        }

        if (! Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('doc_no', 64);
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('source_type', 80)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->enum('status', ['draft', 'picking', 'packed', 'shipped', 'closed', 'cancelled'])->default('draft');
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->unsignedInteger('packages_count')->nullable();
                $table->decimal('gross_weight', 14, 3)->nullable();
                $table->decimal('net_weight', 14, 3)->nullable();
                $table->dateTime('shipped_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'doc_no']);
                $table->index(['company_id', 'status']);
            });
        }

        if (! Schema::hasTable('shipment_lines')) {
            Schema::create('shipment_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('source_line_type', 80)->nullable();
                $table->unsignedBigInteger('source_line_id')->nullable();
                $table->decimal('qty', 18, 4);
                $table->string('uom', 16)->default('pcs');
                $table->decimal('picked_qty', 18, 4)->default(0);
                $table->decimal('packed_qty', 18, 4)->default(0);
                $table->decimal('shipped_qty', 18, 4)->default(0);
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->unsignedInteger('sort')->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('goods_receipts')) {
            Schema::create('goods_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('doc_no', 64);
                $table->unsignedBigInteger('vendor_id')->nullable();
                $table->string('source_type', 80)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->enum('status', ['draft', 'received', 'reconciled', 'closed', 'cancelled'])->default('draft');
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->dateTime('received_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'doc_no']);
                $table->index(['company_id', 'status']);
            });
        }

        if (! Schema::hasTable('goods_receipt_lines')) {
            Schema::create('goods_receipt_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->string('source_line_type', 80)->nullable();
                $table->unsignedBigInteger('source_line_id')->nullable();
                $table->decimal('qty_expected', 18, 4)->nullable();
                $table->decimal('qty_received', 18, 4);
                $table->decimal('variance', 18, 4)->storedAs('`qty_received` - COALESCE(`qty_expected`, 0)');
                $table->string('variance_reason', 40)->nullable();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('bin_id')->nullable()->constrained('warehouse_bins')->nullOnDelete();
                $table->unsignedInteger('sort')->default(0);
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('variance_reasons')) {
            Schema::create('variance_reasons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->string('code', 32);
                $table->string('name');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_lines');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('shipment_lines');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('variance_reasons');

        if (Schema::hasTable('stock_ledger') && DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE stock_ledger MODIFY COLUMN reason ENUM('transfer','count_adjust','manual','wo_issue','wo_receipt')");
        }
    }
};
