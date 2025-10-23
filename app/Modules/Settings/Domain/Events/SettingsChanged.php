<?php

namespace App\Modules\Settings\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingsChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $companyId,
        public readonly string $area,
        public readonly int $version
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('company.' . $this->companyId . '.settings');
    }

    public function broadcastAs(): string
    {
        return 'settings.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'company_id' => $this->companyId,
            'area' => $this->area,
            'version' => $this->version,
        ];
    }
}
