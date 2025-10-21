<?php

namespace App\Core\Bus\Events;

use App\Modules\Procurement\Domain\Models\Grn;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GrnReceived
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Grn $grn)
    {
    }
}
