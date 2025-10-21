<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\OrderConfirmed;
use App\Modules\Production\Domain\Services\WoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProposeWorkOrderFromOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function handle(OrderConfirmed $event): void
    {
        if (! class_exists(WoService::class)) {
            return;
        }

        $service = app(WoService::class);

        if (! method_exists($service, 'proposeFromOrder')) {
            return;
        }

        $order = $event->order->fresh(['lines']);

        if (! $order || $order->lines->isEmpty()) {
            return;
        }

        $service->proposeFromOrder($order);
    }
}
