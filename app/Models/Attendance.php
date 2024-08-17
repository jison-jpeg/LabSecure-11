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
        $lateMinutes = max(0, round($scheduleStartTime->floatDiffInMinutes($timeIn, false)));

        // Set status based on the late minutes
        if ($lateMinutes <= 15) {
            $this->status = 'present'; // On time or slightly late (up to 15 minutes)
        } elseif ($lateMinutes > 15 && $lateMinutes <= 30) {
            $this->status = 'late'; // Late but not excessively
        } else {
            $this->status = 'absent'; // Excessively late (more than 30 minutes)
        }

        $this->save();
    }

    if ($this->time_out) {
        $timeOut = Carbon::parse($this->time_out);
        $attendedDurationMinutes = round($timeIn->floatDiffInMinutes($timeOut));
        $hours = intdiv($attendedDurationMinutes, 60);
        $minutes = $attendedDurationMinutes % 60;
        $durationFormat = "{$hours}hr {$minutes}min";

        // Check if the user checked out before the scheduled end time
        if ($timeOut->lt($scheduleEndTime)) {
            $this->status = 'absent';
            $this->remarks = "Absent: Checked out before the scheduled end time, attended {$durationFormat}.";
        } else {
            // Update remarks based on the status, considering checking out on time or later
            switch ($this->status) {
                case 'present':
                    $this->remarks = "Present: Attended full duration of {$durationFormat}.";
                    break;
                case 'late':
                    $this->remarks = "Late: Arrived more than 15 minutes late, attended {$durationFormat}.";
                    break;
                case 'absent':
                    // Already set above, potentially redundant but ensures clarity
                    $this->remarks = "Absent: Checked out before the scheduled end time at {$timeOut->format('g:i A')}, or was more than 15 minutes late. Total attended: {$attendedDurationMinutes} minutes.";
                    break;
            }
        }

        $this->save();
    } else {
        // Handle case where time_out is not recorded
        if ($this->time_in) {
            $this->status = 'incomplete';
            $this->remarks = 'Incomplete: No time out recorded.';
            $this->save();
        }
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
