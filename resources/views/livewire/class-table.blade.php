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
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control" placeholder="Search sections...">
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
                    @include('livewire.includes.table-sortable-th', ['name' => 'name', 'displayName' => 'Section Name'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'year_level', 'displayName' => 'Year Level'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'semester', 'displayName' => 'Semester'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'department_id', 'displayName' => 'Department'])
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sections as $key => $section)
                    <tr wire:key="{{ $section->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $section->name }}</td>
                        <td>{{ $section->year_level }}</td>
                        <td>{{ $section->semester }}</td>
                        <td>{{ $section->department->name }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button type="button" class="dropdown-item" href="#">Edit</button></li>
                                    <li><button wire:click="delete({{ $section->id }})" type="button" class="dropdown-item text-danger">Delete {{ $section->name }}</button></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex flex-column align-items-start">
            {!! $sections->links() !!}
        </div>
    </div>
</div>
