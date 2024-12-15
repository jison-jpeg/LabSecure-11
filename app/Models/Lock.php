<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lock extends Model
{
    use HasFactory;

    protected $fillable = [
        'lockable_type', 'lockable_id', 'locked_by', 'lock_expires_at',
    ];

    protected $casts = [
        'lock_expires_at' => 'datetime',
    ];
}
