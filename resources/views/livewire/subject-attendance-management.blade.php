<div>

    <section class="section dashboard">
        <div class="row">

            <div class="col-12 d-flex flex-column">
                <div class="card h-100 card-info">
                    <div class="card-body text-white">
                        <h5 class="card-title fs-3">
                            {{ $schedule->subject->name }}
                        </h5>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <h6>Section</h6>
                                <p>{{ $schedule->section->name }}</p>
                            </div>

                            <div class="col-12 col-md-6">
                                <h6>Days</h6>
                                <p>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <h6>Section Code</h6>
                                <p>{{ $schedule->schedule_code }}</p>
                            </div>

                            <div class="col-12 col-md-6">
                                <h6>Time</h6>
                                <p>{{ Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                    {{ Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                @livewire('attendance-stats', ['scheduleId' => $schedule->id])

            </div>
        </div>
    </section>

    <!-- Attendance Management -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Student Attendance Management</h5>

            <div class="row mb-4">
                <div class="col-md-10">
                    <div class="row g-1">
                        <div class="col-md-3">
                            <input type="date" wire:model.live="selectedDate" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <select wire:model.live="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="incomplete">Incomplete</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <input wire:model.live="search" type="text" class="form-control"
                                placeholder="Search students...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance List -->
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $student->user->full_name }}</td>
                            <td>{{ $student->formatted_time_in }}</td>
                            <td>{{ $student->formatted_time_out }}</td>
                            <td>{{ ucfirst($student->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</div>
