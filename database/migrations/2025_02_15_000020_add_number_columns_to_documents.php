<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchase_orders', 'po_number')) {
                $table->string('po_number', 40)->nullable()->after('supplier_id');
            }
        });

        DB::table('purchase_orders')
            ->select(['id', 'company_id', 'created_at'])
            ->orderBy('company_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                static $counters = [];

                foreach ($rows as $row) {
                    $companyId = (int) $row->company_id;
                    $year = $row->created_at ? \Illuminate\Support\Carbon::parse($row->created_at)->format('Y') : now()->format('Y');
                    $key = $companyId.'-'.$year;

                    $counters[$key] = ($counters[$key] ?? 0) + 1;
                    $serial = str_pad((string) $counters[$key], 4, '0', STR_PAD_LEFT);
                    $value = sprintf('PO-%s-%s', $year, $serial);

                    DB::table('purchase_orders')->where('id', $row->id)->update(['po_number' => $value]);
                }
            });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->unique(['company_id', 'po_number']);
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            if (Schema::hasColumn('purchase_orders', 'po_number')) {
                $table->dropUnique('purchase_orders_company_id_po_number_unique');
                $table->dropColumn('po_number');
            }
        });
    }
};
