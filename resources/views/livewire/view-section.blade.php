@php
    use Carbon\Carbon;
@endphp

<div>
    <livewire:edit-section :section="$section" />
    <section class="section dashboard">
        <div class="row">
            <!-- Section Overview -->

            <div class="col-12 d-flex flex-column">
                <div class="card h-100 card-info position-relative mb-4">
                    <div class="card-body text-white">
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
                                        data-bs-target="#editSectionModal">Edit Section</a>

                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#">
                                        Delete Section
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <h5 class="card-title fs-3">Section {{ $section->name }}</h5>

                        <div class="row">
                            <div class="col-6 col-md-4">
                                <h6 class="text-truncate">Year Level</h6>
                                <p class="text-truncate">{{ $section->year_level }}</p>
                            </div>

                            <div class="col-6 col-md-4">
                                <h6 class="text-truncate">Semester</h6>
                                <p class="text-truncate">{{ $section->semester }}</p>
                            </div>

                            <div class="col-6 col-md-4">
                                <h6 class="text-truncate">School Year</h6>
                                <p class="text-truncate">{{ $section->school_year }}</p>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-6 col-md-4">
                                <h6 class="text-truncate">College</h6>
                                <p class="text-truncate">{{ $section->college->name }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <h6 class="text-truncate">Department</h6>
                                <p class="text-truncate">{{ $section->department->name }}</p>
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
                                <h5 class="card-title">View Section</h5>
                                <!-- Bordered Tabs Justified -->
                                <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified"
                                    role="tablist">
                                    <li class="nav-item flex-fill" role="presentation">
                                        <button class="nav-link w-100 @if ($activeTab === 'students') active @endif"
                                            wire:click="switchTab('students')" type="button" role="tab">
                                            Students List
                                        </button>
                                    </li>
                                    <li class="nav-item flex-fill" role="presentation">
                                        <button class="nav-link w-100 @if ($activeTab === 'schedules') active @endif"
                                            wire:click="switchTab('schedules')" type="button" role="tab">
                                            Schedules
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                    @if ($activeTab === 'students')
                                        <div class="tab-pane fade active show" id="bordered-justified-home"
                                            role="tabpanel">
                                            <!-- Students List -->
                                            <div class="col-lg-12 mt-4">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Student Name</th>
                                                                <th>Username</th>
                                                                <th>Email</th>
                                                                <th>Status</th>
                                                                {{-- <th class="text-center">Actions</th> --}}
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($students as $key => $student)
                                                                <tr wire:key="{{ $student->id }}"
                                                                    onclick="window.location='{{ route('student.view', ['student' => $student->id]) }}';"
                                                                    style="cursor: pointer;">
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
                                                                    {{-- <td class="text-center">
                                                                        <div class="btn-group dropstart">
                                                                            <a class="icon" href="#"
                                                                                data-bs-toggle="dropdown"
                                                                                aria-expanded="false"
                                                                                onclick="event.stopPropagation()">
                                                                                <i class="bi bi-three-dots"></i>
                                                                            </a>
                                                                            <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3"
                                                                                onclick="event.stopPropagation()">
                                                                                <!-- View Action -->
                                                                                <li>
                                                                                    <a href="{{ route('student.view', ['student' => $student->id]) }}"
                                                                                        class="dropdown-item">View</a>
                                                                                </li>

                                                                                <!-- Edit Action -->
                                                                                <li>
                                                                                    <button
                                                                                        wire:click="$dispatch('show-edit-user-modal', { student: {{ $student->id }} })"
                                                                                        type="button"
                                                                                        class="dropdown-item">
                                                                                        Edit
                                                                                    </button>
                                                                                </li>

                                                                                <!-- Delete Action -->
                                                                                <li>
                                                                                    <button
                                                                                        wire:click="delete({{ $student->id }})"
                                                                                        wire:confirm="Are you sure you want to delete '{{ $student->full_name }}'?"
                                                                                        type="button"
                                                                                        class="dropdown-item text-danger">
                                                                                        Delete
                                                                                    </button>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </td> --}}

                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="6" class="text-center">
                                                                        No students found.
                                                                    </td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                    {{ $students->links() }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($activeTab === 'schedules')
                                        <div class="tab-pane fade active show" id="bordered-justified-profile"
                                            role="tabpanel">
                                            <!-- Schedules List -->
                                            <div class="col-lg-12 mt-4">
                                                <div class="table-responsive">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Section Code</th>
                                                                <th>Instructor Name</th>
                                                                <th>Days of Week</th>
                                                                <th>Start Time</th>
                                                                <th>End Time</th>
                                                                {{-- <th class="text-center">Actions</th> --}}
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($schedules as $key => $schedule)
                                                                <tr wire:key="{{ $schedule->id }}"
                                                                    onclick="window.location='{{ Auth::user()->isAdmin() || Auth::user()->isChairperson() || Auth::user()->isDean() ? route('schedule.view', $schedule->id) : route('class.view', $schedule->id) }}';"
                                                                    style="cursor: pointer;">
                                                                    <th>{{ $key + 1 }}</th>
                                                                    <td>{{ $schedule->schedule_code }}</td>
                                                                    <td>{{ $schedule->instructor->full_name ?? 'N/A' }}
                                                                    </td>
                                                                    <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}
                                                                    </td>
                                                                    <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                                                    </td>
                                                                    <td>{{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                                                    </td>
                                                                    {{-- <td class="text-center">
                                                                        <div class="btn-group dropstart">
                                                                            <a class="icon" href="#"
                                                                                data-bs-toggle="dropdown"
                                                                                aria-expanded="false"
                                                                                onclick="event.stopPropagation()">
                                                                                <i class="bi bi-three-dots"></i>
                                                                            </a>
                                                                            <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3"
                                                                                onclick="event.stopPropagation()">
                                                                                <!-- View Action -->
                                                                                <li>
                                                                                    <a href="{{ route('schedule.view', ['schedule' => $schedule->id]) }}"
                                                                                        class="dropdown-item">View</a>
                                                                                </li>

                                                                                <!-- Edit Action -->
                                                                                <li>
                                                                                    <button
                                                                                        @click="$dispatch('edit-mode', { id: {{ $schedule->id }} })"
                                                                                        type="button"
                                                                                        class="dropdown-item"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#verticalycenteredschedule">
                                                                                        Edit
                                                                                    </button>
                                                                                </li>

                                                                                <!-- Delete Action -->
                                                                                <li>
                                                                                    <button
                                                                                        wire:click="delete({{ $schedule->id }})"
                                                                                        wire:confirm="Are you sure you want to delete schedule '{{ $schedule->schedule_code }}'?"
                                                                                        type="button"
                                                                                        class="dropdown-item text-danger">
                                                                                        Delete
                                                                                    </button>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </td> --}}

                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    {{ $schedules->links() }}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </section>
</div>
