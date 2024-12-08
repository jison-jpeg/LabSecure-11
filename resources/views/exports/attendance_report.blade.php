<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daily Time Record</title>
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

        .sub-header {
            text-align: center;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .info-box {
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 5px;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box td {
            padding: 2px;
        }

        .schedule {
            margin-top: 10px;
            margin-bottom: 5px;
            text-align: left;
        }

        .schedule strong {
            display: block;
        }

        .dtr-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .dtr-table th,
        .dtr-table td {
            border: 1px solid #000;
            text-align: left;
            padding: 2px;
            font-size: 10px;
        }

        .dtr-table th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="pdf-wrapper">
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

        @unless ($user->isAdmin())
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
                        <td><strong>Month:</strong> {{ \Carbon\Carbon::parse($selectedMonth)->format('F Y') }}</td>
                    </tr>
                </table>
            </div>
        @endunless

        @foreach ($groupedAttendances as $scheduleName => $attendances)
            @php
                // Sort attendances alphabetically by user name (Last, First, Middle Initial Suffix)
                $sortedAttendances = $attendances->sortBy(function ($attendance) {
                    $lastName = $attendance->user->last_name ?? '';
                    $firstName = $attendance->user->first_name ?? '';
                    $middleInitial = $attendance->user->middle_name ? substr($attendance->user->middle_name, 0, 1) . '.' : '';
                    $suffix = $attendance->user->suffix ?? '';
                    return trim("$lastName, $firstName $middleInitial $suffix");
                });

                // Schedule Details
                $schedule = $attendances->first()->schedule;
                $scheduleStart = \Carbon\Carbon::parse($schedule->start_time)->format('h:i A');
                $scheduleEnd = \Carbon\Carbon::parse($schedule->end_time)->format('h:i A');
                $scheduleYear = \Carbon\Carbon::parse($schedule->start_time)->format('Y');
            @endphp

            <div class="schedule">
                <strong>SCHEDULE:</strong> {{ $scheduleName }} ({{ $schedule->schedule_code }}) {{ $scheduleYear }} M-F
                {{ $scheduleStart }} - {{ $scheduleEnd }}
            </div>

            <table class="dtr-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        @if ($user->isAdmin())
                            <th>Name</th>
                            <th>Role</th>
                        @endif
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Percentage</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sortedAttendances as $attendance)
                        @php
                            // Format user name
                            $lastName = $attendance->user->last_name ?? '';
                            $firstName = $attendance->user->first_name ?? '';
                            $middleInitial = $attendance->user->middle_name ? substr($attendance->user->middle_name, 0, 1) . '.' : '';
                            $suffix = $attendance->user->suffix ?? '';
                            $formattedName = trim("$lastName, $firstName $middleInitial $suffix");
                        @endphp

                        <tr>
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('D - m/d/Y') }}</td>

                            @if ($user->isAdmin())
                                <td>{{ $formattedName }}</td>
                                <td>{{ $attendance->user->role->name }}</td>
                            @endif

                            <td>{{ $attendance->formattedTimeIn }}</td>
                            <td>{{ $attendance->formattedTimeOut }}</td>
                            <td>{{ ucfirst($attendance->status) }}</td>
                            <td>{{ $attendance->percentage ?? 'N/A' }}</td>
                            <td>{{ $attendance->remarks ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $user->isAdmin() ? 8 : 6 }}">No attendance records found for this schedule.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach
    </div>
</body>

</html>
