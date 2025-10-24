<?php

namespace App\Core\Bus\Listeners;

use App\Core\Bus\Events\OrderConfirmed;
use App\Modules\Production\Domain\Services\WorkOrderPlanner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProposeWorkOrderFromOrder implements ShouldQueue
{
    use InteractsWithQueue;

    public $afterCommit = true;

    public function handle(OrderConfirmed $event): void
    {
        $order = $event->order->loadMissing('lines');

        /** @var WorkOrderPlanner $planner */
        $planner = app(WorkOrderPlanner::class);

        foreach ($order->lines as $line) {
            $planner->createFromOrderLine($line);
        }
    }
}
