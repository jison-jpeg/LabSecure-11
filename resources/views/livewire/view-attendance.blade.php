<div>
    <section class="section dashboard">
        <div class="row">
            <!-- User Details -->
            <div class="col-lg-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-body">
                        <h5 class="card-title">User Details</h5>
                        <div class="row">
                            <div class="col-12">
                                <p><strong>Username:</strong> {{ $user->username }}</p>
                                <p><strong>Email:</strong> {{ $user->email }}</p>
                                <p><strong>Full Name:</strong> {{ $user->full_name }}</p>
                                <p><strong>Role:</strong> {{ ucfirst($user->role->name) }}</p>
                                <p><strong>College:</strong> {{ $user->college ? $user->college->name : 'N/A' }}</p>
                                <p><strong>Department:</strong> {{ $user->department ? $user->department->name : 'N/A' }}</p>
                                @if ($user->isStudent())
                                    <p><strong>Section:</strong>
                                        {{ $user->section ? ($user->schedules->first() ? $user->schedules->first()->schedule_code . ' ' . $user->section->name : $user->section->name) : 'N/A' }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Summary -->
            <div class="col-lg-8 d-flex flex-column">
                <div class="row flex-fill">
                    <!-- Present Card -->
                    <div class="col-md-6 d-flex">
                        <div class="card info-card present-card flex-fill">
                            <div class="card-body">
                                <h5 class="card-title">Present</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $presentCount }}</h6>
                                        <span class="text-muted small pt-2 ps-1">total</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Absent Card -->
                    <div class="col-md-6 d-flex">
                        <div class="card info-card absent-card flex-fill">
                            <div class="card-body">
                                <h5 class="card-title">Absent</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-x-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $absentCount }}</h6>
                                        <span class="text-muted small pt-2 ps-1">total</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row flex-fill">
                    <!-- Late Card -->
                    <div class="col-md-6 d-flex">
                        <div class="card info-card late-card flex-fill">
                            <div class="card-body">
                                <h5 class="card-title">Late</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $lateCount }}</h6>
                                        <span class="text-muted small pt-2 ps-1">total</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Incomplete Card -->
                    <div class="col-md-6 d-flex">
                        <div class="card info-card incomplete-card flex-fill">
                            <div class="card-body">
                                <h5 class="card-title">Incomplete</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-exclamation-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6>{{ $incompleteCount }}</h6>
                                        <span class="text-muted small pt-2 ps-1">total</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Attendance Records</h5>
                    <div class="row mb-4">
                        <div class="col-md-10">
                            <div class="row g-1">
                                <div class="col-md-1">
                                    <select wire:model.live="perPage" name="perPage" class="form-select">
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        <option value="20">20</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <input wire:model.live.debounce.300ms="search" type="text" name="search"
                                           class="form-control" placeholder="Search attendance...">
                                </div>
                                <div class="col-12 col-md-2">
                                    <select wire:model.live="status" name="status" class="form-select">
                                        <option value="">Status</option>
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                        <option value="late">Late</option>
                                        <option value="incomplete">Incomplete</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <select wire:model.live="scheduleCode" name="scheduleCode" class="form-select">
                                        <option value="">Section Code</option>
                                        @foreach ($scheduleCodes as $id => $code)
                                            <option value="{{ $code }}">{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <select wire:model.live="subject" name="subject" class="form-select">
                                        <option value="">Select Subject</option>
                                        @foreach ($subjects as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-2">
                                    <button class="btn btn-secondary w-100 mb-1" type="reset"
                                            wire:click="clear">Clear Filters</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table with hoverable rows -->
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Status</th>
                                <th>Percentage</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendance as $att)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $att->date->format('m/d/Y') }}</td>
                                    <td>{{ $att->schedule->subject->name ?? 'N/A' }}</td>
                                    <td>{{ $att->formatted_time_in }}</td>
                                    <td>{{ $att->formatted_time_out }}</td>
                                    <td>{{ ucfirst($att->status) }}</td>
                                    <td class="text-center">
                                        <div class="progress mt-progress">
                                            <div class="progress-bar 
                                                {{ $att->percentage < 50 ? 'bg-danger' : ($att->percentage < 70 ? 'bg-warning' : 'bg-success') }}"
                                                role="progressbar" style="width: {{ $att->percentage }}%;"
                                                aria-valuenow="{{ $att->percentage }}" aria-valuemin="0"
                                                aria-valuemax="100">
                                                {{ $att->percentage }}%
                                            </div>
                                        </div>
                                    </td>                                    <td>{{ $att->remarks }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $attendance->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
