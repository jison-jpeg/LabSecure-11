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
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $key => $log)
                    <tr wire:key="{{ $log->id }}">
                        <th scope="row">
                            {{ $logs->firstItem() + $key }}
                        </th>
                        <td>{{ $log->user->full_name }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->model }}</td>
                        <td>{{ $log->readableDetails  }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button wire:click="delete({{ $log->id }})"
                                            wire:confirm="Are you sure you want to delete this log?"
                                            type="button" class="dropdown-item text-danger" href="#">Delete</button>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex flex-column align-items-start">
        {!! $logs->links() !!}
    </div>
</div>
