<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Subject Report</title>
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
            margin-bottom: 5px;
        }

        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-box td {
            padding: 2px;
        }

        .subject-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .subject-table th,
        .subject-table td {
            border: 1px solid #000;
            text-align: left;
            padding: 2px;
            font-size: 10px;
        }

        .subject-table th {
            background-color: #f2f2f2;
        }

        .college-header {
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .department-header {
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
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
    </style>
</head>

<body>
    <div class="pdf-wrapper">
        <div class="header">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logo.png'))) }}"
                alt="Logo" class="logo" />
            <div class="header-text">
                <h1>Bukidnon State University</h1>
                <p>Malaybalay City, Bukidnon 6700</p>
                <p>Tel: (088) 813-5661 to 5663, Telefax: (088) 813-2717</p>
                <p>www.buksu.edu.ph</p>
            </div>
        </div>

        @if ($user->isStudent() || $user->isInstructor())
            <div class="info-box">
                <table>
                    <tr>
                        <td><strong>Name:</strong> {{ $user->full_name }}</td>
                        <td><strong>Role:</strong> {{ $user->role->name }}</td>
                    </tr>
                </table>
            </div>
            <table class="subject-table">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Section</th>
                        <th>Schedule</th>
                        @if ($user->isInstructor())
                            <th>Laboratory</th>
                        @endif
                        @if (!$user->isStudent())
                            <th>Total Students</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        @foreach ($subject->schedules as $schedule)
                            <tr>
                                <td>{{ $subject->code }}</td>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $schedule->section->name ?? 'N/A' }}</td>
                                <td>
                                    {{ implode(', ', json_decode($schedule->days_of_week)) }}
                                    ({{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }})
                                </td>
                                @if ($user->isInstructor())
                                    <td>{{ $schedule->laboratory->name ?? 'N/A' }}</td>
                                @endif
                                @if (!$user->isStudent())
                                    <td>{{ $schedule->section->users->count() ?? '0' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="{{ $user->isInstructor() ? '5' : '4' }}">
                                No subjects found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            @foreach ($colleges as $college)
                <div class="college-header">{{ $college->name }}</div>
                @foreach ($college->departments as $department)
                    <div class="department-header">Department: {{ $department->name }}</div>
                    <table class="subject-table">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($department->subjects as $subject)
                                <tr>
                                    <td>{{ $subject->code }}</td>
                                    <td>{{ $subject->name }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No subjects found for this department.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endforeach
            @endforeach
        @endif

        <div class="footer">
            Generated By: {{ $generatedBy }} | Date: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>
</body>

</html>
