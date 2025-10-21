<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\OrderCancelled;
use App\Modules\Inventory\Domain\Services\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ReleaseInventoryForOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function handle(OrderCancelled $event): void
    {
        if (! class_exists(StockService::class)) {
            return;
        }

        $service = app(StockService::class);

        if (! method_exists($service, 'releaseReservedForOrder')) {
            return;
        }

        $order = $event->order->fresh(['lines']);

        if (! $order) {
            return;
        }

        $service->releaseReservedForOrder($order);
    }
}
