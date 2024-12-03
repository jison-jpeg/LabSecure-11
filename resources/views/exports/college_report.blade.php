<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College and Departments Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 10px;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 20px;
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
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }

        th {
            background-color: #f2f2f2;
        }

        .totals-row {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/img/logo.png'))) }}" alt="Logo" class="logo">
        <div class="header-text">
            <h1>Bukidnon State University</h1>
            <p>Malaybalay City, Bukidnon 6700</p>
            <p>Tel: (088) 813-5661 to 5663, Telefax: (088) 813-2717</p>
            <p>www.buksu.edu.ph</p>
        </div>
    </div>

    <h2 style="text-align: center;">College and Departments Report</h2>

    @foreach ($colleges as $college)
        <h3>{{ $college->name }}</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Total Users</th>
                </tr>
            </thead>
            <tbody>
                @php $collegeTotal = 0; @endphp
                @foreach ($college->departments as $index => $department)
                    @php $userCount = $department->users->count(); @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $department->name }}</td>
                        <td>{{ $userCount }}</td>
                    </tr>
                    @php $collegeTotal += $userCount; @endphp
                @endforeach
                <tr class="totals-row">
                    <td colspan="2">Total Users in {{ $college->name }}</td>
                    <td>{{ $collegeTotal }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <h3>Total Users: 
        @php
            $grandTotal = $colleges->reduce(function ($carry, $college) {
                return $carry + $college->departments->sum(fn($department) => $department->users->count());
            }, 0);
        @endphp
        {{ $grandTotal }}
    </h3>
</body>

</html>
