<?php

namespace App\Modules\Marketing\Domain\Observers;

use App\Core\Bus\Events\OrderCancelled;
use App\Core\Bus\Events\OrderConfirmed;
use App\Core\Bus\Listeners\EnsureCustomerCreditLimit;
use App\Modules\Marketing\Domain\Models\Order;
use Illuminate\Support\Facades\Event;

class OrderObserver
{
    public function creating(Order $order): void
    {
        $this->guardConfirmation($order, null);
    }

    public function updating(Order $order): void
    {
        $order->statusWas = $order->getOriginal('status');
        $this->guardConfirmation($order, $order->statusWas);
    }

    public function created(Order $order): void
    {
        $this->dispatchEvents($order, null);
    }

    public function updated(Order $order): void
    {
        $previous = $order->statusWas ?? null;
        $this->dispatchEvents($order, $previous);
        unset($order->statusWas);
    }

    protected function guardConfirmation(Order $order, ?string $previous): void
    {
        if ($order->status === 'confirmed' && $previous !== 'confirmed') {
            EnsureCustomerCreditLimit::assertWithinLimit($order);
        }
    }

    protected function dispatchEvents(Order $order, ?string $previous): void
    {
        if ($order->status === 'confirmed' && $previous !== 'confirmed') {
            Event::dispatch(new OrderConfirmed($order->fresh(['customer', 'lines'])));
        }

        if ($order->status === 'cancelled' && $previous !== 'cancelled') {
            Event::dispatch(new OrderCancelled($order->fresh(['customer', 'lines'])));
        }
    }
}
