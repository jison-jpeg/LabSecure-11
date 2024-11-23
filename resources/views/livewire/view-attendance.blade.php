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
                                <p><strong>Department:</strong>
                                    {{ $user->department ? $user->department->name : 'N/A' }}</p>
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
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
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
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
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
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
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
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
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
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">User Attendance</h5>
                            </div>
                            @livewire('attendance-table', ['userId' => $user->id])
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </section>
</div>
