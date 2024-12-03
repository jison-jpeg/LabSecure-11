<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            font-size: 9px;
            page-break-inside: avoid;
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
            height: 100px;
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

        .header-text .college-name {
            margin-top: 5px;
            font-size: 13px;
            font-weight: bold;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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

        .department-header {
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .footer {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            font-size: 9px;
            text-align: right;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .page-break {
            page-break-after: always;
        }

        .last-page-break {
            page-break-after: auto;
        }
    </style>
</head>

<body>
    <div class="pdf-wrapper">
        @foreach ($colleges as $index => $college)
            <!-- Header Section -->
            <div class="header">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logo.png'))) }}"
                    alt="Logo" class="logo">
                <div class="header-text">
                    <h1>Bukidnon State University</h1>
                    <h1>{{ $college->name }}</h1>
                    <p>Malaybalay City, Bukidnon 6700</p>
                    <p>Tel: (088) 813-5661 to 5663, Telefax: (088) 813-2717</p>
                    <p>www.buksu.edu.ph</p>
                </div>
            </div>

            <!-- Content Section -->
            <div class="content">
                @foreach ($college->departments as $department)
                    <div class="department-header">Department: {{ $department->name }}</div>
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
                                    <td>{{ $schedule->days_of_week }}</td>
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
                @endforeach
            </div>

            <!-- Page Break -->
            @if ($loop->remaining > 0)
                <div class="page-break"></div>
            @else
                <div class="last-page-break"></div>
            @endif
        @endforeach

        <!-- Footer Section -->
        <div class="footer">
            Generated By: {{ $generatedBy }} | Date: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>
</body>

</html>
