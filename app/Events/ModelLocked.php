<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\Channel;

class ModelLocked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $modelClass;
    public $modelId;
    public $lockedBy;
    public $lockedByName;

    public function __construct($modelClass, $modelId, $lockedBy, $lockedByName)
    {
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;
        $this->lockedBy = $lockedBy;
        $this->lockedByName = $lockedByName;
    }

    public function broadcastOn()
    {
        return [new Channel('model-locks.'.base64_encode($this->modelClass).'.'.$this->modelId)];
    }
}
