<?php

namespace App\Models;

use App\Models\Traits\Lockable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, Lockable;

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

    /**
     * Method to calculate and save the status and remarks.
     *
     * @return void
     */
    public function calculateAndSaveStatusAndRemarks()
    {
        if ($this->schedule) {
            $scheduleStartTime = Carbon::parse($this->schedule->start_time);
            $scheduleEndTime = Carbon::parse($this->schedule->end_time);
            $totalScheduledMinutes = $scheduleStartTime->diffInMinutes($scheduleEndTime);
        } else {
            // Define default or personnel-specific scheduled minutes
            $totalScheduledMinutes = 480; // Example: 8 hours
        }

        // Calculate total attended minutes based on all sessions
        $attendedDurationMinutes = 0;
        foreach ($this->sessions as $session) {
            if ($session->time_in && $session->time_out) {
                $timeIn = Carbon::parse($session->time_in);
                $timeOut = Carbon::parse($session->time_out);
                $attendedDurationMinutes += $timeIn->diffInMinutes($timeOut);
            }
        }

        // Cast to integer to avoid floating-point values
        $attendedDurationMinutes = (int)$attendedDurationMinutes;

        // Convert attended duration to hours and minutes
        $attendedDurationFormatted = $this->formatDuration($attendedDurationMinutes);

        // Check if there are any sessions without a time_out
        $incompleteSession = $this->sessions()->whereNull('time_out')->exists();

        if ($incompleteSession) {
            // Mark attendance as incomplete if any session is open
            $this->status = 'incomplete';
            $this->remarks = 'Incomplete attendance: Some sessions have no time out recorded.';
        } else {
            // Calculate the percentage of the session attended, capped at 100%
            $this->percentage = $totalScheduledMinutes > 0
                ? round(min(($attendedDurationMinutes / $totalScheduledMinutes) * 100, 100), 2)
                : 100;

            if ($this->schedule) {
                // For scheduled users
                $lateMinutes = 0;
                $firstSession = $this->sessions->first();
                if ($firstSession && $firstSession->time_in) {
                    $timeIn = Carbon::parse($firstSession->time_in);
                    $scheduleStartTime = Carbon::parse($this->schedule->start_time);
                    $lateMinutes = $scheduleStartTime->diffInMinutes($timeIn, false);
                }

                if ($attendedDurationMinutes < 10) {
                    // Absent if attended less than 10 minutes
                    $this->status = 'absent';
                    $this->remarks = "Marked as absent. Attended only {$attendedDurationFormatted}, which is insufficient.";
                } elseif ($attendedDurationMinutes >= 10 && $attendedDurationMinutes < ceil($totalScheduledMinutes * 0.75)) {
                    if ($lateMinutes > 15) {
                        $this->status = 'absent';
                        $this->remarks = "Marked as absent. Arrived late by {$lateMinutes} minutes and attended only {$attendedDurationFormatted}.";
                    } else {
                        $this->status = 'absent';
                        $this->remarks = "Marked as absent. Attended {$attendedDurationFormatted}, which is less than the required 75%.";
                    }
                } elseif ($attendedDurationMinutes >= ceil($totalScheduledMinutes * 0.75)) {
                    if ($lateMinutes > 15) {
                        $this->status = 'late';
                        $this->remarks = "Marked as late. Arrived {$lateMinutes} minutes late but attended {$attendedDurationFormatted} out of {$this->formatDuration($totalScheduledMinutes)}.";
                    } else {
                        $this->status = 'present';
                        $this->remarks = "Marked as present. Attended {$attendedDurationFormatted} out of {$this->formatDuration($totalScheduledMinutes)} ({$this->percentage}%).";
                    }
                }
            } else {
                // For personnel without schedules
                if ($attendedDurationMinutes < 10) {
                    $this->status = 'absent';
                    $this->remarks = "Marked as absent. Attended only {$attendedDurationFormatted}, which is insufficient.";
                } else {
                    $this->status = 'present'; // Or another logic as per requirements
                    $this->remarks = "Attendance recorded. Total attended time: {$attendedDurationFormatted}.";
                }
            }
        }

        $this->save();
    }

    /**
     * Converts a duration in minutes to a formatted string with hours and minutes.
     *
     * @param int $minutes The total duration in minutes.
     * @return string Formatted duration in 'Xh Ym' format.
     */
    private function formatDuration($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        $formatted = '';
        if ($hours > 0) {
            $formatted .= "{$hours}h ";
        }
        if ($remainingMinutes > 0 || $hours === 0) {
            $formatted .= "{$remainingMinutes}m";
        }

        return trim($formatted);
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
