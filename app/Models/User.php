<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'rfid_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'username',
        'email',
        'password',
        'role_id',
        'college_id',
        'department_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class);
    }

    public function getFullNameAttribute()
    {
        $fullName = $this->first_name;

        if ($this->middle_name) {
            $fullName .= ' ' . $this->middle_name;
        }

        $fullName .= ' ' . $this->last_name;

        if ($this->suffix) {
            $fullName .= ' ' . $this->suffix;
        }

        return $fullName;
    }

    public function isStudent()
    {
        return $this->role->name === 'student';
    }

    public function isInstructor()
    {
        return $this->role->name === 'instructor';
    }

    public function isAdmin()
    {
        return $this->role->name === 'admin';
    }

    public function scopeSearch($query, $value)
    {
        return $query->where('first_name', 'like', '%' . $value . '%')
            ->orWhere('middle_name', 'like', '%' . $value . '%')
            ->orWhere('last_name', 'like', '%' . $value . '%')
            ->orWhere('suffix', 'like', '%' . $value . '%')
            ->orWhere('username', 'like', '%' . $value . '%')
            ->orWhere('email', 'like', '%' . $value . '%');
    }
}
