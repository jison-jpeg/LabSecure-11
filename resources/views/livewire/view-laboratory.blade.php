<div>
    <section class="section dashboard">
        <div class="row">

            <!-- Left side columns -->
            <div class="col-lg-3 d-flex flex-column">
                {{-- Lab Stats --}}
                <div class="card h-100">
                    <div class="info-card sales-card">
                        <div class="card-body mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5
                                    class="badge rounded-pill 
                                    {{ $laboratory->status == 'Occupied'
                                        ? 'bg-warning text-black'
                                        : ($laboratory->status == 'Locked'
                                            ? 'bg-danger'
                                            : ($laboratory->status == 'Available'
                                                ? 'bg-success'
                                                : 'bg-secondary')) }}">
                                    {{ $laboratory->status }}
                                </h5>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault">
                                    <label class="form-check-label" for="flexSwitchCheckDefault">Lock</label>
                                </div>
                            </div>
                            <div class="row mt-4 sub-header">
                                <div class="col-6 text-start text-truncate">
                                    <h6 class="text-muted">TYPE</h6>
                                    <span>{{ $laboratory->type }}</span>
                                </div>
                                <div class="col-6 text-end text-truncate">
                                    <h6 class="text-muted">LOCATION</h6>
                                    <span>{{ $laboratory->location }}</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <h1 class="lab-title">LAB</h1>
                                </div>
                                <div class="col-auto">
                                    <h5 class="sub-lab-title">{{ $laboratory->name }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Left side columns -->

            <!-- Right side columns -->
            <div class="col-lg-9 d-flex flex-column">
                <!-- Recent Activity -->
                <div class="card h-100">
                    <div class="action">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li class="dropdown-header text-start">
                                <h6>Action</h6>
                            </li>
                            <li><a @click="$dispatch('edit-mode',{id:{{ $laboratory->id }}})" class="dropdown-item"
                                    data-bs-toggle="modal" data-bs-target="#verticalycentered">Edit</a></li>
                            <li><a wire:click="delete({{ $laboratory->id }})"
                                    wire:confirm="Are you sure you want to delete laboratory {{ $laboratory->name }} ?"
                                    class="dropdown-item text-danger" href="#">Delete LAB
                                    {{ $laboratory->name }}</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Laboratory Details</h5>
                        <div class="row mb-4">
                            <div class="col-6">
                                <h6>Laboratory Name</h6>
                                <p>Lab {{ $laboratory->name }}</p>
                            </div>
                            <div class="col-6">
                                <h6>Status</h6>
                                <p
                                    class="badge rounded-pill 
                                {{ $laboratory->status == 'Occupied'
                                    ? 'bg-warning text-black'
                                    : ($laboratory->status == 'Locked'
                                        ? 'bg-danger'
                                        : ($laboratory->status == 'Available'
                                            ? 'bg-success'
                                            : 'bg-secondary')) }}">
                                    {{ $laboratory->status }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <h6>Type</h6>
                                <p>{{ $laboratory->type }}</p>
                            </div>
                            <div class="col-6">
                                <h6>Location</h6>
                                <p>{{ $laboratory->location }}</p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <h6>Current User</h6>
                                <p>
                                    {{ $laboratory->getCurrentUser() 
                                        ? $laboratory->getCurrentUser()->user->full_name 
                                        : 'N/A' }}
                                </p>
                            </div>
                            <div class="col-6">
                                <h6>Recent User</h6>
                                <p>
                                    {{ $laboratory->getRecentUser() 
                                        ? $laboratory->getRecentUser()->user->full_name 
                                        : 'N/A' }}
                                </p>
                            </div>
                        </div>
                        
                        
                    </div>
                </div>
                <!-- End Recent Activity -->
            </div>
            <!-- End Right side columns -->

            <!-- Recent User Activity -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Activity</h5>
                        <div class="row mb-4">
                            <div class="col-md-10">

                                {{-- perpage --}}
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
                                            <option value="check_in">Check In</option>
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
                                            wire:click="clear">Clear
                                            Filters</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Table with hoverable rows -->
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Time</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">1</th>
                                    <td>09/18/2024</td>
                                    <td>7:30 AM</td>
                                    <td>123456789</td>
                                    <td>John Doe</td>
                                    <td>Student</td>
                                    <td>Check In</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End Table with hoverable rows -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
