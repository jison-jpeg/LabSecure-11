<div>
    <section class="section dashboard">

        @livewire('attendance-stats')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">My Attendance Records</h5>
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="row g-1 align-items-center">
                                    <div class="col-md-1">
                                        <select wire:model.live="perPage" class="form-select">
                                            <option value="10">10</option>
                                            <option value="15">15</option>
                                            <option value="20">20</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <select wire:model.live="selectedSection" class="form-select">
                                            <option value="">Section</option>
                                            @foreach ($sections as $section)
                                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select wire:model.live="selectedSchedule" class="form-select">
                                            <option value="">Select Schedule Code</option>
                                            @foreach ($schedules as $schedule)
                                                <option value="{{ $schedule->id }}">{{ $schedule->schedule_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select wire:model.live="selectedSubject" class="form-select">
                                            <option value="">Select Subject</option>
                                            @foreach ($subjects as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="date" wire:model.live="selectedDate" class="form-control"
                                            placeholder="Select a date">
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
                                    <div class="col-md-2 text-md-end mt-2 mt-md-0">
                                        <button class="btn btn-secondary w-100" type="reset" wire:click="clear">Clear
                                            Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="overflow-auto">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        @include('livewire.includes.table-sortable-th', [
                                            'name' => 'date',
                                            'displayName' => 'Date',
                                        ])
                                        @include('livewire.includes.table-sortable-th', [
                                            'name' => 'schedule.schedule_code',
                                            'displayName' => 'Class',
                                        ])
                                        @include('livewire.includes.table-sortable-th', [
                                            'name' => 'status',
                                            'displayName' => 'Status',
                                        ])
                                        @include('livewire.includes.table-sortable-th', [
                                            'name' => 'percentage',
                                            'displayName' => 'Percentage',
                                        ])
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                
                                <tbody>
                                    @foreach ($attendances as $attendance)
                                        <tr onclick="window.location='{{ route('attendance.subject.view', ['schedule' => $attendance->schedule->id]) }}';"
                                            style="cursor: pointer;">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $attendance->formatted_date }}</td>
                                            <td>{{ $attendance->schedule->schedule_code }} -
                                                {{ $attendance->schedule->subject->name }}</td>
                                            <td>{{ ucfirst($attendance->status) }}</td>
                                            <td>{{ $attendance->formatted_time_in }}</td>
                                            <td>{{ $attendance->formatted_time_out }}</td>
                                            <td class="text-center">
                                                <div class="progress mt-progress">
                                                    <div class="progress-bar 
                                                        {{ $attendance->percentage < 50 ? 'bg-danger' : ($attendance->percentage < 70 ? 'bg-warning' : 'bg-success') }}"
                                                        role="progressbar"
                                                        style="width: {{ $attendance->percentage }}%;"
                                                        aria-valuenow="{{ $attendance->percentage }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                        {{ $attendance->percentage }}%
                                                    </div>
                                                </div>
                                            </td>
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
