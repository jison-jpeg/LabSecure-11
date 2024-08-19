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
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', ['name' => 'subject.name', 'displayName' => 'Subject'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'instructor.full_name', 'displayName' => 'Instructor'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'college.name', 'displayName' => 'College'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'department.name', 'displayName' => 'Department'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'section.name', 'displayName' => 'Section'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'laboratory.name', 'displayName' => 'Laboratory'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'days_of_week', 'displayName' => 'Days of Week'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'start_time', 'displayName' => 'Start Time'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'end_time', 'displayName' => 'End Time'])
                </tr>
            </thead>
            <tbody>
                @foreach ($schedules as $key => $schedule)
                    <tr wire:key="{{ $schedule->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $schedule->subject->name }}</td>
                        <td>{{ $schedule->instructor->full_name }}</td>
                        <td>{{ $schedule->college->name }}</td>
                        <td>{{ $schedule->department->name }}</td>
                        <td>{{ $schedule->section->name }}</td>
                        <td>{{ $schedule->laboratory->name }}</td>
                        <td>{{ implode(', ', json_decode($schedule->days_of_week, true)) }}</td>
                        <td>{{ $schedule->start_time }}</td>
                        <td>{{ $schedule->end_time }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex flex-column align-items-start">
        {!! $schedules->links() !!}
    </div>
</div>
