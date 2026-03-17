<?php

namespace App\Events;

use App\Models\Alert;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Alert $alert)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('alerts')];
    }

    public function broadcastAs(): string
    {
        return 'AlertCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'alert_id' => $this->alert->alert_id,
            'bin_id' => $this->alert->bin_id,
            'type' => $this->alert->type,
            'description' => $this->alert->description,
            'status' => $this->alert->status,
            'severity' => $this->alert->severity,
            'timestamp' => $this->alert->timestamp?->toISOString(),
            'is_resolved' => $this->alert->is_resolved,
        ];
    }
}
