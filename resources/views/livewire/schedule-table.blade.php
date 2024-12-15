@php
    use Carbon\Carbon;
@endphp

<div>
    <!-- Schedule Import Modal -->
    <div wire:ignore.self class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Schedules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <!-- Display Import Summary Message -->
                    @if ($importSummary)
                        <div class="alert alert-info">
                            {{ $importSummary }}
                        </div>
                    @endif

                    <!-- Display Row-level Validation Errors -->
                    @if ($importErrors)
                        <div class="alert alert-danger mt-3">
                            <ul>
                                @foreach ($importErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit.prevent="importSchedules">
                        <div class="mb-3">
                            <label for="scheduleFile" class="form-label">Upload File</label>
                            <input type="file" class="form-control" id="scheduleFile" wire:model="scheduleFile"
                                accept=".csv, .xlsx">
                            @error('scheduleFile')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Display uploading state on the button -->
                        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="scheduleFile">Import</span>
                            <span wire:loading wire:target="scheduleFile">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Uploading...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Export Modal --}}
    <div wire:ignore.self class="modal fade" id="exportScheduleModal" tabindex="-1"
        aria-labelledby="exportScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="exportScheduleModalLabel">Export Schedules</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <form class="row g-3">
                        <!-- College Filter -->
                        <div class="col-6">
                            <label for="college" class="form-label">College</label>
                            <select wire:model.live="college" name="college" class="form-select">
                                <option value="">All Colleges</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-6">
                            <label for="department" class="form-label">Department</label>
                            <select wire:model.live="department" name="department" class="form-select">
                                <option value="">All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Year Level Filter -->
                        <div class="col-6">
                            <label for="yearLevel" class="form-label">Year Level</label>
                            <select wire:model.live="yearLevel" name="yearLevel" class="form-select">
                                <option value="">All Year Levels</option>
                                @foreach ($availableYearLevels as $yearLevel)
                                    <option value="{{ $yearLevel }}">{{ $yearLevel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Section Filter -->
                        <div class="col-6">
                            <label for="section" class="form-label">Section</label>
                            <select wire:model.live="section" name="section" class="form-select">
                                <option value="">All Sections</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
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


    <div class="row mb-4">
        <div class="col-md-10">
                <div class="filter">
                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <li class="dropdown-header text-start">
                            <h6>Option</h6>
                        </li>
                        @if (Auth::user()->isAdmin())
                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#importModal">Import
                            Schedule</a>
                        @endif

                        <a href="#" class="dropdown-item" data-bs-toggle="modal"
                            data-bs-target="#exportScheduleModal">Export
                            Schedule</a>
                        {{-- <li class="dropdown-submenu position-relative">
                        <a class="dropdown-item dropdown-toggle" href="#">Export As</a>
                        <ul class="dropdown-menu position-absolute">
                            <li><a wire:click.prevent="exportAs('csv')" href="#" class="dropdown-item">CSV</a>
                            </li>
                            <li><a wire:click.prevent="exportAs('excel')" href="#" class="dropdown-item">Excel</a>
                            </li>
                            <li><a wire:click.prevent="exportAs('pdf')" href="#" class="dropdown-item">PDF</a>
                            </li>
                        </ul>
                    </li>
                    <li><a class="dropdown-item text-danger" href="#">Delete Selected</a></li> --}}
                    </ul>
                </div>
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
                        class="form-control" placeholder="Search schedules...">
                </div>

                {{-- Conditionally Display College Filter --}}
                @if (auth()->user()->isAdmin())
                    @if (!in_array('college', $hideFilters))

                        <div class="col-12 col-md-2">
                            <select wire:model.live="college" name="college" class="form-select">
                                <option value="">Select College</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @elseif(auth()->user()->isDean())
                    {{-- For Dean, the college is fixed and hidden, so no select is needed --}}
                    <input type="hidden" wire:model="college" value="{{ auth()->user()->college_id }}">
                @endif

                {{-- Conditionally Display Department Filter --}}
                @if (auth()->user()->isAdmin() || auth()->user()->isDean())
                    @if (!in_array('college', $hideFilters))

                        <div class="col-12 col-md-2">
                            <select wire:model.live="department" name="department" class="form-select"
                                @if (auth()->user()->isAdmin() && !$college) disabled @endif>
                                <option value="">Select Department</option>
                                @forelse ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @empty
                                    <option value="" disabled>No departments available</option>
                                @endforelse
                            </select>
                        </div>
                    @endif
                @endif

                {{-- New Year Level Filter --}}
                @if (!in_array('college', $hideFilters))

                    <div class="col-12 col-md-2">
                        <select wire:model.live="yearLevel" name="yearLevel" class="form-select">
                            <option value="">All Year Levels</option>
                            @foreach ($availableYearLevels as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Conditionally Display Section Filter --}}
                @if (auth()->user()->isAdmin() ||
                        auth()->user()->isDean() ||
                        auth()->user()->isChairperson() ||
                        auth()->user()->isInstructor())
                    @if (!in_array('college', $hideFilters))
                        <div class="col-12 col-md-2">
                            <select wire:model.live="section" name="section" class="form-select"
                                @if (!$yearLevel) disabled @endif>
                                <option value="">Select Section</option>
                                @forelse ($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @empty
                                    <option value="" disabled>No sections available</option>
                                @endforelse
                            </select>
                        </div>
                    @endif
                @endif

                {{-- Clear Filters Button --}}
                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        @if (Auth::user()->isAdmin())
            <div class="col-12 col-md-2">
                <livewire:create-schedule />
            </div>
        @endif
    </div>

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'schedule_code',
                        'displayName' => 'Code',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'section.name',
                        'displayName' => 'Section',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'section.year_level', // New Year Level Header (optional)
                        'displayName' => 'Year Level',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'subject.code',
                        'displayName' => 'Subject Code',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'subject.name',
                        'displayName' => 'Subject',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'instructor.full_name',
                        'displayName' => 'Instructor',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'college.name',
                        'displayName' => 'College',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'department.name',
                        'displayName' => 'Department',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'laboratory.name',
                        'displayName' => 'Laboratory',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'days_of_week',
                        'displayName' => 'Weekdays',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'start_time',
                        'displayName' => 'Start Time',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'end_time',
                        'displayName' => 'End Time',
                    ])
                    @if (Auth::user()->isAdmin())
                        <th scope="col" class="text-center text-dark fw-semibold">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($schedules as $key => $schedule)
                    <tr wire:key="{{ $schedule->id }}"
                        onclick="window.location='{{ Auth::user()->isAdmin() || Auth::user()->isChairperson() || Auth::user()->isDean() ? route('schedule.view', $schedule->id) : route('class.view', $schedule->id) }}';"
                        style="cursor: pointer;">
                        <th scope="row">{{ ($schedules->currentPage() - 1) * $schedules->perPage() + $key + 1 }}
                        </th>
                        <td>{{ $schedule->schedule_code }}</td>
                        <td>{{ $schedule->section->name }}</td>
                        <td>{{ $schedule->section->year_level ?? 'N/A' }}</td>
                        <!-- New Year Level Data Column (optional) -->
                        <td>{{ $schedule->subject->code }}</td>
                        <td>{{ $schedule->subject->name }}</td>
                        <td>{{ $schedule->instructor->full_name }}</td>
                        <td>{{ $schedule->college->name }}</td>
                        <td>{{ $schedule->department->name }}</td>
                        <td>{{ $schedule->laboratory->name }}</td>
                        <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</td>
                        <td>{{ Carbon::parse($schedule->start_time)->format('h:i A') }}</td>
                        <td>{{ Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                        @if (Auth::user()->isAdmin())
                            <td class="text-center">
                                <div class="btn-group dropstart">
                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false"
                                        onclick="event.stopPropagation()">
                                        <i class="bi bi-three-dots"></i>
                                    </a>
                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3"
                                        onclick="event.stopPropagation()">
                                        <li>
                                            <a href="{{ route('schedule.view', $schedule->id) }}"
                                                class="dropdown-item">View</a>
                                        </li>
                                        <li><button @click="$dispatch('edit-mode',{id:{{ $schedule->id }}})"
                                                type="button" class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#verticalycentered">Edit</button></li>
                                        <li><button wire:click="delete({{ $schedule->id }})"
                                                wire:confirm="Are you sure you want to delete this schedule?"
                                                type="button" class="dropdown-item text-danger"
                                                href="#">Delete</button></li>
                                    </ul>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $schedules->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('close-import-modal', () => {
                var importModal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                importModal.hide();
            });
        });

        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-schedule-table', (event) => {
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
</div>
