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
                        placeholder="Search subjects...">
                </div>

                @if (Auth::user()->role->name === 'admin')
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
                @endif

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        @if (Auth::user()->role->name === 'admin')
        <div class="col-12 col-md-2">
            <livewire:create-subject />
        </div>
        @endif
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
                        'name' => 'code',
                        'displayName' => 'Code',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'description',
                        'displayName' => 'Description',
                    ])
                    {{-- @include('livewire.includes.table-sortable-th', [
                        'name' => 'college.name',
                        'displayName' => 'College',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'department.name',
                        'displayName' => 'Department',
                    ]) --}}
                    @if (Auth::user()->role->name === 'admin')
                    <th scope="col" class="text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($subjects as $key => $subject)
                    <tr wire:key="{{ $subject->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $subject->name }}</td>
                        <td>{{ $subject->code }}</td>
                        <td>{{ $subject->description ?? 'N/A' }}</td>
                        {{-- <td>{{ $subject->college->name ?? 'N/A' }}</td>
                        <td>{{ $subject->department->name ?? 'N/A' }}</td> --}}
                        @if (Auth::user()->role->name === 'admin')
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button @click="$dispatch('edit-mode',{id:{{ $subject->id }}})" type="button"
                                            class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycentered">Edit</button></li>
                                    <li><button wire:click="delete({{ $subject->id }})"
                                            wire:confirm="Are you sure you want to delete '{{ $subject->name }}'"
                                            type="button" class="dropdown-item text-danger" href="#">Delete
                                            {{ $subject->name }}</button>
                                </ul>
                            </div>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex flex-column align-items-start">
            {!! $subjects->links() !!}
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-subject-table', (event) => {
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
