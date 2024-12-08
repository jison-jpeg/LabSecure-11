<div>
    <section class="section dashboard">
        <div class="row">
            <livewire:edit-user :user="$student" />

            <!-- Student Overview -->
            <div class="col-12 d-flex flex-column">
                <div class="card h-100 card-info position-relative">
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
                                        <a class="dropdown-item" href="#"
                                            wire:click="$dispatch('show-edit-user-modal')">Edit User</a>

                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#">
                                            Delete User
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        <div class="d-flex align-items-center">
                            <h5 class="card-title fs-3">{{ $student->full_name }}</h5>
                            <div
                                class="badge rounded-pill ms-3
                                {{ $student->status === 'active' ? 'bg-success text-light' : 'bg-danger text-light' }}">
                                {{ ucfirst($student->status) }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>Username</h6>
                                <p>{{ $student->username }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Email</h6>
                                <p>{{ $student->email }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Role</h6>
                                <p>{{ $student->role->name }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h6>College</h6>
                                <p>{{ $student->college ? $student->college->name : 'N/A' }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Department</h6>
                                <p>{{ $student->department ? $student->department->name : 'N/A' }}</p>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6>Year and Section</h6>
                                <p>
                                    @if ($student->section)
                                        {{ $student->section->year_level }} - {{ $student->section->name }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">View User</h5>
                                <!-- Bordered Tabs Justified -->
                                <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified"
                                    role="tablist">
                                    <li class="nav-item flex-fill" role="presentation">
                                        <button class="nav-link w-100 active" id="home-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-home" type="button" role="tab"
                                            aria-controls="home" aria-selected="true">Attendances</button>
                                    </li>
                                    <li class="nav-item flex-fill" role="presentation">
                                        <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab"
                                            data-bs-target="#bordered-justified-profile" type="button" role="tab"
                                            aria-controls="profile" aria-selected="false"
                                            tabindex="-1">Schedules</button>
                                    </li>
                                </ul>
                                <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                    <div class="tab-pane fade active show" id="bordered-justified-home" role="tabpanel"
                                        aria-labelledby="home-tab">
                                        @livewire('attendance-table', ['userId' => $student->id, 'hideFilters' => ['college', 'department', 'section', 'yearLevel']])
                                    </div>
                                    <div class="tab-pane fade" id="bordered-justified-profile" role="tabpanel"
                                        aria-labelledby="profile-tab">
                                        @livewire('schedule-table', ['userId' => $student->id])

                                    </div>
                                    {{-- Schedule Table --}}
                                    
                                </div><!-- End Bordered Tabs Justified -->

                            </div>
                        </div>

                    </div>
                </div>

            </div>





        </div>
    </section>
</div>
