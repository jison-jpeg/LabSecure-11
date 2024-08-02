<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'college_id',
        'department_id',
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

    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
                     ->orWhere('year_level', 'like', '%' . $value . '%')
                     ->orWhereHas('department', function($q) use ($value) {
                         $q->where('name', 'like', '%' . $value . '%');
                     });
    }
    
}
