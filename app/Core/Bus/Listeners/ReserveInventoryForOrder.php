<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\OrderConfirmed;
use App\Modules\Inventory\Domain\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ReserveInventoryForOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function handle(OrderConfirmed $event): void
    {
        if (! class_exists(StockService::class)) {
            return;
        }

        $service = app(StockService::class);

        if (! method_exists($service, 'reserveForOrder')) {
            return;
        }

        $order = $event->order->fresh(['lines']);

        if (! $order) {
            return;
        }

        $service->reserveForOrder($order);
    }
}
