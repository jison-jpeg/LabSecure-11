<div>
    <section class="section dashboard">
        <div class="row">
            <!-- Student Overview -->
            <div class="col-12 d-flex flex-column">
                <div class="card h-100 card-info position-relative">
                    <div class="card-body text-white">
                        <h5 class="card-title fs-3">{{ $student->full_name }}</h5>
                        <div
                        class="badge rounded-pill 
                        {{ $student->status === 'active' ? 'bg-success text-light' : 'bg-danger text-light' }} 
                        position-absolute top-0 end-0 m-3">
                        {{ ucfirst($student->status) }}
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>Username</h6>
                                <p>{{ $student->username }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Email</h6>
                                <p>{{ $student->email }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Role</h6>
                                <p>{{ $student->role->name }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>College</h6>
                                <p>{{ $student->college ? $student->college->name : 'N/A' }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Department</h6>
                                <p>{{ $student->department ? $student->department->name : 'N/A' }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Section</h6>
                                <p>{{ $student->section ? ($student->schedules->first() ? $student->schedules->first()->schedule_code . ' ' . $student->section->name : $student->section->name) : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance History -->
            <div class="col-12 d-flex flex-column">
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
                                    <th  class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendanceRecords as $record)
                                    <tr>
                                        <td>{{ $record->date->format('m/d/Y') }}</td>
                                        <td>{{ $record->schedule->subject->name ?? 'N/A' }}</td>
                                        <td>{{ $record->formatted_time_in }}</td>
                                        <td>{{ $record->formatted_time_out }}</td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill 
                                                {{ $record->status == 'present' ? 'bg-success' : 
                                                   ($record->status == 'absent' ? 'bg-danger' : 
                                                   ($record->status == 'late' ? 'bg-warning' : 'bg-secondary')) }}">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Enrolled Schedules -->
            <div class="col-12 d-flex flex-column">
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
        </div>
    </section>
</div>
