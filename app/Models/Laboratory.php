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

    public function getCurrentOrRecentUserAttribute()
    {
        // Retrieve the most recent log related to the laboratory via the attendance model
        $log = TransactionLog::where('model', 'Attendance') // Log for Attendance
            ->whereJsonContains('details->laboratory_status', $this->status) // Filter based on laboratory status
            ->orderBy('created_at', 'desc')
            ->first();

        if ($log) {
            // Get user from the log
            $user = User::find($log->user_id);
            return [
                'name' => $user->full_name ?? 'Unknown User',
                'time' => Carbon::parse($log->created_at)->diffForHumans(),
                'status' => $log->action == 'check_in' ? 'Current User' : 'Recent User',
            ];
        }

        return null;
    }

    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('location', 'like', '%' . $value . '%')
            ->orWhere('type', 'like', '%' . $value . '%')
            ->orWhere('status', 'like', '%' . $value . '%');
    }
}
