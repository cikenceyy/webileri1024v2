<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\ShipmentDelivered;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Finance\Domain\Models\Invoice;
use App\Modules\Finance\Domain\Services\InvoiceCalculator;
use App\Modules\Finance\Domain\Services\NumberSequencer;
use App\Core\Contracts\SettingsReader;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateInvoiceFromShipment implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function __construct(
        private InvoiceCalculator $calculator,
        private NumberSequencer $sequencer,
        private SettingsReader $settingsReader,
    )
    {
    }

    public function handle(ShipmentDelivered $event): void
    {
        $shipment = $event->shipment->fresh(['order']);

        if (! $shipment || ! $shipment->order) {
            return;
        }

        $order = $shipment->order;

        if (! $order instanceof Order) {
            return;
        }

        $alreadyInvoiced = Invoice::query()
            ->where('company_id', $order->company_id)
            ->where('order_id', $order->getKey())
            ->exists();

        if ($alreadyInvoiced) {
            return;
        }

        $settings = $this->settingsReader->get($order->company_id);
        $order->load('lines');

        $lines = $order->lines->map(function ($line): array {
            return [
                'product_id' => $line->product_id,
                'variant_id' => $line->variant_id,
                'description' => $line->product?->name ?? __('Order Line'),
                'qty' => $line->qty,
                'uom' => $line->uom,
                'unit_price' => $line->unit_price,
                'discount_pct' => $line->discount_pct,
                'tax_rate' => $line->tax_rate,
            ];
        })->toArray();

        $calculation = $this->calculator->calculate($lines, (bool) $order->tax_inclusive);
        $terms = $order->payment_terms_days ?? $settings->defaults['payment_terms_days'];

        $invoice = Invoice::create([
            'company_id' => $order->company_id,
            'customer_id' => $order->customer_id,
            'order_id' => $order->getKey(),
            'currency' => $order->currency ?? $settings->money['base_currency'],
            'tax_inclusive' => (bool) $order->tax_inclusive,
            'payment_terms_days' => $terms,
            'notes' => $order->notes,
            'subtotal' => $calculation['totals']['subtotal'],
            'tax_total' => $calculation['totals']['tax'],
            'grand_total' => $calculation['totals']['grand'],
            'status' => Invoice::STATUS_DRAFT,
        ]);

        foreach ($calculation['lines'] as $line) {
            $invoice->lines()->create(array_merge($line, ['company_id' => $invoice->company_id]));
        }

        $invoice->markIssued($this->sequencer->nextInvoiceNumber($order->company_id), now(), $terms);
    }
}
