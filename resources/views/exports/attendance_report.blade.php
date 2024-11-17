div<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daily Time Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 10px;
            /* Set font size to 10 points */
        }

        .pdf-wrapper {
            transform: scale(0.9);
            transform-origin: top left;
            width: 111.11%;
        }

        .header {
    display: flex;
    align-items: center; /* Vertically centers items */
    justify-content: center; /* Horizontally centers header text */
    position: relative;
    margin-bottom: 20px;
    height: 100px; /* Adjust as needed */
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
            /* Equal font size for all elements */
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
    display: flex;
    justify-content: space-between; /* Space between the two sections */
    align-items: center; /* Ensures vertical alignment */
    /* width: 100%; Makes the container span the full width */
}

.signature p,
.dean p {
    margin: 0; /* Removes any default margin that can cause misalignment */
    line-height: 1; /* Ensures consistent line height for alignment */
}

.signature {
    text-align: left; /* Align text to the left for signature */
    flex: 1; /* Allows it to grow evenly */
}

.dean {
    text-align: right; /* Align text to the right for dean */
    flex: 1; /* Allows it to grow evenly */
}

    </style>
</head>

<body>
    <div class="pdf-wrapper">
        <div class="header">
            <!-- Embedded Logo Using Base64 Encoding -->
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logo.png'))) }}" alt="BukSU Logo" class="logo" />
            <div class="header-text">
                <h1>Bukidnon State University</h1>
                <p>Malaybalay City, Bukidnon 6700</p>
                <p>Tel: (088) 813-5661 to 5663, Telefax: (088) 813-2717</p>
                <p>www.buksu.edu.ph</p>
            </div>
        </div>

        <!-- Conditional Display of Info Box -->
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
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('D - m/d/Y') }}</td>
                            
                            @if ($user->isAdmin())
                                <td>{{ $attendance->user->full_name }}</td>
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
                            <td colspan="{{ $user->isAdmin() ? 8 : 6 }}">No attendance records found for this schedule.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endforeach

    </div>
    {{-- <div class="signatures">
        <div class="signature">
            <p>Signature</p>
        </div>
        <div class="dean">
            <p>Dean/Director/Head of Office</p>
        </div>
    </div> --}}
    
</body>

</html>
