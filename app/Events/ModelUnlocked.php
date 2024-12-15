<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\Channel;

class ModelUnlocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $modelClass;
    public $modelId;

    public function __construct($modelClass, $modelId)
    {
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;
    }

    public function broadcastOn()
    {
        return [new Channel('model-locks.'.base64_encode($this->modelClass).'.'.$this->modelId)];
    }
}
