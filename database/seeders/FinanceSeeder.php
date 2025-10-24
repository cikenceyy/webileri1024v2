<?php

namespace Database\Seeders;

use App\Modules\Finance\Domain\Models\CashbookEntry;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\InvoiceLine;
use App\Modules\Finance\Domain\Models\Receipt;
use App\Modules\Finance\Domain\Models\ReceiptApplication;
use App\Modules\Inventory\Domain\Models\Product;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\SalesOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $invoices = $this->seedInvoices();
        $this->seedReceipts($invoices);
        $this->seedCashbookEntries();
    }

    protected function seedInvoices(): array
    {
        $result = [];
        $now = Carbon::now();

        foreach ([1, 2] as $companyId) {
            $customer = Customer::query()->where('company_id', $companyId)->first();
            $order = SalesOrder::query()->where('company_id', $companyId)->with('lines')->first();
            $productFallback = Product::query()->where('company_id', $companyId)->first();

            if (! $customer || ! $productFallback) {
                continue;
            }

            $definitions = $companyId === 1
                ? [
                    ['code' => 'ACM-INV-001', 'status' => Invoice::STATUS_ISSUED, 'qty' => 5, 'unit_price' => 3200.0],
                    ['code' => 'ACM-INV-002', 'status' => Invoice::STATUS_DRAFT, 'qty' => 3, 'unit_price' => 950.0],
                ]
                : [
                    ['code' => 'BET-INV-001', 'status' => Invoice::STATUS_ISSUED, 'qty' => 10, 'unit_price' => 180.0],
                    ['code' => 'BET-INV-002', 'status' => Invoice::STATUS_DRAFT, 'qty' => 6, 'unit_price' => 220.0],
                ];

            foreach ($definitions as $definition) {
                $product = $order?->lines?->first()?->product ?? $productFallback;
                $qty = $definition['qty'];
                $unitPrice = $definition['unit_price'];
                $lineSubtotal = $qty * $unitPrice;
                $tax = round($lineSubtotal * 0.2, 2);
                $lineTotal = $lineSubtotal + $tax;

                $invoice = Invoice::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'doc_no' => $definition['code'],
                    ],
                    [
                        'customer_id' => $customer->id,
                        'order_id' => $order?->id,
                        'status' => $definition['status'],
                        'currency' => 'TRY',
                        'tax_inclusive' => false,
                        'subtotal' => $lineSubtotal,
                        'tax_total' => $tax,
                        'grand_total' => $lineTotal,
                        'paid_amount' => 0,
                        'payment_terms_days' => 30,
                        'due_date' => $now->copy()->addDays(30),
                        'issued_at' => $definition['status'] === Invoice::STATUS_ISSUED ? $now->copy()->subDay() : null,
                        'notes' => 'Seed invoice',
                    ]
                );

                $invoice->lines()->delete();

                InvoiceLine::query()->create([
                    'company_id' => $companyId,
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'description' => $product->name,
                    'qty' => $qty,
                    'uom' => 'adet',
                    'unit_price' => $unitPrice,
                    'discount_pct' => 0,
                    'tax_rate' => 20,
                    'line_subtotal' => $lineSubtotal,
                    'line_tax' => $tax,
                    'line_total' => $lineTotal,
                    'sort' => 1,
                ]);

                $invoice->refresh();
                $invoice->syncPaymentStatus();

                $result[$companyId][] = $invoice;
            }
        }

        return $result;
    }

    protected function seedReceipts(array $invoices): void
    {
        $now = Carbon::now();

        $definitions = [
            1 => [
                ['code' => 'ACM-RC-001', 'amount' => 5000.00, 'applies' => [['ref' => 'ACM-INV-001', 'amount' => 3200.00]]],
                ['code' => 'ACM-RC-002', 'amount' => 2500.00, 'applies' => [['ref' => 'ACM-INV-001', 'amount' => 1500.00]]],
            ],
            2 => [
                ['code' => 'BET-RC-001', 'amount' => 1800.00, 'applies' => [['ref' => 'BET-INV-001', 'amount' => 1800.00]]],
            ],
        ];

        foreach ($definitions as $companyId => $receipts) {
            $customer = Customer::query()->where('company_id', $companyId)->first();
            if (! $customer) {
                continue;
            }

            foreach ($receipts as $definition) {
                $receipt = Receipt::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'doc_no' => $definition['code'],
                    ],
                    [
                        'customer_id' => $customer->id,
                        'received_at' => $now->copy()->subDays(1),
                        'amount' => $definition['amount'],
                        'method' => 'Havale',
                        'reference' => 'BANK-' . substr($definition['code'], -3),
                        'notes' => 'Seed receipt',
                    ]
                );

                $receipt->applications()->delete();

                foreach ($definition['applies'] as $apply) {
                    $invoice = collect($invoices[$companyId] ?? [])->firstWhere('doc_no', $apply['ref']);
                    if (! $invoice) {
                        continue;
                    }

                    ReceiptApplication::query()->create([
                        'company_id' => $companyId,
                        'receipt_id' => $receipt->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $apply['amount'],
                    ]);

                    $invoice->refresh();
                    $invoice->applyPayment($apply['amount']);
                }
            }
        }
    }

    protected function seedCashbookEntries(): void
    {
        $now = Carbon::today();

        $entries = [
            ['company_id' => 1, 'direction' => CashbookEntry::DIRECTION_IN, 'amount' => 7500, 'account' => 'Banka - İş', 'notes' => 'Tahsilat', 'offset' => 0],
            ['company_id' => 1, 'direction' => CashbookEntry::DIRECTION_OUT, 'amount' => 2100, 'account' => 'Banka - İş', 'notes' => 'Hammadde Ödemesi', 'offset' => 1],
            ['company_id' => 1, 'direction' => CashbookEntry::DIRECTION_IN, 'amount' => 1250, 'account' => 'Kasa', 'notes' => 'Nakit Satış', 'offset' => 2],
            ['company_id' => 2, 'direction' => CashbookEntry::DIRECTION_IN, 'amount' => 3200, 'account' => 'Banka - Perakende', 'notes' => 'Online Tahsilat', 'offset' => 0],
            ['company_id' => 2, 'direction' => CashbookEntry::DIRECTION_OUT, 'amount' => 950, 'account' => 'Banka - Perakende', 'notes' => 'Mağaza Kirası', 'offset' => 1],
            ['company_id' => 2, 'direction' => CashbookEntry::DIRECTION_IN, 'amount' => 680, 'account' => 'Kasa', 'notes' => 'Günlük Hasılat', 'offset' => 2],
        ];

        foreach ($entries as $entry) {
            CashbookEntry::query()->updateOrCreate(
                [
                    'company_id' => $entry['company_id'],
                    'account' => $entry['account'],
                    'occurred_at' => $now->copy()->subDays($entry['offset']),
                    'direction' => $entry['direction'],
                    'amount' => $entry['amount'],
                ],
                [
                    'notes' => $entry['notes'],
                ]
            );
        }
    }
}
