<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'type',
        'status',
    ];

    protected $attributes = [
        'status' => 'Available',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function recentUserLog()
{
    // Retrieve all attendance IDs related to this laboratory's schedules
    $attendanceIds = Attendance::whereIn('schedule_id', $this->schedules->pluck('id'))
        ->pluck('id');

    // Retrieve the most recent 'check_in' or 'check_out' log for this laboratory based on related attendances
    return TransactionLog::where('model', 'Attendance')  // Match to Attendance, not Laboratory
        ->whereIn('model_id', $attendanceIds)  // Fetch related attendance IDs
        ->whereIn('action', ['check_in', 'check_out'])
        ->latest()
        ->with('user') // Ensure TransactionLog is associated with User
        ->first();
}



    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('location', 'like', '%' . $value . '%')
            ->orWhere('type', 'like', '%' . $value . '%')
            ->orWhere('status', 'like', '%' . $value . '%');
    }
}
