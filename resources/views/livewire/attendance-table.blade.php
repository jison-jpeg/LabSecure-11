@php
use Carbon\Carbon;
@endphp

<div>
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
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control"
                        placeholder="Search by user name...">
                </div>

                <div class="col-12 col-md-2">
                    <select wire:model.live="status" name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                        <option value="Excused">Excused</option>
                        <option value="Incomplete">Incomplete</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            {{-- Optional button for adding attendance records --}}
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'user.name',
                        'displayName' => 'User',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'schedule.name',
                        'displayName' => 'Schedule',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'date',
                        'displayName' => 'Date',
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
                        'name' => 'status',
                        'displayName' => 'Status',
                    ])
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $key => $attendance)
                    <tr wire:key="{{ $attendance->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $attendance->user->first_name }}</td>
                        <td>{{ $attendance->schedule }}</td>
                        <td>{{ $attendance->date }}</td>
                        <td>{{ Carbon::parse($attendance->time_in)->format('h:i A') }}</td>
                        <td>{{ Carbon::parse($attendance->time_out)->format('h:i A') }}</td>

                        <td>{{ $attendance->status }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button @click="$dispatch('edit-mode',{id:{{ $attendance->id }}})" type="button"
                                            class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycentered">Edit</button></li>
                                    <li><button wire:click="delete({{ $attendance->id }})"
                                            wire:confirm="Are you sure you want to delete this record?"
                                            type="button" class="dropdown-item text-danger" href="#">Delete</button>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex flex-column align-items-start">
            {!! $attendances->links() !!}
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
    </script>
</div>
