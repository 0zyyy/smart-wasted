<?php

namespace App\Events;

use App\Models\Measurement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeasurementCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Measurement $measurement)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel('measurements')];
    }

    public function broadcastAs(): string
    {
        return 'MeasurementCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'measurement_id' => $this->measurement->measurement_id,
            'sensor_id' => $this->measurement->sensor_id,
            'value' => $this->measurement->value,
            'unit' => $this->measurement->unit,
            'timestamp' => $this->measurement->timestamp?->toISOString(),
        ];
    }
}
