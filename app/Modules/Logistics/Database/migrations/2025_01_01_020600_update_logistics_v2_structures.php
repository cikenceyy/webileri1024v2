<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('carriers')) {
            Schema::create('carriers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->string('code', 32);
                $table->string('name', 128);
                $table->string('contact_phone', 32)->nullable();
                $table->string('tracking_url')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();

                $table->unique(['company_id', 'code']);
            });
        }

        if (! Schema::hasTable('shipment_lines')) {
            Schema::create('shipment_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
                $orderLine = $table->foreignId('order_line_id')->nullable();
                if (Schema::hasTable('order_lines')) {
                    $orderLine->constrained('order_lines')->nullOnDelete();
                }
                $product = $table->foreignId('product_id')->nullable();
                if (Schema::hasTable('products')) {
                    $product->constrained('products')->nullOnDelete();
                }
                $variant = $table->foreignId('variant_id')->nullable();
                if (Schema::hasTable('product_variants')) {
                    $variant->constrained('product_variants')->nullOnDelete();
                }
                $table->string('description');
                $table->decimal('quantity', 14, 3);
                $table->string('unit', 16)->default('pcs');
                $table->decimal('weight_kg', 10, 3)->nullable();
                $table->text('notes')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['company_id', 'shipment_id']);
            });
        }

        if (! Schema::hasTable('shipment_packages')) {
            Schema::create('shipment_packages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
                $table->string('reference', 64)->nullable();
                $table->decimal('weight_kg', 10, 3)->nullable();
                $table->decimal('length_cm', 10, 2)->nullable();
                $table->decimal('width_cm', 10, 2)->nullable();
                $table->decimal('height_cm', 10, 2)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'shipment_id']);
            });
        }

        if (! Schema::hasTable('tracking_events')) {
            Schema::create('tracking_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
                $table->string('status', 64);
                $table->string('location', 128)->nullable();
                $table->text('description')->nullable();
                $table->timestamp('recorded_at')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'shipment_id']);
            });
        }

        Schema::table('shipments', function (Blueprint $table) {
            if (! Schema::hasColumn('shipments', 'warehouse_id')) {
                $column = $table->foreignId('warehouse_id')->nullable()->after('order_id');
                if (Schema::hasTable('warehouses')) {
                    $column->constrained('warehouses')->nullOnDelete();
                }
            }
            if (! Schema::hasColumn('shipments', 'carrier_id')) {
                $table->foreignId('carrier_id')->nullable()->after('warehouse_id')->constrained('carriers')->nullOnDelete();
            }
            if (! Schema::hasColumn('shipments', 'carrier')) {
                $table->string('carrier', 128)->nullable()->after('carrier_id');
            }
            if (! Schema::hasColumn('shipments', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->nullable()->after('volume_dm3');
            }
            if (! Schema::hasColumn('shipments', 'picking_started_at')) {
                $table->timestamp('picking_started_at')->nullable()->after('ship_date');
            }
            if (! Schema::hasColumn('shipments', 'packed_at')) {
                $table->timestamp('packed_at')->nullable()->after('picking_started_at');
            }
            if (! Schema::hasColumn('shipments', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('packed_at');
            }
            if (! Schema::hasColumn('shipments', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            }
            if (! Schema::hasColumn('shipments', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('delivered_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'returned_at')) {
                $table->dropColumn('returned_at');
            }
            if (Schema::hasColumn('shipments', 'delivered_at')) {
                $table->dropColumn('delivered_at');
            }
            if (Schema::hasColumn('shipments', 'shipped_at')) {
                $table->dropColumn('shipped_at');
            }
            if (Schema::hasColumn('shipments', 'packed_at')) {
                $table->dropColumn('packed_at');
            }
            if (Schema::hasColumn('shipments', 'picking_started_at')) {
                $table->dropColumn('picking_started_at');
            }
            if (Schema::hasColumn('shipments', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }
            if (Schema::hasColumn('shipments', 'carrier_id')) {
                $table->dropConstrainedForeignId('carrier_id');
            }
            if (Schema::hasColumn('shipments', 'warehouse_id')) {
                try {
                    $table->dropConstrainedForeignId('warehouse_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('warehouse_id');
                }
            }
        });

        Schema::dropIfExists('tracking_events');
        Schema::dropIfExists('shipment_packages');
        Schema::dropIfExists('shipment_lines');
        Schema::dropIfExists('carriers');
    }
};
