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

        switch ($this->action) {
            case 'check_in':
                return "{$userFullName} checked in for attendance in the laboratory  with schedule ID {$this->model_id}.";
            
            case 'check_out':
                return "{$userFullName} checked out from the laboratory for attendance schedule ID {$this->model_id}.";

            case 'create':
                return "{$userFullName} created a new {$this->model} with ID {$this->model_id}.";
            
            case 'update':
                return "{$userFullName} updated {$this->model} with ID {$this->model_id}. Changes: {$this->details}";

            case 'delete':
                return "{$userFullName} deleted {$this->model} with ID {$this->model_id}.";

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
