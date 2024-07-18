<?php

namespace App\Models;

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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function determineStatus()
    {
        if ($this->time_in && $this->time_out) {
            $schedule = $this->schedule;
            if ($this->time_in <= $schedule->start_time && $this->time_out >= $schedule->end_time) {
                $this->status = 'present';
            } else {
                $this->status = 'incomplete';
            }
        } else {
            $this->status = 'absent';
        }
        $this->save();
    }
}
