<div>
    <section class="section dashboard">

        @livewire('attendance-stats')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">My Attendance Records</h5>
                        <div class="row mb-4">
                            <div class="col-md-10">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <select wire:model="selectedSchedule" class="form-select">
                                            <option value="">Select Class</option>
                                            @foreach ($schedules as $schedule)
                                                <option value="{{ $schedule->id }}">{{ $schedule->schedule_code }} -
                                                    {{ $schedule->subject->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <input type="date" wire:model="selectedDate" class="form-control"
                                            placeholder="Select a date">
                                    </div>

                                    <div class="col-md-3">
                                        <select wire:model="status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="incomplete">Incomplete</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-auto">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Percentage</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendances as $attendance)
                                    <tr onclick="window.location='{{ route('attendance.subject.view', ['schedule' => $attendance->schedule->id]) }}';"
                                        style="cursor: pointer;">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $attendance->schedule->schedule_code }} -
                                            {{ $attendance->schedule->subject->name }}</td>
                                        <td>{{ $attendance->formatted_date }}</td>
                                        <td>{{ ucfirst($attendance->status) }}</td>
                                        <td>{{ $attendance->formatted_time_in }}</td>
                                        <td>{{ $attendance->formatted_time_out }}</td>
                                        <td>{{ $attendance->percentage }}%</td>
                                        <td>{{ $attendance->remarks }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>

                        <div class="mt-4">
                            {{ $attendances->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
