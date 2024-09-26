@php
    use Carbon\Carbon;
@endphp

<div>
    <section class="section dashboard">
        <div class="row">
    
            <!-- User Details -->
            <div class="col-12 d-flex flex-column">
                <div class="card h-100 card-info">
                    <div class="card-body text-white">
                        <h5 class="card-title fs-3">{{ $user->full_name }}</h5>
                        <div
                        class="badge rounded-pill 
                        {{ $user->status === 'active' ? 'bg-success text-light' : 'bg-danger text-light' }} 
                        position-absolute top-0 end-0 m-3">
                        {{ ucfirst($user->status) }}
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>Username</h6>
                                <p>{{ $user->username }}</p>
                            </div>
    
                            <div class="col-12 col-md-4">
                                <h6>Email</h6>
                                <p>{{ $user->email }}</p>
                            </div>
    
                            <div class="col-12 col-md-4">
                                <h6>Role</h6>
                                <p>{{ $user->role->name }}</p>
                            </div>
                        </div>
    
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>College</h6>
                                <p>{{ $user->college ? $user->college->name : 'N/A' }}</p>
                            </div>
    
                            <div class="col-12 col-md-4">
                                <h6>Department</h6>
                                <p>{{ $user->department ? $user->department->name : 'N/A' }}</p>
                            </div>
    
                            <div class="col-12 col-md-4">
                                <h6>Section</h6>
                                <p>{{ $user->section ? ($user->schedules->first() ? $user->schedules->first()->schedule_code . ' ' . $user->section->name : $user->section->name) : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- Class Schedule -->
            <div class="col-12 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Class Schedule</h5>
    
                        @if ($schedules->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Subject</th>
                                            <th>Section Code</th>
                                            <th>Section</th>
                                            <th>Days</th>
                                            <th>Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($schedules as $key => $schedule)
                                            <tr>
                                                <th>{{ $key + 1 }}</th>
                                                <td>{{ $schedule->subject->name }}</td>
                                                <td>{{ $schedule->schedule_code }}</td>
                                                <td>{{ $schedule->section->name }}</td>
                                                <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</td>
                                                <td>{{ Carbon::parse($schedule->start_time)->format('h:i A') }} -
                                                    {{ Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>No class schedules available for this user.</p>
                        @endif
                    </div>
                </div>
            </div>
    
            <!-- Recent Activity Logs -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Activity</h5>
                        <div class="row mb-4">
                            <div class="col-md-10">
                                <div class="row g-1">
                                    <div class="col-6 col-md-2">
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
                                            class="form-control" placeholder="Search logs...">
                                    </div>
    
                                    <div class="col-6 col-md-2">
                                        <select wire:model.live="action" name="action" class="form-select">
                                            <option value="">Action Type</option>
                                            <option value="create">Create</option>
                                            <option value="update">Update</option>
                                            <option value="delete">Delete</option>
                                        </select>
                                    </div>
    
                                    <div class="col-6 col-md-2">
                                        <select wire:model.live="role" name="role" class="form-select">
                                            <option value="">User Type</option>
                                            <option value="admin">Admin</option>
                                            <option value="student">Student</option>
                                            <option value="instructor">Instructor</option>
                                        </select>
                                    </div>
    
                                    <div class="col-12 col-md-3">
                                        <button class="btn btn-secondary w-100" type="reset" wire:click="clear">Clear Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <!-- Table with hoverable rows -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Role</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logs as $key => $log)
                                        <tr>
                                            <th>{{ $logs->firstItem() + $key }}</th>
                                            <td>{{ $log->created_at->format('m/d/Y') }}</td>
                                            <td>{{ $log->created_at->format('h:i A') }}</td>
                                            <td>{{ $log->user->username }}</td>
                                            <td>{{ $log->user->full_name }}</td>
                                            <td>{{ $log->user->role->name }}</td>
                                            <td>{{ ucfirst($log->action) }}</td>
                                            <td>{{ $log->readable_details }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
    
                        <div class="mt-4">
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
</div>
