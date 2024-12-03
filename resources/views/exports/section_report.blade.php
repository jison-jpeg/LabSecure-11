<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Report</title>
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

        .section-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .section-table th,
        .section-table td {
            border: 1px solid #000;
            text-align: left;
            padding: 2px;
            font-size: 10px;
        }

        .section-table th {
            background-color: #f2f2f2;
        }

        .department-header {
            font-size: 11px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .section-header {
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
        }

        .empty-message {
            margin-top: 5px;
            font-size: 10px;
            font-style: italic;
            color: #555;
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

                    @if ($department->sections->isEmpty())
                        <!-- Display message if no sections -->
                        <p class="empty-message">No sections available in this department.</p>
                    @else
                        @foreach ($department->sections as $section)
                            <div class="section-header">Section: {{ $section->name }}</div>
                            <table class="section-table">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($section->students as $student)
                                        <tr>
                                            <td>{{ $student->student_id }}</td>
                                            <td>{{ $student->full_name }}</td>
                                            <td>{{ $student->email }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3">No students enrolled in this section.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        @endforeach
                    @endif
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
