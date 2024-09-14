<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // Import Auth
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceExport implements WithMultipleSheets
{
    protected $search;
    protected $status;
    protected $selectedMonth;
    protected $selectedSubject;
    protected $selectedSection;
    protected $perPage;

    public function __construct($search, $status, $selectedMonth, $selectedSubject, $selectedSection, $perPage = 1000)
    {
        $this->search = $search;
        $this->status = $status;
        $this->selectedMonth = $selectedMonth;
        $this->selectedSubject = $selectedSubject;
        $this->selectedSection = $selectedSection;
        $this->perPage = $perPage; // Handle pagination if no filter
    }

    public function sheets(): array
    {
        $sheets = [];

        // Get the authenticated user
        $authUser = Auth::user();

        // Build the base query
        $query = Attendance::with([
                'user.role', 
                'schedule.section', 
                'schedule.subject', 
                'schedule', 
                'sessions'
            ])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->selectedSubject, function ($query) {
                $query->whereHas('schedule.subject', function ($q) {
                    $q->where('id', $this->selectedSubject);
                });
            })
            ->when($this->selectedSection, function ($query) {
                $query->whereHas('schedule.section', function ($q) {
                    $q->where('id', $this->selectedSection);
                });
            });

        // If the user is not an admin, restrict the query to their own attendance data
        if (!$authUser->isAdmin()) {
            $query->where('user_id', $authUser->id);
        }

        // Now handle the filtered data for both admins and non-admins
        $attendances = $query->get()->groupBy('schedule.subject.name');

        foreach ($attendances as $subjectName => $subjectAttendances) {
            foreach ($subjectAttendances->groupBy('user.full_name') as $fullName => $userAttendances) {
                $sheets[] = new AttendancePerUserSheet(
                    $fullName,
                    $userAttendances->first()->user->role->name, // Role Name
                    $subjectName, 
                    $userAttendances->first()->schedule->section->name, // Section Name
                    $this->formatDaysOfWeek($userAttendances->first()->schedule->days_of_week), // Formatted Days of Week
                    Carbon::parse($userAttendances->first()->schedule->start_time)->format('h:i A') . ' - ' . Carbon::parse($userAttendances->first()->schedule->end_time)->format('h:i A'), // Schedule Time
                    $this->selectedMonth,
                    $userAttendances
                );
            }
        }

        return $sheets;
    }

    /**
     * Format the days of the week to short forms (Mon, Tue, etc.).
     *
     * @param array|string $daysOfWeek
     * @return string
     */
    protected function formatDaysOfWeek($daysOfWeek)
    {
        // Check if the days_of_week is a JSON string and decode it
        if (is_string($daysOfWeek)) {
            $daysOfWeek = json_decode($daysOfWeek, true); // Decode JSON string to array
        }

        // If decoding fails, return an empty string to avoid errors
        if (!is_array($daysOfWeek)) {
            return ''; 
        }

        // Map the full day names to their short forms
        $daysMap = [
            'Monday' => 'Mon',
            'Tuesday' => 'Tue',
            'Wednesday' => 'Wed',
            'Thursday' => 'Thu',
            'Friday' => 'Fri',
            'Saturday' => 'Sat',
            'Sunday' => 'Sun',
        ];

        // Convert the array of days into short forms and join them with commas
        return implode(', ', array_map(function($day) use ($daysMap) {
            return $daysMap[$day] ?? $day; // Use the short form if available
        }, $daysOfWeek));
    }
}