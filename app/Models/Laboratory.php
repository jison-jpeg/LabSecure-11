<?php

namespace App\Models;

use App\Models\Traits\Lockable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    use HasFactory, Lockable;

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

        // Retrieve the most recent 'in' or 'out' log for this laboratory based on related attendances
        return TransactionLog::where('model', 'Attendance')  // Match to Attendance, not Laboratory
            ->whereIn('model_id', $attendanceIds)  // Fetch related attendance IDs
            ->whereIn('action', ['in', 'out'])
            ->latest()
            ->with('user') // Ensure TransactionLog is associated with User
            ->first();
    }

    public function getCurrentUser()
    {
        // Retrieve the most recent 'in' log for the laboratory based on related attendances
        $attendanceIds = Attendance::whereIn('schedule_id', $this->schedules->pluck('id'))
            ->pluck('id');

        return TransactionLog::where('model', 'Attendance')
            ->whereIn('model_id', $attendanceIds)
            ->where('action', 'in')
            ->latest()
            ->with('user') // Load the related User
            ->first();  // Return the latest 'in' log
    }

    public function getRecentUser()
    {
        // Retrieve the most recent 'out' log for the laboratory based on related attendances
        $attendanceIds = Attendance::whereIn('schedule_id', $this->schedules->pluck('id'))
            ->pluck('id');

        return TransactionLog::where('model', 'Attendance')
            ->whereIn('model_id', $attendanceIds)
            ->where('action', 'out')
            ->latest()
            ->with('user') // Load the related User
            ->first();  // Return the latest 'out' log
    }

    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('location', 'like', '%' . $value . '%')
            ->orWhere('type', 'like', '%' . $value . '%')
            ->orWhere('status', 'like', '%' . $value . '%');
    }
}
