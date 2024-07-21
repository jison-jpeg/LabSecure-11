<?php

namespace App\Models;

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

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    // Scope Search
    public function scopeSearch($query, $value)
    {
        return $query->where('name', 'like', '%' . $value . '%')
            ->orWhere('location', 'like', '%' . $value . '%')
            ->orWhere('type', 'like', '%' . $value . '%')
            ->orWhere('status', 'like', '%' . $value . '%');
    }
}
