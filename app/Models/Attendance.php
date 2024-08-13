<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schedule_id',
        'date',
        'time_in',
        'time_out',
        'status',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // Method to calculate and save the status and remarks
    public function calculateAndSaveStatusAndRemarks()
{
    $scheduleStartTime = Carbon::parse($this->schedule->start_time);
    $scheduleEndTime = Carbon::parse($this->schedule->end_time);

    if ($this->time_in) {
        $timeIn = Carbon::parse($this->time_in);
        $lateMinutes = max($scheduleStartTime->diffInMinutes($timeIn, false), 0);

        if ($lateMinutes <= 15) {  // Arrives within 15 minutes of the start time
            $this->status = 'present';
        } elseif ($lateMinutes > 15 && $lateMinutes <= 30) {  // Arrives late but within 30 minutes
            $this->status = 'late';
        } else {  // Arrives more than 30 minutes late
            $this->status = 'absent';
        }

        $this->save();
    }

    if ($this->time_out && $this->status) {
        $timeOut = Carbon::parse($this->time_out);
        $attendedDuration = $timeIn->diffInMinutes($timeOut);

        switch ($this->status) {
            case 'present':
                $this->remarks = "Present: Attended full duration of {$attendedDuration} minutes.";
                break;
            case 'late':
                $this->remarks = "Late: Arrived more than 15 minutes late but within 30 minutes, attended {$attendedDuration} minutes.";
                break;
            case 'absent':
                $this->remarks = "Absent: Arrived more than 30 minutes late. Attended {$attendedDuration} minutes from the arrival.";
                break;
        }

        $this->save();
    }
}

    // Accessor for formatted time_in
    public function getFormattedTimeInAttribute()
    {
        return $this->time_in ? Carbon::parse($this->time_in)->format('h:i A') : '-';
    }

    // Accessor for formatted time_out
    public function getFormattedTimeOutAttribute()
    {
        return $this->time_out ? Carbon::parse($this->time_out)->format('h:i A') : '-';
    }

    public function scopeSearch($query, $value)
    {
        return $query->whereHas('user', function ($query) use ($value) {
            $query->where('first_name', 'like', '%' . $value . '%')
                ->orWhere('middle_name', 'like', '%' . $value . '%')
                ->orWhere('last_name', 'like', '%' . $value . '%')
                ->orWhere('suffix', 'like', '%' . $value . '%')
                ->orWhere('username', 'like', '%' . $value . '%')
                ->orWhere('email', 'like', '%' . $value . '%');
        })->orWhereHas('schedule', function ($query) use ($value) {
            $query->where('name', 'like', '%' . $value . '%')
                ->orWhere('location', 'like', '%' . $value . '%')
                ->orWhere('type', 'like', '%' . $value . '%')
                ->orWhere('status', 'like', '%' . $value . '%');
        })->orWhere('date', 'like', '%' . $value . '%')
            ->orWhere('time_in', 'like', '%' . $value . '%')
            ->orWhere('time_out', 'like', '%' . $value . '%')
            ->orWhere('status', 'like', '%' . $value . '%');
    }
}
