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
                        placeholder="Search logs...">
                </div>

                <div class="col-12 col-md-2">
                    <select wire:model.live="action" name="action" class="form-select">
                        <option value="">Action Type</option>
                        <option value="check_in">Check In</option>
                        <option value="create">Create</option>
                        <option value="update">Update</option>
                        <option value="delete">Delete</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'time',
                        'displayName' => 'Time',
                    ])
                    <th scope="col">Date</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'user_id',
                        'displayName' => 'User',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'action',
                        'displayName' => 'Action',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'model',
                        'displayName' => 'Model',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'details',
                        'displayName' => 'Details',
                    ])
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $key => $log)
                    <tr wire:key="{{ $log->id }}">
                        <th scope="row">
                            {{ $logs->firstItem() + $key }}
                        </th>
                        <td>{{ $log->created_at->format('m:i A') }}</td>
                        <td>{{ $log->created_at->format('F j, Y') }}</td>
                        <td>{{ $log->user->full_name }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->model }}</td>
                        <td>{{ $log->readableDetails }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
</div>
