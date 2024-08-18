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
        'percentage',
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
    $totalScheduledMinutes = $scheduleStartTime->diffInMinutes($scheduleEndTime);

    if ($this->time_in) {
        $timeIn = Carbon::parse($this->time_in);
        $lateMinutes = max(0, round($scheduleStartTime->floatDiffInMinutes($timeIn, false)));

        if ($lateMinutes <= 15) {
            $this->status = 'present';
        } elseif ($lateMinutes > 15 && $lateMinutes <= 30) {
            $this->status = 'late';
        } else {
            $this->status = 'absent';
        }
    }

    if ($this->time_out) {
        $timeOut = Carbon::parse($this->time_out);
        $attendedDurationMinutes = round($timeIn->floatDiffInMinutes($timeOut));
        $this->percentage = ($attendedDurationMinutes / $totalScheduledMinutes) * 100;

        if ($timeOut->lt($scheduleEndTime)) {
            $this->status = 'absent';
            $this->remarks = "Absent: Checked out before the scheduled end time.";
        } else {
            $this->remarks = "Completed {$this->percentage}% of the session.";  // Now accesses the accessor
        }
    } else {
        $this->status = 'incomplete';
        $this->remarks = 'Incomplete: No time out recorded.';
    }

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

    // Accessor for formatted percentage
    public function getPercentageAttribute($value)
{
    // Check if the percentage is an integer
    if (floor($value) == $value) {
        return (int) $value;  // Return as integer if no decimals
    }
    return number_format($value, 2, '.', '');  // Format to two decimals if needed
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
