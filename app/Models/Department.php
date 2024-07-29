<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'college_id',
    ];

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scope Search
    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
                     ->orWhereHas('college', function($q) use ($value) {
                         $q->where('name', 'like', '%' . $value . '%');
                     });
    }
}
