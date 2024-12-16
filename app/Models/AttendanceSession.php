<?php

namespace App\Models;

use App\Models\Traits\Lockable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory, Lockable;

    protected $fillable = [
        'attendance_id', 'time_in', 'time_out'
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
