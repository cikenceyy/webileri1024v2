<?php

namespace App\Providers;

use App\Core\Bus\Events\GrnReceived;
use App\Core\Bus\Events\InvoicePaid;
use App\Core\Bus\Events\OrderCancelled;
use App\Core\Bus\Events\OrderConfirmed;
use App\Core\Bus\Events\ShipmentDelivered;
use App\Core\Bus\Events\ShipmentShipped;
use App\Core\Bus\Listeners\CreateInvoiceFromShipment;
use App\Core\Bus\Listeners\EnsureCustomerCreditLimit;
use App\Core\Bus\Listeners\CreateApInvoiceFromGrn;
use App\Core\Bus\Listeners\MarkOrderFulfilled;
use App\Core\Bus\Listeners\ReleaseInventoryForOrder;
use App\Core\Bus\Listeners\ReserveInventoryForOrder;
use App\Core\Bus\Listeners\ProposeWorkOrderFromOrder;
use App\Core\Bus\Listeners\SyncShippingCostToInvoice;
use App\Core\Bus\Listeners\UpdateCustomerBalanceOnInvoicePaid;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(OrderConfirmed::class, [EnsureCustomerCreditLimit::class, 'handle']);
        Event::listen(OrderConfirmed::class, [ReserveInventoryForOrder::class, 'handle']);
        Event::listen(OrderConfirmed::class, [ProposeWorkOrderFromOrder::class, 'handle']);

        Event::listen(OrderCancelled::class, [ReleaseInventoryForOrder::class, 'handle']);

        Event::listen(ShipmentShipped::class, [SyncShippingCostToInvoice::class, 'handle']);
        Event::listen(ShipmentDelivered::class, [SyncShippingCostToInvoice::class, 'handle']);
        Event::listen(ShipmentDelivered::class, [MarkOrderFulfilled::class, 'handle']);
        Event::listen(ShipmentDelivered::class, [CreateInvoiceFromShipment::class, 'handle']);

        Event::listen(InvoicePaid::class, [UpdateCustomerBalanceOnInvoicePaid::class, 'handle']);

        Event::listen(GrnReceived::class, [CreateApInvoiceFromGrn::class, 'handle']);
    }
}
