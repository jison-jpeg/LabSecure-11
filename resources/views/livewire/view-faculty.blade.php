<div>
    <section class="section dashboard">
        <div class="row">
            <livewire:edit-user :user="$faculty" />

            <!-- Faculty Overview -->
            <div class="col-12 d-flex flex-column">
                <div class="card h-100 card-info">
                    <div class="card-body">
                        <div class="action">
                            <a class="icon" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots text-white"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Action</h6>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" wire:click="$dispatch('show-edit-user-modal')">Edit User</a>

                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#">
                                        Delete User
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="d-flex align-items-center">
                            <h5 class="card-title fs-3">{{ $faculty->full_name }}</h5>
                            <div
                                class="badge rounded-pill ms-3
                                {{ $faculty->status === 'active' ? 'bg-success text-light' : 'bg-danger text-light' }}">
                                {{ ucfirst($faculty->status) }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>Username</h6>
                                <p>{{ $faculty->username }}</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <h6>Email</h6>
                                <p>{{ $faculty->email }}</p>
                            </div>
                            <div class="col-12 col-md-4">
                                <h6>Role</h6>
                                <p>{{ $faculty->role->name }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>College</h6>
                                <p>{{ $faculty->college ? $faculty->college->name : 'N/A' }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Department</h6>
                                <p>{{ $faculty->department ? $faculty->department->name : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Faculty's Schedules -->
            <div class="col-12 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Class Schedule</h5>
                        <div class="row mb-4">
                            <div class="col-md-10">
                                <div class="row g-1">
                                    <div class="col-md-1">
                                        <select wire:model.live="perPage" class="form-select">
                                            <option value="10">10</option>
                                            <option value="15">15</option>
                                            <option value="20">20</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search subjects...">
                                    </div>

                                    <div class="col-md-2">
                                        <select wire:model.live="selectedScheduleCode" class="form-select">
                                            <option value="">All Schedule Codes</option>
                                            @foreach ($scheduleCodes as $code)
                                                <option value="{{ $code }}">{{ $code }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <select wire:model.live="selectedSection" class="form-select">
                                            <option value="">All Sections</option>
                                            @foreach ($sections as $section)
                                                <option value="{{ $section }}">{{ $section }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <button class="btn btn-secondary w-100" type="reset" wire:click="clear">Clear Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($schedules->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Subject</th>
                                            <th>Schedule Code</th>
                                            <th>Section</th>
                                            <th>Days</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($schedules as $key => $schedule)
                                            <tr onclick="window.location='{{ route('schedule.view', ['schedule' => $schedule->id]) }}';" style="cursor: pointer;">
                                                <th>{{ $key + 1 }}</th>
                                                <td>{{ $schedule->subject->name }}</td>
                                                <td>{{ $schedule->schedule_code }}</td>
                                                <td>{{ $schedule->section->name }}</td>
                                                <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</td>
                                                <td>{{ Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>No class schedules available for this faculty.</p>
                        @endif
                        
                        <div class="mt-4">
                            {{ $schedules->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
