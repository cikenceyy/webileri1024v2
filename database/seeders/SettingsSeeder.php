<?php

namespace Database\Seeders;

use App\Modules\Inventory\Domain\Models\PriceList;
use App\Modules\Inventory\Domain\Models\Warehouse;
use App\Modules\Settings\Domain\Models\Setting;
use App\Modules\Settings\Domain\SettingsDTO;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([1, 2] as $companyId) {
            $warehouse = Warehouse::query()
                ->where('company_id', $companyId)
                ->orderBy('id')
                ->first();

            $secondaryWarehouse = Warehouse::query()
                ->where('company_id', $companyId)
                ->orderBy('id', 'desc')
                ->first();

            $priceList = PriceList::query()
                ->where('company_id', $companyId)
                ->orderBy('id')
                ->first();

            $settings = SettingsDTO::fromArray([
                'money' => [
                    'base_currency' => 'TRY',
                    'allowed_currencies' => ['TRY', 'USD', 'EUR'],
                ],
                'tax' => [
                    'default_vat_rate' => 20,
                    'withholding_enabled' => false,
                ],
                'sequencing' => [
                    'invoice_prefix' => $companyId === 1 ? 'ACM-INV' : 'BET-INV',
                    'receipt_prefix' => $companyId === 1 ? 'ACM-RC' : 'BET-RC',
                    'order_prefix' => $companyId === 1 ? 'ACM-SO' : 'BET-SO',
                    'shipment_prefix' => $companyId === 1 ? 'ACM-SHP' : 'BET-SHP',
                    'grn_prefix' => $companyId === 1 ? 'ACM-GRN' : 'BET-GRN',
                    'work_order_prefix' => $companyId === 1 ? 'ACM-WO' : 'BET-WO',
                    'padding' => 6,
                    'reset_policy' => 'yearly',
                ],
                'defaults' => [
                    'payment_terms_days' => 30,
                    'warehouse_id' => $warehouse?->id,
                    'price_list_id' => $priceList?->id,
                    'tax_inclusive' => false,
                    'production_issue_warehouse_id' => $warehouse?->id,
                    'production_receipt_warehouse_id' => $secondaryWarehouse?->id ?? $warehouse?->id,
                    'shipment_warehouse_id' => $warehouse?->id,
                    'receipt_warehouse_id' => $secondaryWarehouse?->id ?? $warehouse?->id,
                ],
                'documents' => [
                    'invoice_print_template' => 'std-invoice',
                    'shipment_note_template' => 'std-shipment',
                    'grn_note_template' => 'std-grn',
                ],
                'general' => [
                    'company_locale' => 'tr_TR',
                    'timezone' => 'Europe/Istanbul',
                    'decimal_precision' => 2,
                ],
            ]);

            Setting::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                ],
                [
                    'data' => $settings->toArray(),
                    'version' => 1,
                ]
            );
        }
    }
}
