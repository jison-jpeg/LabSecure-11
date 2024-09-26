<div>
    <section class="section dashboard">
        <!-- Schedule Overview -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Schedule Overview</h5>
                        <ul>
                            <li><strong>Subject:</strong> {{ $schedule->subject->name }}</li>
                            <li><strong>Instructor:</strong> {{ $schedule->instructor->full_name }}</li>
                            <li><strong>Schedule Code:</strong> {{ $schedule->schedule_code }}</li>
                            <li><strong>Section:</strong> {{ $schedule->section->name }}</li>
                            <li><strong>Time:</strong> {{ $schedule->start_time }} - {{ $schedule->end_time }}</li>
                            <li><strong>Days:</strong> {{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Students/Attendance -->
        <div class="row">
            <div class="row mb-4">
                <div class="col-md-10">
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li class="dropdown-header text-start">
                                <h6>Option</h6>
                            </li>
                            <li><a wire:click.prevent="import" href="#" class="dropdown-item">Import</a></li>
                            <li class="dropdown-submenu position-relative">
                                <a class="dropdown-item dropdown-toggle" href="#">Export As</a>
                                <ul class="dropdown-menu position-absolute">
                                    <li><a wire:click.prevent="exportAs('csv')" href="#"
                                            class="dropdown-item">CSV</a></li>
                                    <li><a wire:click.prevent="exportAs('excel')" href="#"
                                            class="dropdown-item">Excel</a></li>
                                    <li><a wire:click.prevent="exportAs('pdf')" href="#"
                                            class="dropdown-item">PDF</a></li>
                                </ul>
                            </li>
                            <li><a class="dropdown-item text-danger" href="#">Delete Selected</a></li>
                        </ul>
                    </div>

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
                                class="form-control" placeholder="Search students...">
                        </div>

                        <div class="col-12 col-md-2">
                            <select wire:model.live="status" name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="incomplete">Incomplete</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <input type="date" wire:model.live="selectedDate" name="selectedDate"
                                class="form-control">
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear Filters</button>
                </div>
            </div>

            <!-- Students and Attendance Table -->
            <div class="overflow-auto">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
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
                                        <a class="icon" href="#" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="bi bi-three-dots"></i>
                                        </a>
                                        <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                            <li>
                                                <a href="{{ route('user.view', ['user' => $student->id]) }}"
                                                    class="dropdown-item">View</a>
                                            </li>
                                            <li><button @click="$dispatch('edit-mode',{id:{{ $student->id }}})"
                                                    type="button" class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#verticalycentered">Edit</button></li>
                                            <li><button wire:click="delete({{ $student->id }})"
                                                    wire:confirm="Are you sure you want to delete this record?"
                                                    type="button" class="dropdown-item text-danger">Delete</button>
                                            </li>
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
    </section>
</div>
