@php
    use Carbon\Carbon;
@endphp

<div>
    <livewire:edit-schedule :schedule="$schedule" />

    <section class="section dashboard">
        <div class="row">
            <!-- Schedule Overview -->
            <div class="col-12 d-flex flex-column">
                <div class="card h-100 card-info position-relative mb-4">
                    <div class="card-body text-white">
                        @if (Auth::user()->isAdmin())
                            <div class="action">
                                <a class="icon" href="#" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots text-white"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                        <h6>Action</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" data-bs-toggle="modal"
                                            data-bs-target="#editScheduleModal">Edit Schedule</a>

                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#">
                                            Delete Schedule
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        <h5 class="card-title fs-3">Class {{ ucfirst($schedule->schedule_code) }} -
                            {{ $schedule->section->year_level }} {{ $schedule->section->name }}</h5>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>Subject</h6>
                                <p>{{ $schedule->subject->name }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Instructor</h6>
                                <p>{{ $schedule->instructor->full_name }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Section</h6>
                                <p>{{ $schedule->section->year_level }} {{ $schedule->section->name }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>Days</h6>
                                <p>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Time</h6>
                                <p>{{ Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                    {{ Carbon::parse($schedule->end_time)->format('h:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Student Attendance</h5>
                        </div>
                        <!-- Filters and Students/Attendance livewire -->
                        @livewire('attendance-table', ['scheduleId' => $schedule->id, 'hideFilters' => ['subject', 'college', 'department', 'section', 'yearLevel']])
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
