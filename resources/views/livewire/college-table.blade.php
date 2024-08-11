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
                        placeholder="Search colleges...">
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear Filters</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <livewire:create-college />
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', ['name' => 'name', 'displayName' => 'Name'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'created_at', 'displayName' => 'Created At'])
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($colleges as $key => $college)
                    <tr wire:key="{{ $college->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $college->name }}</td>
                        <td>{{ $college->created_at->diffForHumans() }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button @click="$dispatch('edit-mode',{id:{{ $college->id }}})" type="button"
                                            class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycentered">Edit</button></li>
                                    <li><button wire:click="delete({{ $college->id }})"
                                            wire:confirm="Are you sure you want to delete '{{ $college->name }}'"
                                            type="button" class="dropdown-item text-danger" href="#">Delete</button>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex flex-column align-items-start">
            {!! $colleges->links() !!}
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-college-table', (event) => {
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