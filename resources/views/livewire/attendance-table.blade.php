@php
    use Carbon\Carbon;
@endphp

<div>
    <div class="row mb-4">
        <!-- Filter options and controls remain unchanged -->
        <div class="col-md-10">
            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Option</h6>
                    </li>
                    <li><a wire:click.prevent="import" href="#" class="dropdown-item">Import</a></li>
                    <li class="dropdown-submenu position-relative">
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
                    <li><a class="dropdown-item text-danger" href="#">Delete Selected</a></li>
                </ul>
            </div>

            <!-- Per Page and Filter Controls -->
            <div class="row g-1">
                <div class="col-md-1 col-sm-2">
                    <select wire:model.live="perPage" name="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <!-- Search and Filter Controls -->
                <div class="col-12 col-md-3 col-sm-10">
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control"
                        placeholder="Search users...">
                </div>

                <div class="col-12 col-md-2 col-sm-6">
                    <select wire:model.live="selectedSubject" name="selectedSubject" class="form-select">
                        <option value="">All Subjects</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 col-sm-6">
                    <select wire:model.live="selectedSection" name="selectedSection" class="form-select">
                        <option value="">All Sections</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2 col-sm-6">
                    <select wire:model.live="status" name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                        <option value="Excused">Excused</option>
                        <option value="Incomplete">Incomplete</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 col-sm-6">
                    <input type="month" wire:model.live="selectedMonth" name="selectedMonth" class="form-control">
                </div>
            </div>
        </div>

        <div class="col-12 col-md-2">
            <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear Filters</button>
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
                    ]) @include('livewire.includes.table-sortable-th', [
                        'name' => 'user.name',
                        'displayName' => 'User',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'subject.name',
                        'displayName' => 'Subject',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'schedule.schedule_code',
                        'displayName' => 'Schedule Code',
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
                    ]) @include('livewire.includes.table-sortable-th', [
                        'name' => 'status',
                        'displayName' => 'Status',
                    ])
                    <th scope="col" class="text-dark fw-semibold">Remarks</th>
                    @if (Auth::user()->isAdmin())
                    <th scope="col" class="text-center text-dark fw-semibold">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr wire:key="{{ $attendance->id }}"
                        onclick="window.location='{{ route('attendance.user.view', ['user' => $attendance->user->id]) }}';"
                        style="cursor: pointer;">
                        <th scope="row">{{ $loop->iteration }}</th>
                        <td>{{ Carbon::parse($attendance->date)->format('m/d/Y') }}</td>
                        <td>{{ $attendance->user->username }}</td>
                        <td>{{ $attendance->user->full_name }}</td>
                        <td>{{ $attendance->schedule->subject->name }}</td>
                        <td>{{ $attendance->schedule->schedule_code }}</td>
                        <td>{{ $attendance->schedule->section->name }}</td>
                        <td>{{ optional($attendance->sessions->first())->time_in ? Carbon::parse($attendance->sessions->first()->time_in)->format('h:i A') : '-' }}
                        </td>
                        <td>{{ optional($attendance->sessions->last())->time_out ? Carbon::parse($attendance->sessions->last()->time_out)->format('h:i A') : '-' }}
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
                        @if (Auth::user()->isAdmin())
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3" onclick="event.stopPropagation()">
                                    <li><button type="button" class="dropdown-item">View</button></li>
                                    <li><button @click="$dispatch('edit-mode',{id:{{ $attendance->id }}})"
                                            type="button" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycentered">Edit</button></li>
                                    <li><button wire:click="delete({{ $attendance->id }})"
                                            wire:confirm="Are you sure you want to delete this record?" type="button"
                                            class="dropdown-item text-danger">Delete</button></li>
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
