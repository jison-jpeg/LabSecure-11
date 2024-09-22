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
            return "{$userFullName} created a new {$this->model} with ID {$this->model_id}.";

        case 'update':
            if ($this->model === 'Section') {
                return "{$userFullName} updated the Section named {$details['section_name']}. Changes: {$this->details}";
            }
            if ($this->model === 'College') {
                return "{$userFullName} updated the College named {$details['college_name']}. Changes: {$this->details}";
            }
            return "{$userFullName} updated {$this->model} with ID {$this->model_id}. Changes: {$this->details}";

        case 'delete':
            if ($this->model === 'College') {
                return "{$userFullName} deleted the College named {$details['college_name']}.";
            }
            return "{$userFullName} deleted {$this->model} with ID {$this->model_id}.";

        // Handle attendance with subject details
        case 'check_in':
            if ($this->model === 'Attendance' && isset($details['subject_name'])) {
                return "{$userFullName} checked in for attendance in the subject {$details['subject_name']}.";
            }
            return "{$userFullName} checked in for attendance.";

        case 'check_out':
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
