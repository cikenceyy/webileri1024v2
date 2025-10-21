<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->bigIncrements('id'); 
            $table->unsignedBigInteger('company_id')->constrained()->cascadeOnDelete();
            $table->string('shipment_no', 32);
            $table->date('ship_date');
            $table->string('status', 16)->default('draft');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('carrier', 64)->nullable();
            $table->string('tracking_no', 64)->nullable();
            $table->unsignedInteger('package_count')->nullable();
            $table->decimal('weight_kg', 10, 3)->nullable();
            $table->decimal('volume_dm3', 10, 3)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'shipment_no']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'ship_date']);
            $table->index(['company_id', 'tracking_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
