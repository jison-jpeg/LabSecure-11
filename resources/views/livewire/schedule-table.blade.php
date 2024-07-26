<div>
    <div class="row mb-4">
        <div class="col-md-10">

            {{-- Per Page --}}
            <div class="row g-1">
                <div class="col-md-2">
                    <select wire:model.live="perPage" name="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
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
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'subject.name',
                        'displayName' => 'Subject',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'instructor.full_name',
                        'displayName' => 'Instructor',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'section.name',
                        'displayName' => 'Section',
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
                        'displayName' => 'Days of Week',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'start_time',
                        'displayName' => 'Start Time',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'end_time',
                        'displayName' => 'End Time',
                    ])
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schedules as $key => $schedule)
                    <tr wire:key="{{ $schedule->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $schedule->subject->name }}</td>
                        <td>{{ $schedule->instructor->full_name }}</td>
                        <td>{{ $schedule->section->name }}</td>
                        <td>{{ $schedule->college->name }}</td>
                        <td>{{ $schedule->department->name }}</td>
                        <td>{{ $schedule->laboratory->name }}</td>
                        <td>{{ implode(', ', json_decode($schedule->days_of_week)) }}</td>
                        <td>{{ $schedule->start_time }}</td>
                        <td>{{ $schedule->end_time }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
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
        <div class="d-flex flex-column align-items-start">
            {!! $schedules->links() !!}
        </div>
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
    </script>
</div>
