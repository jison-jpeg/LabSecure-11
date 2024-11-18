<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NfcCardDetected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cardId;

    public function __construct($cardId)
    {
        $this->cardId = $cardId;
    }

    public function broadcastOn()
    {
        return new Channel('nfc-channel');
    }

    public function broadcastAs()
    {
        return 'nfc.card.detected';
    }

    public function broadcastWith()
    {
        return [
            'card_id' => $this->cardId,
        ];
    }
}
