<?php

namespace App\Models;

use App\Models\Traits\Lockable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory, Lockable;

    protected $fillable = [
        'name',
        'college_id',
        'department_id',
        'school_year',
        'year_level',
        'semester',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function users()
    {
        return $this->hasMany(User::class, 'section_id');
    }
    public function students()
    {
        // Fetch users assigned to this section who have the student role
        return $this->hasMany(User::class, 'section_id')->whereHas('role', function ($query) {
            $query->where('name', 'student');
        });
    }

    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('year_level', 'like', '%' . $value . '%')
            ->orWhere('semester', 'like', '%' . $value . '%')
            ->orWhere('school_year', 'like', '%' . $value . '%')
            ->orWhereHas('college', function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('department', function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            });
    }
}
