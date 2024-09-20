@php
    use Carbon\Carbon;
@endphp

<div>
    <section class="section dashboard">
        <div class="row">

            <!-- User Details -->
            <div class="col-lg-4 d-flex flex-column">
                <div class="card h-100">
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

            <!-- Class Schedule -->
            <div class="col-lg-8 d-flex flex-column">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Class Schedule</h5>

                        @if ($schedules->isNotEmpty())
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
                                            class="form-control" placeholder="Search logs...">
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <select wire:model.live="action" name="action" class="form-select">
                                            <option value="">Action Type</option>
                                            <option value="create">Create</option>
                                            <option value="update">Update</option>
                                            <option value="delete">Delete</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <select wire:model.live="role" name="role" class="form-select">
                                            <option value="">User Type</option>
                                            <option value="admin">Admin</option>
                                            <option value="student">Student</option>
                                            <option value="instructor">Instructor</option>
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

                        <div class="mt-4">
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
