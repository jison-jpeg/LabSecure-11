@php
    use Carbon\Carbon;
@endphp

<div>
    {{-- edit attendance --}}
    @livewire('edit-attendance')

    <!-- Export Modal -->
    <div wire:ignore.self class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Export Attendance Records</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form wire:submit.prevent="prepareExport" class="row g-2 needs-validation" novalidate>

                        <!-- Search Filter -->
                        <div class="col-9 col-sm-8 col-md-8">
                            <label for="search" class="form-label">Search</label>
                            <input wire:model.live.debounce.300ms="search" type="text" name="search"
                                class="form-control" placeholder="Search users...">
                        </div>

                        <!-- Month Filter -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <label for="selectedMonth" class="form-label">Month</label>
                            <input type="{{ $dateInputType }}" wire:model.live="selectedMonth" name="selectedMonth"
                                class="form-control">
                        </div>

                        <!-- Role-Based Filters -->
                        @php
                            $user = Auth::user();
                        @endphp

                        @if ($user->isAdmin())
                            <!-- College Filter -->
                            <div class="col-6 col-sm-4 col-md-2">
                                <label for="selectedCollege" class="form-label">College</label>
                                <select wire:model.live="selectedCollege" name="selectedCollege" class="form-select">
                                    <option value="">All Colleges</option>
                                    @foreach ($colleges as $college)
                                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if ($user->isAdmin() || $user->isDean())
                            <!-- Department Filter -->
                            <div class="col-6 col-sm-4 col-md-2">
                                <label for="selectedDepartment" class="form-label">Department</label>
                                <select wire:model.live="selectedDepartment" name="selectedDepartment"
                                    class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if ($user->isAdmin() || $user->isDean() || $user->isChairperson() || $user->isInstructor())
                            <!-- Year Level Filter -->
                            <div class="col-6 col-sm-4 col-md-2">
                                <label for="selectedYearLevel" class="form-label">Year Level</label>
                                <select wire:model.live="selectedYearLevel" name="selectedYearLevel"
                                    class="form-select">
                                    <option value="">All Year Levels</option>
                                    @foreach ($yearLevels as $yearLevel)
                                        <option value="{{ $yearLevel }}">{{ $yearLevel }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if ($user->isAdmin() || $user->isDean() || $user->isChairperson() || $user->isInstructor())
                            <!-- Section Filter -->
                            <div class="col-6 col-sm-4 col-md-2">
                                <label for="selectedSection" class="form-label">Section</label>
                                <select wire:model.live="selectedSection" name="selectedSection" class="form-select">
                                    <option value="">All Sections</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if ($user->isAdmin() || $user->isDean() || $user->isChairperson() || $user->isInstructor() || $user->isStudent())
                            <!-- Subject Filter -->
                            <div class="col-6 col-sm-6 col-md-2">
                                <label for="selectedSubject" class="form-label">Subject</label>
                                <select wire:model.live="selectedSubject" name="selectedSubject" class="form-select">
                                    <option value="">All Subjects</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Status Filter -->
                        <div class="col-6 col-sm-6 col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select wire:model.live="status" name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="excused">Excused</option>
                                <option value="incomplete">Incomplete</option>
                            </select>
                        </div>



                        <!-- Hidden Input to Store Export Format -->
                        <input type="hidden" wire:model="exportFormat" />
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="exportAs">Export as</span>
                            <span wire:loading wire:target="exportAs">
                                <span class="spinner-border spinner-border-sm" role="status"
                                    aria-hidden="true"></span>
                                Exporting...
                            </span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item" href="#" wire:click.prevent="exportAs('csv')">
                                    <span wire:loading.remove wire:target="exportAs('csv')">CSV</span>
                                    <span wire:loading wire:target="exportAs('csv')">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        Exporting CSV...
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" wire:click.prevent="exportAs('excel')">
                                    <span wire:loading.remove wire:target="exportAs('excel')">Excel</span>
                                    <span wire:loading wire:target="exportAs('excel')">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        Exporting Excel...
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" wire:click.prevent="exportAs('pdf')">
                                    <span wire:loading.remove wire:target="exportAs('pdf')">PDF</span>
                                    <span wire:loading wire:target="exportAs('pdf')">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        Exporting PDF...
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-4 align-items-start">
        <!-- Filters Section -->
        <div class="col-md-10">
            <!-- Dropdown Menu -->
            {{-- @if (Auth::user()->isAdmin()) --}}
            <div class="filter mb-3">
                <a class="icon" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Options</h6>
                    </li>
                    {{-- <li><a href="#" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#importModal">Import</a></li> --}}
                    <li><a href="#" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#exportModal">Export</a></li>

                    {{-- <li class="dropdown-submenu position-relative">
                        <a class="dropdown-item dropdown-toggle" href="#">Export As</a>
                        <ul class="dropdown-menu position-absolute">
                            <li><a wire:click.prevent="exportAs('csv')" href="#" class="dropdown-item">CSV</a></li>
                            <li><a wire:click.prevent="exportAs('excel')" href="#" class="dropdown-item">Excel</a></li>
                            <li><a wire:click.prevent="exportAs('pdf')" href="#" class="dropdown-item">PDF</a></li>
                            <li><a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#exportModal">Export Attendance</a></li>
                        </ul>
                    </li>
                    <li><a class="dropdown-item text-danger" href="#">Delete Selected</a></li> --}}
                </ul>
            </div>
            {{-- @endif --}}

            <!-- Filters Row -->
            <div class="row g-2">
                <!-- Per Page Filter -->
                <div class="col-3 col-sm-4 col-md-1">
                    <select wire:model.live="perPage" name="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                {{-- Role Filter --}}

                <!-- Search Filter -->
                <div class="col-9 col-sm-8 col-md-4">
                    <input wire:model.live.debounce.300ms="search" type="text" name="search"
                        class="form-control" placeholder="Search users...">
                </div>

                @php
                    $user = Auth::user();
                @endphp

                @if ($user->isAdmin() && !in_array('college', $hideFilters))
                    <!-- College Filter -->
                    <div class="col-6 col-sm-4 col-md-2">
                        <select wire:model.live="selectedCollege" name="selectedCollege" class="form-select">
                            <option value="">All Colleges</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (($user->isAdmin() || $user->isDean()) && !in_array('department', $hideFilters))
                    <!-- Department Filter -->
                    <div class="col-6 col-sm-4 col-md-2">
                        <select wire:model.live="selectedDepartment" name="selectedDepartment" class="form-select">
                            <option value="">All Departments</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (
                    ($user->isAdmin() || $user->isDean() || $user->isChairperson() || $user->isInstructor()) &&
                        !in_array('yearLevel', $hideFilters))
                    <!-- Year Level Filter -->
                    <div class="col-6 col-sm-4 col-md-2">
                        <select wire:model.live="selectedYearLevel" name="selectedYearLevel" class="form-select">
                            <option value="">All Year Levels</option>
                            @foreach ($yearLevels as $yearLevel)
                                <option value="{{ $yearLevel }}">{{ $yearLevel }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (
                    ($user->isAdmin() || $user->isDean() || $user->isChairperson() || $user->isInstructor()) &&
                        !in_array('section', $hideFilters))
                    <!-- Section Filter -->
                    <div class="col-6 col-sm-4 col-md-2">
                        <select wire:model.live="selectedSection" name="selectedSection" class="form-select">
                            <option value="">All Sections</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (
                    ($user->isAdmin() || $user->isDean() || $user->isChairperson() || $user->isInstructor() || $user->isStudent()) &&
                        !in_array('subject', $hideFilters))
                    <!-- Subject Filter -->
                    <div class="col-6 col-sm-6 col-md-2">
                        <select wire:model.live="selectedSubject" name="selectedSubject" class="form-select">
                            <option value="">All Subjects</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif


                <!-- Status Filter -->
                <div class="col-6 col-sm-6 col-md-2">
                    <select wire:model.live="status" name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="excused">Excused</option>
                        <option value="incomplete">Incomplete</option>
                    </select>
                </div>

                <!-- Month Filter -->
                <div class="col-12 col-sm-6 col-md-2">
                    <input type="{{ $dateInputType }}" wire:model.live="selectedMonth" name="selectedMonth"
                        class="form-control">
                </div>
            </div>
        </div>

        <!-- Clear Filters Button -->
        <div class="col-md-2 d-flex align-items-start mt-3 mt-md-0">
            <button class="btn btn-secondary w-100" type="reset" wire:click="clear">Clear Filters</button>
        </div>
    </div>


    <!-- Attendance Table -->
    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'date',
                        'displayName' => 'Date',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'user.username',
                        'displayName' => 'Username',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'user.name',
                        'displayName' => 'User',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'schedule.schedule_code',
                        'displayName' => 'Section Code',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'subject.name',
                        'displayName' => 'Subject',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'schedule.section.name',
                        'displayName' => 'Section',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'time_in',
                        'displayName' => 'Time In',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'time_out',
                        'displayName' => 'Time Out',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'percentage',
                        'displayName' => 'Percentage',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'status',
                        'displayName' => 'Status',
                    ])
                    <th scope="col" class="text-dark fw-semibold">Remarks</th>
                    @if (Auth::user()->isAdmin() || Auth::user()->isDean() || Auth::user()->isChairperson() || Auth::user()->isInstructor())
                        <th scope="col" class="text-center text-dark fw-semibold">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($attendances as $attendance)
                    <tr wire:key="{{ $attendance->id ?? $attendance->user->id }}"
                        onclick="window.location='{{ route('attendance.user.view', [
                            'user' => $attendance->user->id,
                            'selectedMonth' => Carbon::parse($attendance->date)->format('Y-m'),
                            'selectedSubject' => $attendance->schedule->subject->id ?? null, // Include subject ID

                        ]) }}';"
                        style="cursor: pointer;">

                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ isset($attendance->date) ? Carbon::parse($attendance->date)->format('m/d/Y') : 'N/A' }}
                        </td>
                        <td>{{ $attendance->user->username }}</td>
                        <td>{{ $attendance->user->full_name }}</td>
                        <td>{{ $attendance->schedule->schedule_code ?? 'N/A' }}</td>
                        <td>{{ $attendance->schedule->subject->name ?? 'N/A' }}</td>
                        <td>{{ $attendance->schedule->section->name ?? 'N/A' }}</td>
                        <td>
                            @if (isset($attendance->sessions) && $attendance->sessions->isNotEmpty())
                                {{ optional($attendance->sessions->first())->time_in ? Carbon::parse($attendance->sessions->first()->time_in)->format('h:i A') : '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if (isset($attendance->sessions) && $attendance->sessions->isNotEmpty())
                                {{ optional($attendance->sessions->last())->time_out ? Carbon::parse($attendance->sessions->last()->time_out)->format('h:i A') : '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="progress mt-progress">
                                <div class="progress-bar 
                                {{ $attendance->percentage < 50 ? 'bg-danger' : ($attendance->percentage < 70 ? 'bg-warning' : 'bg-success') }}"
                                    role="progressbar" style="width: {{ $attendance->percentage }}%;"
                                    aria-valuenow="{{ $attendance->percentage }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                    {{ $attendance->percentage }}%
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span
                                class="badge rounded-pill 
                            {{ $attendance->status == 'present'
                                ? 'bg-success'
                                : ($attendance->status == 'late'
                                    ? 'bg-warning'
                                    : ($attendance->status == 'absent'
                                        ? 'bg-danger'
                                        : ($attendance->status == 'incomplete'
                                            ? 'bg-secondary'
                                            : 'bg-secondary'))) }}">
                                {{ ucfirst($attendance->status) }}
                            </span>
                        </td>
                        <td>{{ $attendance->remarks }}</td>
                        @if (Auth::user()->isAdmin() || Auth::user()->isDean() || Auth::user()->isChairperson() || Auth::user()->isInstructor())
                            <td class="text-center">
                                <div class="btn-group dropstart">
                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false"
                                        onclick="event.stopPropagation()">
                                        <i class="bi bi-three-dots"></i>
                                    </a>
                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3"
                                        onclick="event.stopPropagation()">
                                        <li>
                                            <a href="{{ route('attendance.user.view', [
                                                'user' => $attendance->user->id,
                                                'selectedMonth' => Carbon::parse($attendance->date)->format('Y-m'),
                                                'selectedSubject' => $attendance->schedule->subject->id ?? null, // Include subject ID
                                            ]) }}" class="dropdown-item">View</a>
                                        </li>
                                        <li>
                                            <button
                                                @click="$dispatch('edit-mode', {
                                                userId: {{ $attendance->user->id }},
                                                scheduleId: {{ $attendance->schedule->id ?? 'null' }},
                                                date: '{{ $attendance->date ?? Carbon::today()->format('Y-m-d') }}'
                                            })"
                                                type="button" class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#verticalycentered">
                                                Edit
                                            </button>
                                        </li>
                                        <li>
                                            @if (isset($attendance->id))
                                                <button wire:click="delete({{ $attendance->id }})"
                                                    wire:confirm="Are you sure you want to delete this record?"
                                                    type="button" class="dropdown-item text-danger">Delete</button>
                                            @else
                                                <!-- Optionally handle delete for default records if needed -->
                                                <button type="button" class="dropdown-item text-danger"
                                                    disabled>Delete</button>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center">
                            @if ($this->scheduleId)
                                No students are assigned to this schedule.
                            @else
                                @if (!empty($scheduleId) && $students->isEmpty())
                                    No students are assigned to this section.
                                @else
                                    No attendance records available.
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $attendances->links() }}
    </div>


</div>


<script>
    document.addEventListener('livewire:initialized', () => {
        @this.on('refresh-attendance-table', (event) => {
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

    document.addEventListener('DOMContentLoaded', function() {
        var dropdowns = document.querySelectorAll('.dropdown-submenu');

        dropdowns.forEach(function(dropdown) {
            dropdown.addEventListener('mouseover', function() {
                let submenu = this.querySelector('.dropdown-menu');
                submenu.classList.add('show');
            });

            dropdown.addEventListener('mouseout', function() {
                let submenu = this.querySelector('.dropdown-menu');
                submenu.classList.remove('show');
            });
        });
    });
</script>
