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

    if ($this->time_in) {
        $timeIn = Carbon::parse($this->time_in);
        $lateMinutes = max(0, round($scheduleStartTime->floatDiffInMinutes($timeIn, false)));

        // Determine status based on late minutes
        if ($lateMinutes <= 15) {
            $this->status = 'present';
        } elseif ($lateMinutes > 15 && $lateMinutes <= 30) {
            $this->status = 'late';
        } else {
            $this->status = 'absent';
        }

        // Save the status early to ensure it's in the database before further logic
        $this->save();
    }

    // Check if time_out is recorded
    if (!$this->time_out) {
        // Mark as incomplete if time_in is set but time_out is not
        if ($this->time_in) {
            $this->status = 'incomplete';
            $this->remarks = 'Incomplete: No time out recorded.';
        }
    } else {
        $timeOut = Carbon::parse($this->time_out);
        $attendedDurationMinutes = round($timeIn->floatDiffInMinutes($timeOut));
        $hours = intdiv($attendedDurationMinutes, 60);
        $minutes = $attendedDurationMinutes % 60;
        $durationFormat = "{$hours}hr {$minutes}min";

        // Update remarks based on the previously set status
        switch ($this->status) {
            case 'present':
                $this->remarks = "Present: Attended full duration of {$durationFormat}.";
                break;
            case 'late':
                $this->remarks = "Late: Arrived more than 15 minutes late but within 30 minutes, attended {$durationFormat}.";
                break;
            case 'absent':
                $this->remarks = "Absent: Arrived more than 30 minutes late. Attended {$durationFormat} from the arrival.";
                break;
            case 'incomplete': // Should only reach here if time_out is somehow cleared after being set
                $this->remarks = "Incomplete: Attendance started but did not record full session.";
                break;
        }
    }

    // Save any changes made to status or remarks
    $this->save();
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
