<!-- resources/views/livewire/student-per-section-table.blade.php -->
<div>
    <div class="row mb-4">
        <div class="col-md-10">
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

                <div class="col-12 col-md-4">
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control"
                        placeholder="Search students...">
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
                    @include('livewire.includes.table-sortable-th', ['name' => 'username', 'displayName' => 'Username'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'first_name', 'displayName' => 'First Name'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'last_name', 'displayName' => 'Last Name'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'email', 'displayName' => 'Email'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'created_at', 'displayName' => 'Created At'])
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $key => $student)
                    <tr wire:key="{{ $student->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $student->username }}</td>
                        <td>{{ $student->first_name }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $students->links()}}
    </div>
</div>
