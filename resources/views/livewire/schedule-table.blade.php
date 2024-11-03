@php
    use Carbon\Carbon;
@endphp

<div>
    <div class="row mb-4">
        <div class="col-md-10">
            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Options</h6>
                    </li>
                    <li><a wire:click.prevent="import" href="#" class="dropdown-item">Import</a></li>
                    <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteSelected">Delete Selected</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a wire:click.prevent="exportAs('csv')" href="#" class="dropdown-item">Export as CSV</a></li>
                    <li><a wire:click.prevent="exportAs('excel')" href="#" class="dropdown-item">Export as Excel</a></li>
                    {{-- Add PDF export if implemented --}}
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
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control" placeholder="Search schedules...">
                </div>

                {{-- Conditionally Display College Filter --}}
                @if(auth()->user()->isAdmin())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="college" name="college" class="form-select">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif(auth()->user()->isDean())
                    {{-- For Dean, the college is fixed and hidden, so no select is needed --}}
                    <input type="hidden" wire:model="college" value="{{ auth()->user()->college_id }}">
                @endif

                {{-- Conditionally Display Department Filter --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isDean())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="department" name="department" class="form-select" @if(auth()->user()->isAdmin() && !$college) disabled @endif>
                            <option value="">Select Department</option>
                            @forelse ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @empty
                                <option value="" disabled>No departments available</option>
                            @endforelse
                        </select>
                    </div>
                @endif

                {{-- New Year Level Filter --}}
                <div class="col-12 col-md-2">
                    <select wire:model.live="yearLevel" name="yearLevel" class="form-select">
                        <option value="">All Year Levels</option>
                        @foreach ($availableYearLevels as $level)
                            <option value="{{ $level }}">{{ $level }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Conditionally Display Section Filter --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isDean() || auth()->user()->isChairperson() || auth()->user()->isInstructor())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="section" name="section" class="form-select" @if(!$yearLevel) disabled @endif>
                            <option value="">Select Section</option>
                            @forelse ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @empty
                                <option value="" disabled>No sections available</option>
                            @endforelse
                        </select>
                    </div>
                @endif

                {{-- Clear Filters Button --}}
                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear Filters</button>
                </div>
            </div>
        </div>
        @if(Auth::user()->isAdmin())
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
                        <th scope="row">{{ ($schedules->currentPage() - 1) * $schedules->perPage() + $key + 1 }}</th>
                        <td>{{ $schedule->schedule_code }}</td>
                        <td>{{ $schedule->section->name }}</td>
                        <td>{{ $schedule->section->year_level ?? 'N/A' }}</td> <!-- New Year Level Data Column (optional) -->
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
                                        <li><button type="button" class="dropdown-item" href="#">View</button></li>
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
