<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 8px;
            word-wrap: break-word;
        }
        th {
            background-color: #4F81BD;
            color: white;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Attendance Report</h2>
        <p>Month: {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</p>
        @if($selectedCollege)
            <p>College: {{ $collegeName }}</p>
        @endif
        @if($selectedDepartment)
            <p>Department: {{ $departmentName }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Name</th>
                <th>Role</th>
                <th>Section Code</th>
                <th>Section</th>
                <th>Subject</th>
                <th>Schedule</th>
                <th>Laboratory</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Status</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d/Y') }}</td>
                    <td>{{ $attendance->user->full_name }}</td>
                    <td>{{ $attendance->user->role->name }}</td>
                    <td>{{ $attendance->schedule->schedule_code }}</td>
                    <td>{{ optional($attendance->schedule->section)->name }}</td>
                    <td>{{ optional($attendance->schedule->subject)->name }}</td>
                    <td>
                        @php
                            $schedule = $attendance->schedule;
                            $scheduleTime = \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($schedule->end_time)->format('h:i A');
                            $shortDays = [];
                            foreach (json_decode($schedule->days_of_week) as $day) {
                                $shortDays[] = substr($day, 0, 3);
                            }
                            $formattedSchedule = "{$scheduleTime} (" . implode(', ', $shortDays) . ")";
                        @endphp
                        {{ $formattedSchedule }}
                    </td>
                    <td>{{ optional($attendance->schedule->laboratory)->name }}</td>
                    <td>{{ $attendance->formattedTimeIn }}</td>
                    <td>{{ $attendance->formattedTimeOut }}</td>
                    <td>{{ ucfirst($attendance->status) }}</td>
                    <td>{{ $attendance->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
