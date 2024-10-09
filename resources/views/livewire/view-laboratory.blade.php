<div>
    <section class="section dashboard">
        <div class="row">
            <div wire:ignore.self class="modal fade" id="verticalycentered" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $formTitle }}</h5>
                            <button wire:click="close" type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="update" class="row g-3 needs-validation" novalidate>
                                <div class="col-md-4">
                                    <label for="name" class="form-label">Name</label>
                                    <input wire:model.lazy="name" type="text"
                                        class="form-control @error('name') is-invalid @enderror" name="name">
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="type" class="form-label">Type</label>
                                    <select wire:model="type" name="type"
                                        class="form-select form-control @error('location') is-invalid @enderror">
                                        <option value="">Laboratory Type</option>
                                        <option value="Computer Laboratory">Computer</option>
                                        <option value="EMC Laboratory">Entertainment MC</option>
                                    </select>
                                    @error('type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="location" class="form-label">Location</label>
                                    <input wire:model="location" type="text"
                                        class="form-control @error('location') is-invalid @enderror" name="location">
                                    @error('location')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="modal-footer">
                                    <button wire:click="close" type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <!-- Left side columns -->
            <div class="col-lg-3 d-flex flex-column">
                {{-- Lab Stats --}}
                <div class="card h-100">
                    <div class="info-card sales-card">
                        <div class="card-body mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5
                                    class="badge rounded-pill 
                                    {{ $laboratory->status == 'Occupied' ? 'bg-warning text-black' : ($laboratory->status == 'Locked' ? 'bg-danger' : ($laboratory->status == 'Available' ? 'bg-success' : 'bg-secondary')) }}">
                                    {{ $laboratory->status }}
                                </h5>
                                @if (Auth::user()->isAdmin())
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault"
                                        wire:click="toggleLock" {{ $isLocked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="flexSwitchCheckDefault">
                                        {{ $isLocked ? 'Locked' : 'Unlocked' }}
                                    </label>
                                </div>
                                @endif
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
                    @if (Auth::user()->isAdmin())
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
                    @endif
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
                                    {{ $laboratory->getCurrentUser() ? $laboratory->getCurrentUser()->user->full_name : 'N/A' }}
                                </p>
                            </div>
                            <div class="col-6">
                                <h6>Recent User</h6>
                                <p>
                                    {{ $laboratory->getRecentUser() ? $laboratory->getRecentUser()->user->full_name : 'N/A' }}
                                </p>
                            </div>
                        </div>


                    </div>
                </div>
                <!-- End Recent Activity -->
            </div>
            <!-- End Right side columns -->

            <!-- Recent User Activity -->
            @if (Auth::user()->isAdmin())
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Activity</h5>
                        <div class="row mb-4">
                            <div class="col-md-10">
                                {{-- Per Page --}}
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
                                            <option value="in">Check In</option>
                                            <option value="out">Check Out</option>
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
                                    <th scope="col">#</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Time</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $key => $log)
                                    <tr>
                                        <th scope="row">{{ $logs->firstItem() + $key }}</th>
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
            @endif
        </div>
    </section>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('refresh-laboratory-table', (event) => {
            //alert('product created/updated')
            var myModalEl = document.querySelector('#verticalycentered')
            var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)

            setTimeout(() => {
                modal.hide();
                @this.dispatch('reset-modal');
            });
        })

        var mymodal = document.getElementById('verticalycentered')
        mymodal.addEventListener('hidden.bs.modal', (event) => {
            @this.dispatch('reset-modal');
        })
    })
</script>
