<div>
    <div class="row mb-4 mt-4">
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
                        placeholder="Search departments...">
                </div>

                <div class="col-12 col-md-2">
                    <select wire:model.live="college" name="college" class="form-select">
                        <option value="">Select College</option>
                        @foreach ($colleges as $college)
                            <option value="{{ $college->id }}">{{ $college->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <livewire:create-department />
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'name',
                        'displayName' => 'Name',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'college.name',
                        'displayName' => 'College',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'created_at',
                        'displayName' => 'Created At',
                    ])
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departments as $key => $department)
                    <tr wire:key="{{ $department->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->college->name }}</td>
                        <td>{{ $department->created_at->diffForHumans() }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button @click="$dispatch('edit-department',{id:{{ $department->id }}})"
                                            type="button" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycentereddepartment">Edit</button></li>
                                    <li><button wire:click="delete({{ $department->id }})"
                                            wire:confirm="Are you sure you want to delete '{{ $department->name }}'"
                                            type="button" class="dropdown-item text-danger"
                                            href="#">Delete</button>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $departments->links('pagination::bootstrap-5') }}
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-department-table', (event) => {
                var myModalEl = document.querySelector('#verticalycentereddepartment')
                var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)

                setTimeout(() => {
                    modal.hide();
                    @this.dispatch('reset-modal');
                });
            })

            var mymodal = document.getElementById('verticalycentereddepartment')
            mymodal.addEventListener('hidden.bs.modal', (event) => {
                @this.dispatch('reset-modal');
            })
        })
    </script>
</div>
