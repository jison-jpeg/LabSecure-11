<div>
    <div class="row mb-4 mt-4">
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
        <table class="table table-hover">
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
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($departments as $key => $department)
                    <tr wire:key="{{ $department->id }}" onclick="window.location='{{ route('department.view', ['department' => $department->id]) }}';" style="cursor: pointer;">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $department->name }}</td>
                        <td>{{ $department->college->name }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3" onclick="event.stopPropagation()">
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
        {{ $departments->links() }}
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
