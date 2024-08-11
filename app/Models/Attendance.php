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
        $timeIn = $this->time_in ? Carbon::parse($this->time_in) : null;
        $timeOut = $this->time_out ? Carbon::parse($this->time_out) : null;
        $scheduleStartTime = $this->schedule ? Carbon::parse($this->schedule->start_time) : null;
        $scheduleEndTime = $this->schedule ? Carbon::parse($this->schedule->end_time) : null;

        // Determine status
        if (!$timeIn || !$timeOut) {
            $this->status = 'absent';
        } elseif ($timeIn->lte($scheduleStartTime) && $timeOut->gte($scheduleEndTime)) {
            $this->status = 'present';
        } elseif ($timeIn->gt($scheduleStartTime)) {
            $this->status = 'late';
        } elseif ($timeIn->lte($scheduleStartTime) && $timeOut->lt($scheduleEndTime)) {
            $this->status = 'incomplete';
        } else {
            $this->status = 'unknown';
        }

        // Determine remarks based on status
        if ($this->status == 'present') {
            $duration = $timeIn->diff($timeOut);
            $this->remarks = "Attended " . $duration->format('%hhr %imin');
        } elseif ($this->status == 'late') {
            $lateDuration = $timeIn->diff($scheduleStartTime);
            $this->remarks = "Late by " . $lateDuration->format('%i minutes');
        } elseif ($this->status == 'incomplete') {
            $duration = $timeIn->diff($timeOut);
            $this->remarks = "Incomplete attendance: " . $duration->format('%hhr %imin');
        } else {
            $this->remarks = ucfirst($this->status);
        }

        // Save the status and remarks to the database
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
