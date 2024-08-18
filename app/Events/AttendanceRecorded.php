<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
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
        // Retrieve the latest session (time_in and time_out details) from attendance sessions
        $latestSession = $this->attendance->sessions()->latest()->first();

        return [
            'id' => $this->attendance->id,
            'user_id' => $this->attendance->user_id,
            'schedule_id' => $this->attendance->schedule_id,
            'time_in' => $latestSession->time_in ?? null, // Use the latest session's time_in
            'time_out' => $latestSession->time_out ?? null, // Use the latest session's time_out
            'date' => $this->attendance->date,
            'status' => $this->attendance->status,
            'percentage' => $this->attendance->percentage, // Include percentage calculation if available
            'remarks' => $this->attendance->remarks,
            'user' => $this->attendance->user,
            'schedule' => $this->attendance->schedule,
        ];
    }
}
