<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\OrderConfirmed;
use App\Modules\Marketing\Domain\Models\Customer;
use App\Modules\Marketing\Domain\Models\Order;
use App\Modules\Finance\Domain\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EnsureCustomerCreditLimit implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function handle(OrderConfirmed $event): void
    {
        try {
            self::assertWithinLimit($event->order);
        } catch (ValidationException $exception) {
            // Fail the job so operators notice, but avoid leaving it reserved forever.
            $this->fail($exception);
        }
    }

    /**
     * @throws ValidationException
     */
    public static function assertWithinLimit(Order $order): void
    {
        $order->loadMissing('customer');
        $customer = $order->customer;

        if (! $customer instanceof Customer) {
            return;
        }

        $limit = (float) ($customer->credit_limit ?? 0.0);

        if ($limit <= 0.0) {
            return;
        }

        $outstanding = (float) ($customer->balance ?? 0.0);

        if (class_exists(Invoice::class)) {
            $outstanding = (float) Invoice::query()
                ->where('company_id', $order->company_id)
                ->where('customer_id', $customer->getKey())
                ->sum('balance_due');
        }

        $projected = $outstanding + (float) $order->total_amount;

        if ($projected - $limit > 0.01) {
            throw ValidationException::withMessages([
                'credit_limit' => __(':name customer credit limit exceeded.', [
                    'name' => Str::of($customer->name ?? '')->trim()->value() ?: __('Customer'),
                ]),
            ]);
        }
    }
}
