@php
    use Carbon\Carbon;
@endphp

<div>
    <section class="section dashboard">
        <div class="row">
            <!-- Section Overview -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Section Overview</h5>
                        <ul class="list-unstyled">
                            <li><strong>Section Name:</strong> {{ $section->name }}</li>
                            <li><strong>Year Level:</strong> {{ $section->year_level }}</li>
                            <li><strong>Semester:</strong> {{ $section->semester }}</li>
                            <li><strong>School Year:</strong> {{ $section->school_year }}</li>
                            <li><strong>College:</strong> {{ $section->college->name }}</li>
                            <li><strong>Department:</strong> {{ $section->department->name }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Students List</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Student Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $key => $student)
                                        <tr>
                                            <th>{{ $key + 1 }}</th>
                                            <td>{{ $student->full_name }}</td>
                                            <td>{{ $student->username }}</td>
                                            <td>{{ $student->email }}</td>
                                            <td>
                                                <span
                                                    class="badge rounded-pill {{ $student->status ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $student->status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group dropstart">
                                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3" onclick="event.stopPropagation()">
                                                        <li>
                                                            <a href="{{ route('student.view', ['student' => $student->id]) }}" class="dropdown-item">View</a>
                                                        </li>
                                                        <li>
                                                            <button @click="$dispatch('edit-mode',{id:{{ $student->id }}})" type="button"
                                                                    class="dropdown-item" data-bs-toggle="modal" data-bs-target="#verticalycentered">Edit</button>
                                                        </li>
                                                        <li>
                                                            <button wire:click="delete({{ $student->id }})"
                                                                    wire:confirm="Are you sure you want to delete '{{ $student->first_name }} {{ $student->last_name }}'?"
                                                                    type="button" class="dropdown-item text-danger">Delete {{ $student->username }}</button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedules List -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Schedules</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Instructor Name</th>
                                        <th>Section Code</th>
                                        <th>Days of Week</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($schedules as $key => $schedule)
                                        <tr>
                                            <th>{{ $key + 1 }}</th>
                                            <td>{{ $schedule->instructor->full_name ?? 'N/A' }}</td>
                                            <td>{{ $schedule->schedule_code }}</td>
                                            <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                                            <td class="text-center">
                                                <div class="btn-group dropstart">
                                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3" onclick="event.stopPropagation()">
                                                        <li>
                                                            <a href="{{ route('schedule.view', ['schedule' => $schedule->id]) }}" class="dropdown-item">View</a>
                                                        </li>
                                                        <li>
                                                            <button @click="$dispatch('edit-mode',{id:{{ $schedule->id }}})" type="button"
                                                                    class="dropdown-item" data-bs-toggle="modal" data-bs-target="#verticalycenteredschedule">Edit</button>
                                                        </li>
                                                        <li>
                                                            <button wire:click="delete({{ $schedule->id }})"
                                                                    wire:confirm="Are you sure you want to delete schedule '{{ $schedule->schedule_code }}'?"
                                                                    type="button" class="dropdown-item text-danger">Delete</button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>
