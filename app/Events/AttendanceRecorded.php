<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceRecorded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function broadcastOn()
    {
        return new Channel('attendance-channel');
    }

    public function broadcastAs()
    {
        return 'attendance.recorded';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->attendance->id,
            'user_id' => $this->attendance->user_id,
            'schedule_id' => $this->attendance->schedule_id,
            'time_in' => $this->attendance->time_in,
            'time_out' => $this->attendance->time_out,
            'date' => $this->attendance->date,
            'status' => $this->attendance->status,
            'percentage' => $this->attendance->percentage,
            'remarks' => $this->attendance->remarks,
            'user' => $this->attendance->user,
            'schedule' => $this->attendance->schedule,
        ];
    }
}
