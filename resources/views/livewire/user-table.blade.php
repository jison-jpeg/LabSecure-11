<div>
    {{-- Search --}}
    <div class="row mb-3 g-3">
        <div class="col-5 col-md-4">
            <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control" placeholder="Search users...">
        </div>
        <div class="col-5 col-md-2">
            <select wire:model.live="role" name="role" class="form-select">
                <option value="">User Type</option>
                <option value="1">Admin</option>
                <option value="2">Instructor</option>
                <option value="3">Student</option>
            </select>
        </div>
        <div class="col-2">
            <button class="btn btn-success">Hello</button>
        </div>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Username</th>
                <th scope="col">Email</th>
                <th scope="col">First Name</th>
                <th scope="col">Middle Name</th>
                <th scope="col">Last Name</th>
                <th scope="col">Suffix</th>
                <th scope="col">Role</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $key => $user)
                <tr wire:key="{{$user->id}}">
                    <th scope="row">{{ $key + 1 }}</th>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->first_name }}</td>
                    <td>{{ $user->middle_name }}</td>
                    <td>{{ $user->last_name }}</td>
                    <td>{{ $user->suffix }}</td>
                    <td><span class="badge rounded-pill {{
                        $user->role->name == 'admin' ? 'bg-danger' : ($user->role->name == 'instructor' ? 'bg-success' : 'bg-secondary')}}">{{ $user->role->name }}</span></td>
                    <td>
                        <a class="btn btn-primary btn-sm">View</a>
                        <a class="btn btn-warning btn-sm">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex flex-column align-items-start">
        <div class="text-center text-muted mb-3">
            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} out of {{ $users->total() }} results
        </div>
        {!! $users->links() !!}
    </div>
</div>

