<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_code',
        'subject_id',
        'instructor_id',
        'laboratory_id',
        'college_id',
        'department_id',
        'section_id',
        'days_of_week',
        'start_time',
        'end_time',
        'section_code',
    ];

    public function getShortenedDaysOfWeek()
    {
        $daysOfWeek = json_decode($this->days_of_week, true) ?? [];
        $shortDays = [
            'Monday' => 'Mon',
            'Tuesday' => 'Tue',
            'Wednesday' => 'Wed',
            'Thursday' => 'Thu',
            'Friday' => 'Fri',
            'Saturday' => 'Sat',
            'Sunday' => 'Sun',
        ];

        return array_map(fn($day) => $shortDays[$day] ?? $day, $daysOfWeek);
    }

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

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getSectionName()
    {
        return $this->section ? $this->section->name : 'Unknown Section';
    }

    public function scopeSearch($query, $value)
    {
        return $query->whereHas('subject', function ($q) use ($value) {
            $q->where('name', 'like', '%' . $value . '%');
        })
            ->orWhereHas('instructor', function ($q) use ($value) {
                $q->where('first_name', 'like', '%' . $value . '%')
                    ->orWhere('middle_name', 'like', '%' . $value . '%')
                    ->orWhere('last_name', 'like', '%' . $value . '%')
                    ->orWhere('suffix', 'like', '%' . $value . '%');
            })
            ->orWhereHas('college', function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('department', function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('section', function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereHas('laboratory', function ($q) use ($value) {
                $q->where('name', 'like', '%' . $value . '%');
            })
            ->orWhereJsonContains('days_of_week', $value)
            ->orWhere('start_time', 'like', '%' . $value . '%')
            ->orWhere('end_time', 'like', '%' . $value . '%');
    }

    public function scopeSort($query, $sortBy, $sortDir)
    {
        if (in_array($sortBy, ['subject.name', 'college.name', 'department.name', 'section.name', 'laboratory.name'])) {
            $relation = explode('.', $sortBy)[0];
            $column = explode('.', $sortBy)[1];
            return $query->join($relation . 's', $relation . 's.id', '=', 'schedules.' . $relation . '_id')
                ->orderBy($relation . 's.' . $column, $sortDir)
                ->select('schedules.*');
        } elseif ($sortBy === 'instructor.full_name') {
            return $query->join('users as instructors', 'instructors.id', '=', 'schedules.instructor_id')
                ->orderBy('instructors.first_name', $sortDir)
                ->orderBy('instructors.middle_name', $sortDir)
                ->orderBy('instructors.last_name', $sortDir)
                ->select('schedules.*');
        } else {
            return $query->orderBy($sortBy, $sortDir);
        }
    }
}
