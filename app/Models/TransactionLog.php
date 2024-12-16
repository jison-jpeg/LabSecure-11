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
            return "{$userFullName} created a new {$this->model} with ID {$this->model_id}.";

        case 'update':
            return "{$userFullName} updated {$this->model} with ID {$this->model_id}.";

        case 'delete':
            return "{$userFullName} deleted {$this->model} with ID {$this->model_id}.";

        case 'lock':
            return "{$userFullName} locked {$this->model} with ID {$this->model_id}.";

        case 'unlock':
            return "{$userFullName} unlocked {$this->model} with ID {$this->model_id}.";

        case 'in':
            // Check for subject details
            if (isset($details['subject_name'])) {
                return "{$userFullName} checked in for attendance in the subject {$details['subject_name']}.";
            }
            // Check for laboratory details if no subject is provided
            if (isset($details['laboratory_name'])) {
                return "{$userFullName} checked in from laboratory {$details['laboratory_name']}.";
            }
            return "{$userFullName} checked in for attendance.";

        case 'out':
            // Check for subject details
            if (isset($details['subject_name'])) {
                return "{$userFullName} checked out from attendance in the subject {$details['subject_name']}.";
            }
            // Check for laboratory details if no subject is provided
            if (isset($details['laboratory_name'])) {
                return "{$userFullName} checked out from laboratory {$details['laboratory_name']}.";
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
