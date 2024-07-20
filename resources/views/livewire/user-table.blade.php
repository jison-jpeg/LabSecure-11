<div>
    {{-- Search --}}
    <div class="row mb-5 g-1 align-items-center">
        <div class="col-12 col-md-1">
            <select wire:model.live="perPage" name="perPage" class="form-select">
                <option value="2">2</option>
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="20">20</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        
        <form class="col-12 col-md-9 row g-1 align-items-center">
            <div class="col-12 col-md-4">
                <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control"
                    placeholder="Search users...">
            </div>
            <div class="col-12 col-md-3">
                <select wire:model.live="role" name="role" class="form-select">
                    <option value="">User Type</option>
                    <option value="1">Admin</option>
                    <option value="2">Instructor</option>
                    <option value="3">Student</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button class="btn btn-secondary w-100" type="reset" wire:click="clear">Clear Filters</button>
            </div>
        </form>
        
        <div class="col-12 col-md-2 ms-md-auto text-md-end">
            <button class="btn btn-primary w-100" wire:click="addUser">Add User</button>
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
                        'name' => 'role_id',
                        'displayName' => 'Role',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'created_at',
                        'displayName' => 'Created At',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'updated_at',
                        'displayName' => 'Updated At',
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
                        <td><span
                                class="badge rounded-pill {{ $user->role->name == 'admin' ? 'bg-danger' : ($user->role->name == 'instructor' ? 'bg-success' : 'bg-secondary') }}">{{ $user->role->name }}</span>
                        </td>
                        <td>{{ $user->created_at->diffForHumans() }}</td>
                        <td>{{ $user->updated_at->diffForHumans() }}</td>
                        <td class="text-center">
                            <div class="action">
                                <a href="#" id="dropdownMenuLink{{ $user->id }}" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end"
                                    aria-labelledby="dropdownMenuLink{{ $user->id }}">
                                    <li class="dropdown-header text-start">
                                        <h6>Action</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#">View</a></li>
                                    <li><a class="dropdown-item" href="#">Edit</a></li>
                                    <li><a wire:click="delete({{ $user->id }})" class="dropdown-item text-danger"
                                            href="#">Delete</a></li>
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
