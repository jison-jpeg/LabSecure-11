<div>
    <div class="row mb-4">
        <div class="col-md-10">

            {{-- perpage --}}
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
                        placeholder="Search users...">
                </div>

                <div class="col-12 col-md-2">
                    <select wire:model.live="role" name="role" class="form-select">
                        <option value="">User Type</option>
                        <option value="1">Admin</option>
                        <option value="2">Instructor</option>
                        <option value="3">Student</option>
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#verticalycentered">
                Create User
              </button>
            {{-- <x-modal :modalTitle="$title" :eventName="$event">
            </x-modal> --}}

            @livewire('create-user')

        </div>
    </div>

    <div class="">
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
                        'name' => 'role_id',
                        'displayName' => 'Role',
                    ])
                    {{-- @include('livewire.includes.table-sortable-th', [
                        'name' => 'created_at',
                        'displayName' => 'Created At',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'updated_at',
                        'displayName' => 'Updated At',
                    ]) --}}
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
                        <td><span
                                class="badge rounded-pill {{ $user->role->name == 'admin' ? 'bg-danger' : ($user->role->name == 'instructor' ? 'bg-success' : 'bg-secondary') }}">{{ $user->role->name }}</span>
                        </td>
                        {{-- <td>{{ $user->created_at->diffForHumans() }}</td> --}}
                        {{-- <td>{{ $user->updated_at->diffForHumans() }}</td> --}}
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button class="dropdown-item" href="#">View</button></li>
                                    <li><button class="dropdown-item">Edit</button></li>
                                    {{-- delete user --}}
                                    <li><button wire:click="delete({{$user->id}})" class="dropdown-item text-danger" href="#">Delete User {{ $user->id }}</button>
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
</div>
