@php
    use Carbon\Carbon;
@endphp

<div>
    <section class="section dashboard">
        <div class="row">
            <!-- Schedule Overview -->
            <div class="col-12 d-flex flex-column">
                <div class="card h-100 card-info position-relative mb-4">
                    <div class="card-body text-white">
                        <h5 class="card-title fs-3">Class {{ ucfirst($schedule->schedule_code) }} - {{ $schedule->section->name }}</h5>

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
                                <p>{{ $schedule->section->name }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>Days</h6>
                                <p>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Time</h6>
                                <p>{{ $schedule->start_time }} - {{ $schedule->end_time }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Students/Attendance -->
            <div class="col-12 d-flex flex-column">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Students Attendance</h5>

                        <div class="row align-items-end mb-3">
                            <div class="col-12 col-lg-10">
                                <div class="row g-2">
                                    <div class="col-3 col-lg-1">
                                        <select wire:model.live="perPage" name="perPage" class="form-select">
                                            <option value="10">10</option>
                                            <option value="15">15</option>
                                            <option value="20">20</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </div>

                                    <div class="col-9 col-lg-3">
                                        <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control" placeholder="Search students...">
                                    </div>

                                    <div class="col-3 col-lg-3">
                                        <select wire:model.live="status" name="status" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="late">Late</option>
                                            <option value="incomplete">Incomplete</option>
                                        </select>
                                    </div>

                                    <div class="col-9 col-lg-3">
                                        <input type="date" wire:model.live="selectedDate" name="selectedDate" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-2 mt-3 mt-lg-0">
                                <button class="btn btn-secondary w-100" type="reset" wire:click="clear">Clear Filters</button>
                            </div>
                        </div>

                        <!-- Students and Attendance Table -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($students as $key => $student)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ Carbon::now()->format('m/d/Y') }}</td>
                                            <td>{{ $student->full_name }}</td>
                                            <td>{{ $student->username }}</td>
                                            <td>{{ $student->email }}</td>
                                            <td>
                                                @if (isset($attendances[$student->id]))
                                                    {{ ucfirst($attendances[$student->id]->status) }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group dropstart">
                                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots"></i>
                                                    </a>
                                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                                        <li>
                                                            <a href="{{ route('user.view', ['user' => $student->id]) }}" class="dropdown-item">View</a>
                                                        </li>
                                                        <li><button @click="$dispatch('edit-mode',{id:{{ $student->id }}})" type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#verticalycentered">Edit</button></li>
                                                        <li><button wire:click="delete({{ $student->id }})" wire:confirm="Are you sure you want to delete this record?" type="button" class="dropdown-item text-danger">Delete</button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No students found for this status</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $students->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
