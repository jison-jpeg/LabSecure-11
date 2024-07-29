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

                <div class="col-12 col-md-3">
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control"
                        placeholder="Search faculty...">
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
                    <select wire:model.live="department" name="department" class="form-select">
                        <option value="">Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
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
            <livewire:create-faculty />
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'username',
                        'displayName' => 'Username',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'email',
                        'displayName' => 'Email',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'first_name',
                        'displayName' => 'First Name',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'middle_name',
                        'displayName' => 'Middle Name',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'last_name',
                        'displayName' => 'Last Name',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'suffix',
                        'displayName' => 'Suffix',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'college.name',
                        'displayName' => 'College',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'department.name',
                        'displayName' => 'Department',
                    ])
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $key => $user)
                    <tr wire:key="{{ $user->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->first_name }}</td>
                        <td>{{ $user->middle_name }}</td>
                        <td>{{ $user->last_name }}</td>
                        <td>{{ $user->suffix }}</td>
                        <td>{{ $user->college->name ?? 'N/A' }}</td>
                        <td>{{ $user->department->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button @click="$dispatch('edit-mode',{id:{{ $user->id }}})" type="button"
                                            class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycentered">Edit</button></li>
                                    <li><button wire:click="delete({{ $user->id }})"
                                            wire:confirm="Are you sure you want to delete '{{ $user->first_name }} {{ $user->last_name }}'"
                                            type="button" class="dropdown-item text-danger" href="#">Delete
                                            {{ $user->username }}</button>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex flex-column align-items-start">
            {!! $users->links() !!}
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-faculty-table', (event) => {
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
