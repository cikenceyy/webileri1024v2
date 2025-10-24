<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('customers', 'payment_terms_days')) {
                $table->unsignedSmallInteger('payment_terms_days')->default(0)->after('status');
            }

            if (! Schema::hasColumn('customers', 'default_price_list_id')) {
                $table->foreignId('default_price_list_id')
                    ->nullable()
                    ->after('payment_terms_days')
                    ->constrained('price_lists')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('customers', 'credit_limit')) {
                $table->decimal('credit_limit', 14, 2)->nullable()->after('default_price_list_id');
            }

            if (! Schema::hasColumn('customers', 'billing_address')) {
                $table->json('billing_address')->nullable()->after('credit_limit');
            }

            if (! Schema::hasColumn('customers', 'shipping_address')) {
                $table->json('shipping_address')->nullable()->after('billing_address');
            }

            if (! Schema::hasColumn('customers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('shipping_address');
            }

            if (! Schema::hasColumn('customers', 'tax_no')) {
                $table->string('tax_no', 32)->nullable()->after('name');
            }

            $uniqueIndex = 'customers_company_name_unique';
            $tableName = $table->getTable();
            $connection = Schema::getConnection();
            $hasUnique = collect($connection->select("SHOW INDEX FROM `{$tableName}`"))
                ->contains(fn ($index) => ($index->Key_name ?? '') === $uniqueIndex);

            if (! $hasUnique) {
                $table->unique(['company_id', 'name'], $uniqueIndex);
            }
        });

        if (! Schema::hasTable('sales_orders')) {
            Schema::create('sales_orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers');
                $table->foreignId('price_list_id')->nullable()->constrained('price_lists')->nullOnDelete();
                $table->string('doc_no', 32);
                $table->enum('status', ['draft', 'confirmed', 'fulfilled', 'cancelled'])->default('draft');
                $table->char('currency', 3)->default('TRY');
                $table->boolean('tax_inclusive')->default(false);
                $table->unsignedSmallInteger('payment_terms_days')->default(0);
                $table->date('due_date')->nullable();
                $table->date('ordered_at')->useCurrent();
                $table->text('notes')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('fulfilled_at')->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'doc_no']);
                $table->index(['company_id', 'customer_id']);
                $table->index(['company_id', 'status']);
            });
        }

        if (! Schema::hasTable('sales_order_lines')) {
            Schema::create('sales_order_lines', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_id')->constrained('sales_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->decimal('qty', 14, 3);
                $table->string('uom', 16)->default('pcs');
                $table->decimal('unit_price', 14, 4);
                $table->decimal('discount_pct', 5, 2)->default(0);
                $table->decimal('tax_rate', 5, 2)->nullable();
                $table->decimal('line_total', 14, 2);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'order_id']);
            });
        }

        if (! Schema::hasTable('returns')) {
            Schema::create('returns', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers');
                $table->foreignId('related_order_id')->nullable()->constrained('sales_orders')->nullOnDelete();
                $table->enum('status', ['open', 'approved', 'closed'])->default('open');
                $table->string('reason', 120)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'customer_id']);
            });
        }

        if (! Schema::hasTable('return_lines')) {
            Schema::create('return_lines', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
                $table->decimal('qty', 14, 3);
                $table->string('reason_code', 64)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'return_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('return_lines');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('sales_order_lines');
        Schema::dropIfExists('sales_orders');

        Schema::table('customers', function (Blueprint $table): void {
            if (Schema::hasColumn('customers', 'default_price_list_id')) {
                $table->dropForeign('customers_default_price_list_id_foreign');
            }

            foreach (['payment_terms_days', 'default_price_list_id', 'credit_limit', 'billing_address', 'shipping_address', 'is_active'] as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }

            $uniqueIndex = 'customers_company_name_unique';
            $tableName = $table->getTable();
            $connection = Schema::getConnection();
            $hasUnique = collect($connection->select("SHOW INDEX FROM `{$tableName}`"))
                ->contains(fn ($index) => ($index->Key_name ?? '') === $uniqueIndex);

            if ($hasUnique) {
                $table->dropUnique($uniqueIndex);
            }
        });
    }
};
