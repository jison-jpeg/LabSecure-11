<?php

namespace App\Events;

use App\Models\Laboratory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LaboratoryStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $laboratory;

    public function __construct(Laboratory $laboratory)
    {
        $this->laboratory = $laboratory;
    }

    public function broadcastOn()
    {
        // Broadcast on a laboratory-specific channel
        return new Channel('laboratory-channel');
    }

    public function broadcastAs()
    {
        return 'laboratory.status.updated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->laboratory->id,
            'name' => $this->laboratory->name,
            'location' => $this->laboratory->location,
            'type' => $this->laboratory->type,
            'status' => $this->laboratory->status,  // Status to be updated in real time
        ];
    }
}
