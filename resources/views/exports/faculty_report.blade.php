<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1,
        .header p {
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Faculty Report</h1>
        <p>{{ now()->format('F d, Y h:i A') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>College</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($faculties as $faculty)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $faculty->username }}</td>
                    <td>{{ $faculty->first_name }}</td>
                    <td>{{ $faculty->last_name }}</td>
                    <td>{{ $faculty->email }}</td>
                    <td>{{ optional($faculty->college)->name }}</td>
                    <td>{{ optional($faculty->department)->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
