<?php

namespace App\Models;

use App\Models\Traits\Lockable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, Lockable;

    protected $fillable = [
        'name',
        'code',
        'description',
        'college_id',
        'department_id',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('code', 'like', '%' . $value . '%')
            ->orWhere('description', 'like', '%' . $value . '%')
            ->orWhereHas('college', function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('department', function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            });
    }

    public function scopeSort($query, $sortBy, $sortDir)
    {
        if (in_array($sortBy, ['college.name', 'department.name'])) {
            $relation = explode('.', $sortBy)[0];
            $column = explode('.', $sortBy)[1];
            return $query->join($relation . 's', $relation . 's.id', '=', 'subjects.' . $relation . '_id')
                         ->orderBy($relation . 's.' . $column, $sortDir)
                         ->select('subjects.*');
        } else {
            return $query->orderBy($sortBy, $sortDir);
        }
    }
}
