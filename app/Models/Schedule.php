<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'instructor_id',
        'laboratory_id',
        'college_id',
        'department_id',
        'section_id',
        'days_of_week',
        'start_time',
        'end_time',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'schedule_student');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
