<?php

namespace App\Models;

use App\Models\Traits\Lockable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    use HasFactory, Lockable;

    protected $fillable = [
        'name',
        'description',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class)->with('schedules');
    }

    public function sections()
    {
        return $this->hasManyThrough(Section::class, Department::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function dean()
{
    return $this->hasOne(User::class)->whereHas('role', function ($query) {
        $query->where('name', 'dean');
    });
}


    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%');
    }
}
