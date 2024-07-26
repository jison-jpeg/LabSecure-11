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
