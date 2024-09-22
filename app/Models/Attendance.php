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
        'percentage',
        'status',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function sessions()
    {
        return $this->hasMany(AttendanceSession::class);
    }

    // Method to calculate and save the status and remarks
    public function calculateAndSaveStatusAndRemarks()
    {
        $scheduleStartTime = Carbon::parse($this->schedule->start_time);
        $scheduleEndTime = Carbon::parse($this->schedule->end_time);
        $totalScheduledMinutes = $scheduleStartTime->diffInMinutes($scheduleEndTime);

        // Get the first session's time_in
        $firstSession = $this->sessions->first();
        if ($firstSession && $firstSession->time_in) {
            $timeIn = Carbon::parse($firstSession->time_in);
            $lateMinutes = max(0, $scheduleStartTime->diffInMinutes($timeIn, false));

            // Determine the status based on the late minutes
            if ($lateMinutes <= 15 && $lateMinutes >= 0) {
                $this->status = 'present';  // On time or up to 15 minutes late
            } elseif ($lateMinutes > 15 && $lateMinutes <= 30) {
                $this->status = 'late';  // Late between 16 and 30 minutes
            } else {
                $this->status = 'absent';  // More than 30 minutes late
            }
        }

        // Get the last session's time_out
        $lastSession = $this->sessions->last();
        if ($lastSession && $lastSession->time_out) {
            $timeOut = Carbon::parse($lastSession->time_out);
            $attendedDurationMinutes = max(0, $timeIn->diffInMinutes($timeOut));

            // Calculate and round the percentage to two decimal places, capped at 100%
            $this->percentage = round(min(($attendedDurationMinutes / $totalScheduledMinutes) * 100, 100), 2);

            // Set remarks based on the final status
            if ($this->status === 'present') {
                $this->remarks = "Present: Attended {$this->percentage}% of the session.";
            } elseif ($this->status === 'late') {
                $this->remarks = "Late: Arrived late for {$lateMinutes} minutes. Attended {$this->percentage}% of the session.";
            } elseif ($this->status === 'absent') {
                // Check if the user checked out before the scheduled end time
                if ($timeOut->lt($scheduleEndTime)) {
                    $this->remarks = "Absent: Checked out early, attended only {$this->percentage}% of the session.";
                } else {
                    // Updated remark for being more than 30 minutes late
                    $this->remarks = "Absent due to Late for {$lateMinutes} minutes. Attended {$this->percentage}% of the session.";
                }
            }
        } else {
            // No time_out recorded, mark as incomplete
            $this->status = 'incomplete';
            $this->remarks = 'Incomplete: No time out recorded.';
        }

        $this->save();
    }

    // Accessor for formatted time_in
    public function getFormattedTimeInAttribute()
    {
        $firstSession = $this->sessions->first();
        return $firstSession && $firstSession->time_in
            ? Carbon::parse($firstSession->time_in)->format('h:i A')
            : '-';
    }

    // Accessor for formatted time_out
    public function getFormattedTimeOutAttribute()
    {
        $lastSession = $this->sessions->last();
        return $lastSession && $lastSession->time_out
            ? Carbon::parse($lastSession->time_out)->format('h:i A')
            : '-';
    }

    public function getPercentageAttribute($value)
    {
        // Cap the percentage at 100
        $value = min($value, 100);

        // Return integer if no decimal, otherwise format to two decimals
        if (floor($value) == $value) {
            return (int)$value;
        }
        return number_format($value, 2, '.', '');
    }

    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('m/d/Y') : null;
    }



    public function scopeSearch($query, $value)
    {
        return $query->whereHas('user', function ($q) use ($value) {
            // Search by user's personal information (name, username, email)
            $q->where('first_name', 'like', '%' . $value . '%')
                ->orWhere('middle_name', 'like', '%' . $value . '%')
                ->orWhere('last_name', 'like', '%' . $value . '%')
                ->orWhere('suffix', 'like', '%' . $value . '%')
                ->orWhere('username', 'like', '%' . $value . '%')
                ->orWhere('email', 'like', '%' . $value . '%');
        })
            ->orWhereHas('schedule', function ($q) use ($value) {
                // Search by schedule's subject name or schedule code
                $q->whereHas('subject', function ($subQuery) use ($value) {
                    $subQuery->where('name', 'like', '%' . $value . '%'); // Searching subject name
                })->orWhere('schedule_code', 'like', '%' . $value . '%'); // Searching schedule code
            })
            // Optionally, add other conditions like status or date here
            ->orWhere('date', 'like', '%' . $value . '%')
            ->orWhere('status', 'like', '%' . $value . '%');
    }
}
