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
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control"
                        placeholder="Search schedules...">
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <livewire:create-schedule />
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'schedule.schedule_code',
                        'displayName' => 'Code',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'section.name',
                        'displayName' => 'Section',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'subject.name',
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
                    <th scope="col" class="text-center text-dark fw-semibold">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schedules as $key => $schedule)
                    <tr wire:key="{{ $schedule->id }}"
                        onclick="window.location='{{ route('schedule.view', $schedule->id) }}';"
                        style="cursor: pointer;">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $schedule->schedule_code }}</td>
                        <td>{{ $schedule->section->name }}</td>
                        <td>{{ $schedule->subject->code }}</td>
                        <td>{{ $schedule->subject->name }}</td>
                        <td>{{ $schedule->instructor->full_name }}</td>
                        <td>{{ $schedule->college->name }}</td>
                        <td>{{ $schedule->department->name }}</td>
                        <td>{{ $schedule->laboratory->name }}</td>
                        <td>{{ implode(', ', $schedule->getShortenedDaysOfWeek()) }}</td>
                        <td>{{ Carbon::parse($schedule->start_time)->format('h:i A') }}</td>
                        <td>{{ Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false"
                                    onclick="event.stopPropagation()">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3"
                                    onclick="event.stopPropagation()">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button @click="$dispatch('edit-mode',{id:{{ $schedule->id }}})" type="button"
                                            class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycentered">Edit</button></li>
                                    <li><button wire:click="delete({{ $schedule->id }})"
                                            wire:confirm="Are you sure you want to delete this schedule?" type="button"
                                            class="dropdown-item text-danger" href="#">Delete</button>
                                </ul>
                            </div>
                        </td>
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
