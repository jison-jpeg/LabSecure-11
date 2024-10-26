<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getReadableDetailsAttribute()
    {
        $userFullName = $this->user->full_name;
        $details = json_decode($this->details, true);

        switch ($this->action) {
            case 'create':
                if ($this->model === 'Section') {
                    return "{$userFullName} created a new Section named {$details['section_name']}.";
                }
                if ($this->model === 'College') {
                    return "{$userFullName} created a new College named {$details['college_name']}.";
                }
                if ($this->model === 'Laboratory') {
                    return "{$userFullName} created a new Laboratory named '{$details['laboratory_name']}' located at {$details['location']}.";
                }
                if ($this->model === 'User') {
                    return "{$userFullName} created a new User named '{$details['username']}' with username '{$details['username']}'.";
                }
                return "{$userFullName} created a new {$this->model} with ID {$this->model_id}.";

            case 'update':
                if ($this->model === 'Section') {
                    return "{$userFullName} updated the Section named {$details['section_name']}. Changes: {$this->details}";
                }
                if ($this->model === 'College') {
                    return "{$userFullName} updated the College named {$details['college_name']}. Changes: {$this->details}";
                }
                if ($this->model === 'Laboratory') {
                    $changes = collect($details['changes'] ?? [])->map(function ($value, $key) {
                        return ucfirst($key) . ': ' . (is_array($value) ? json_encode($value) : $value);
                    })->implode(', ');
                    return "{$userFullName} updated the Laboratory named '{$details['laboratory_name']}'. Changes: {$changes}";
                }
                if ($this->model === 'User') {
                    $changes = collect($details['changes'] ?? [])->map(function ($value, $key) {
                        return ucfirst($key) . ': ' . (is_array($value) ? json_encode($value) : $value);
                    })->implode(', ');
                    return "{$userFullName} updated the User named '{$details['username']}'. Changes: {$changes}";
                }
                return "{$userFullName} updated {$this->model} with ID {$this->model_id}. Changes: {$this->details}";

            case 'delete':
                if ($this->model === 'College') {
                    return "{$userFullName} deleted the College named {$details['college_name']}.";
                }
                if ($this->model === 'Laboratory') {
                    return "{$userFullName} deleted the Laboratory named '{$details['laboratory_name']}' located at {$details['location']}.";
                }
                if ($this->model === 'User') {
                    return "{$userFullName} deleted the User named '{$details['username']}'.";
                }
                return "{$userFullName} deleted {$this->model} with ID {$this->model_id}.";

            case 'lock':
                if ($this->model === 'Laboratory') {
                    return "{$userFullName} locked the Laboratory named '{$details['laboratory_name']}' located at {$details['location']}.";
                }
                return "{$userFullName} performed a lock action on {$this->model}.";

            case 'unlock':
                if ($this->model === 'Laboratory') {
                    return "{$userFullName} unlocked the Laboratory named '{$details['laboratory_name']}' located at {$details['location']}.";
                }
                return "{$userFullName} performed an unlock action on {$this->model}.";

                // Handle attendance with subject details
            case 'in':
                if ($this->model === 'Attendance' && isset($details['subject_name'])) {
                    return "{$userFullName} checked in for attendance in the subject {$details['subject_name']}.";
                }
                return "{$userFullName} checked in for attendance.";

            case 'out':
                if ($this->model === 'Attendance' && isset($details['subject_name'])) {
                    return "{$userFullName} checked out from attendance in the subject {$details['subject_name']}.";
                }
                return "{$userFullName} checked out from attendance.";

            default:
                return "{$userFullName} performed {$this->action} on {$this->model} with ID {$this->model_id}.";
        }
    }

    public function scopeSearch($query, $value)
    {
        return $query->where('action', 'like', '%' . $value . '%')
            ->orWhere('model', 'like', '%' . $value . '%')
            ->orWhere('details', 'like', '%' . $value . '%')
            ->orWhereHas('user', function ($q) use ($value) {
                $q->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%');
            });
    }
}
