<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Schedule Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            font-size: 9px;
        }

        .pdf-wrapper {
            width: 100%;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 10px;
            height: 80px;
        }

        .header .logo {
            position: absolute;
            left: 0;
            width: 60px;
            height: auto;
        }

        .header-text {
            text-align: center;
        }

        .header-text h1,
        .header-text p {
            margin: 0;
            font-size: 15px;
        }

        .info-box {
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 10px;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box td {
            padding: 2px;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid #000;
            text-align: left;
            padding: 2px;
            font-size: 10px;
        }

        .schedule-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="pdf-wrapper">
        <!-- Header Section -->
        <div class="header">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logo.png'))) }}"
                alt="BukSU Logo" class="logo" />
            <div class="header-text">
                <h1>Bukidnon State University</h1>
                <p>Malaybalay City, Bukidnon 6700</p>
                <p>Tel: (088) 813-5661 to 5663, Telefax: (088) 813-2717</p>
                <p>www.buksu.edu.ph</p>
            </div>
        </div>

        <!-- Info Box -->
        @if ($user->isStudent() || $user->isInstructor())
            <div class="info-box">
                <table>
                    <tr>
                        <td><strong>Name:</strong> {{ $user->full_name }}</td>
                        <td><strong>Position:</strong> {{ $user->role->name }}</td>
                    </tr>
                    <tr>
                        <td>
                            <strong>College:</strong> {{ optional($user->college)->name }}
                        </td>
                        <td><strong>Year Level - Semester:</strong> {{ optional($user->section)->year_level }} -
                            {{ optional($user->section)->semester }}</td>
                    </tr>
                </table>
            </div>
        @endif

        <!-- Schedule Details -->
        <div class="schedule-table-wrapper">
            @if ($user->isStudent() || $user->isInstructor())
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th>Schedule Code</th>
                            <th>Section</th>
                            <th>Year Level</th>
                            <th>Subject</th>
                            <th>Instructor</th>
                            <th>Days</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($schedules as $schedule)
                            <tr>
                                <td>{{ $schedule->schedule_code }}</td>
                                <td>{{ $schedule->section->name ?? 'N/A' }}</td>
                                <td>{{ $schedule->section->year_level ?? 'N/A' }}</td>
                                <td>{{ $schedule->subject->name ?? 'N/A' }}</td>
                                <td>{{ $schedule->instructor->full_name ?? 'N/A' }}</td>
                                <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek() ?? ['N/A']) }}</td>
                                <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">No schedules found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                @forelse ($colleges as $college)
                    <h3>College: {{ $college->name }}</h3>
                    @forelse ($college->departments as $department)
                        <h4>Department: {{ $department->name }}</h4>
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th>Schedule Code</th>
                                    <th>Section</th>
                                    <th>Year Level</th>
                                    <th>Subject</th>
                                    <th>Instructor</th>
                                    <th>Days</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($department->schedules as $schedule)
                                    <tr>
                                        <td>{{ $schedule->schedule_code }}</td>
                                        <td>{{ $schedule->section->name ?? 'N/A' }}</td>
                                        <td>{{ $schedule->section->year_level ?? 'N/A' }}</td>
                                        <td>{{ $schedule->subject->name ?? 'N/A' }}</td>
                                        <td>{{ $schedule->instructor->full_name ?? 'N/A' }}</td>
                                        <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek() ?? ['N/A']) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">No schedules found for this department.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @empty
                        <p>No departments found for this college.</p>
                    @endforelse
                @empty
                    <p>No colleges found.</p>
                @endforelse
            @endif
        </div>
    </div>
</body>

</html>
