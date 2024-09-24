<div>
    <!-- Student Overview -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Student Overview</h5>
            <p><strong>Full Name:</strong> {{ $student->full_name }}</p>
            <p><strong>Username:</strong> {{ $student->username }}</p>
            <p><strong>Email:</strong> {{ $student->email }}</p>
            <p><strong>College:</strong> {{ $student->college->name ?? 'N/A' }}</p>
            <p><strong>Department:</strong> {{ $student->department->name ?? 'N/A' }}</p>
            <p><strong>Section:</strong> {{ $student->section->name ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ $student->status ?? 'Active' }}</p>
        </div>
    </div>

    <!-- Attendance History -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Attendance History</h5>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendanceRecords as $record)
                        <tr>
                            <td>{{ $record->date->format('m/d/Y') }}</td>
                            <td>{{ $record->schedule->subject->name ?? 'N/A' }}</td>
                            <td>{{ $record->formatted_time_in }}</td>
                            <td>{{ $record->formatted_time_out }}</td>
                            <td>{{ ucfirst($record->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Enrolled Schedules -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Enrolled Schedules</h5>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Schedule Code</th>
                        <th>Subject</th>
                        <th>Instructor</th>
                        <th>Days</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->schedule_code }}</td>
                            <td>{{ $schedule->subject->name ?? 'N/A' }}</td>
                            <td>{{ $schedule->instructor->full_name ?? 'N/A' }}</td>
                            <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</td>
                            <td>{{ $schedule->start_time }} - {{ $schedule->end_time }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
