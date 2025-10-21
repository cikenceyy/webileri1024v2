<?php

namespace App\Modules\Finance\Database\Seeders;

use App\Core\Support\Models\Company;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Finance\Domain\Models\Allocation;
use App\Modules\Finance\Domain\Models\BankAccount;
use App\Modules\Finance\Domain\Models\BankTransaction;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Models\InvoiceLine;
use App\Modules\Finance\Domain\Models\Receipt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FinanceV2DemoSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company): void {
            $customers = Customer::where('company_id', $company->id)->get();

            if ($customers->isEmpty()) {
                return;
            }

            $bankAccounts = collect([
                ['name' => 'Main Checking', 'currency' => 'TRY'],
                ['name' => 'USD Settlement', 'currency' => 'USD'],
            ])->map(function ($data) use ($company) {
                return BankAccount::updateOrCreate(
                    ['company_id' => $company->id, 'name' => $data['name']],
                    [
                        'account_no' => Str::upper(Str::random(10)),
                        'currency' => $data['currency'],
                        'is_default' => $data['name'] === 'Main Checking',
                        'status' => 'active',
                    ]
                );
            });

            $orders = Order::where('company_id', $company->id)->with('lines')->take(5)->get();

            foreach ($orders as $order) {
                $invoice = Invoice::firstOrCreate(
                    ['company_id' => $company->id, 'order_id' => $order->id],
                    [
                        'customer_id' => $order->customer_id,
                        'invoice_no' => Invoice::generateInvoiceNo($company->id),
                        'issue_date' => now()->subDays(rand(1, 20)),
                        'due_date' => now()->addDays(rand(5, 25)),
                        'currency' => $order->currency,
                        'status' => 'sent',
                        'notes' => $order->notes,
                    ]
                );

                if ($invoice->lines()->count() === 0) {
                    foreach ($order->lines as $index => $line) {
                        InvoiceLine::create([
                            'company_id' => $company->id,
                            'invoice_id' => $invoice->id,
                            'product_id' => $line->product_id,
                            'variant_id' => $line->variant_id,
                            'description' => $line->description,
                            'qty' => $line->qty,
                            'unit' => $line->unit,
                            'unit_price' => $line->unit_price,
                            'discount_rate' => $line->discount_rate,
                            'tax_rate' => $line->tax_rate,
                            'line_total' => $line->line_total,
                            'sort_order' => $index,
                        ]);
                    }
                }

                $invoice->load(['lines', 'allocations']);
                $invoice->refreshTotals();
                $invoice->save();
            }

            $invoices = Invoice::where('company_id', $company->id)->latest('issue_date')->take(12)->get();

            foreach ($invoices as $invoice) {
                $receipt = Receipt::updateOrCreate(
                    ['company_id' => $company->id, 'receipt_no' => 'RCPT-' . $invoice->invoice_no],
                    [
                        'customer_id' => $invoice->customer_id,
                        'receipt_date' => $invoice->issue_date,
                        'currency' => $invoice->currency,
                        'amount' => max($invoice->grand_total * 0.6, 100),
                        'bank_account_id' => $bankAccounts->first()->id,
                        'notes' => 'Demo receipt for ' . $invoice->invoice_no,
                    ]
                );

                $receipt->refreshAllocatedTotal();

                if ($invoice->balance_due > 0) {
                    $allocateAmount = min($invoice->balance_due, $receipt->amount);

                    Allocation::firstOrCreate(
                        [
                            'company_id' => $company->id,
                            'invoice_id' => $invoice->id,
                            'receipt_id' => $receipt->id,
                        ],
                        [
                            'amount' => round($allocateAmount, 2),
                            'allocated_at' => now()->subDays(rand(1, 10)),
                        ]
                    );

                    $invoice->load('lines', 'allocations');
                    $invoice->refreshTotals();
                    $invoice->save();
                    $receipt->refreshAllocatedTotal();
                }
            }

            foreach ($bankAccounts as $account) {
                foreach (range(1, 5) as $i) {
                    BankTransaction::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'bank_account_id' => $account->id,
                            'reference' => 'TX-' . $account->id . '-' . $i,
                        ],
                        [
                            'type' => $i % 2 === 0 ? 'withdrawal' : 'deposit',
                            'amount' => rand(500, 5000),
                            'currency' => $account->currency,
                            'transacted_at' => now()->subDays($i * 3),
                        ]
                    );
                }
            }
        });
    }
}
