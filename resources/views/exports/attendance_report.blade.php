<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daily Time Record</title>
    <style>
        @page {
            margin: 0.5in;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 10px;
            /* Set font size to 10 points */
        }

        .header {
            position: relative;
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            width: 60px;
            height: auto;
        }

        .header-text {
            display: inline-block;
            text-align: center;
        }

        .header-text h1,
        .header-text p {
            margin: 0;
            font-size: 15px;
            /* Equal font size for all elements */
        }

        .sub-header h1,
        .sub-header p,
        .sub-header strong,
        .sub-header span,
        .sub-header div,
        .sub-header td,
        .sub-header th {
            margin: 0;
            font-size: 15px;
        }

        .sub-header {
            text-align: center;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .sub-header {
            text-align: center;
            font-size: 15px;
            margin-bottom: 10px;
        }

        .info {
            margin-bottom: 20px;
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
            text-align: center;
            padding: 2px;
            font-size: 10px;
        }

        .dtr-table th {
            background-color: #f2f2f2;
        }

        .signatures {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .signatures div {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="https://buksu.edu.ph/wp-content/uploads/2020/05/buksu-logo-min-1024x1024.png" alt="BukSU Logo" />
        <div class="header-text">
            <h1>Bukidnon State University</h1>
            <p>Malaybalay City, Bukidnon 6700</p>
            <p>Tel: (088) 813-5661 to 5663, Telefax: (088) 813-2717</p>
            <p>www.buksu.edu.ph</p>
        </div>
    </div>
    <div class="sub-header">
        <h1>ATTENDANCE RECORD</h1>
    </div>
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

    @foreach ($groupedAttendances as $scheduleName => $attendances)
        @php
            // Retrieve the schedule from the first attendance record in the group
            $schedule = $attendances->first()->schedule;
            // Format the schedule times
            $scheduleStart = \Carbon\Carbon::parse($schedule->start_time)->format('h:i A');
            $scheduleEnd = \Carbon\Carbon::parse($schedule->end_time)->format('h:i A');
            // Extract the year from the schedule's start time
$scheduleYear = \Carbon\Carbon::parse($schedule->start_time)->format('Y');
        @endphp

        <!-- Schedule Information -->
        <div class="schedule">
            <strong>SCHEDULE:</strong> {{ $scheduleName }} ({{ $schedule->schedule_code }}) {{ $scheduleYear }} M-F
            {{ $scheduleStart }} - {{ $scheduleEnd }} : {{ $scheduleStart }} - {{ $scheduleEnd }}
        </div>

        <!-- Attendance Table -->
        <table class="dtr-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                    <th>Percentage</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('D - m/d/Y') }}</td>
                        <td>{{ $attendance->formattedTimeIn }}</td>
                        <td>{{ $attendance->formattedTimeOut }}</td>
                        <td>{{ ucfirst($attendance->status) }}</td>
                        <td>{{ $attendance->percentage ?? 'N/A' }}</td>
                        <td>{{ $attendance->remarks ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No attendance records found for this schedule.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    <div class="signatures">
        <div>
            <p>Signature</p>
            <div class="signature-line"></div>
        </div>
        <div>
            <p>Dean/Director/Head of Office</p>
            <div class="signature-line"></div>
        </div>
    </div>
</body>

</html>
