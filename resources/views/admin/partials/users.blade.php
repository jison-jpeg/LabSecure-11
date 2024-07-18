<form id="delete-form" method="POST" action="{{ route('users.bulkDelete') }}">
    @csrf
    @method('DELETE')
    <table class="table">
        <thead>
            <tr>
                <th scope="col"><input type="checkbox" id="select-all"></th>
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
                <tr>
                    <td><input type="checkbox" class="user-checkbox" name="user_ids[]" value="{{ $user->id }}"></td>
                    <th scope="row">{{ $key + 1 }}</th>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->first_name }}</td>
                    <td>{{ $user->middle_name }}</td>
                    <td>{{ $user->last_name }}</td>
                    <td>{{ $user->suffix }}</td>
                    <td><span class="badge rounded-pill bg-primary">{{ $user->role->name }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-start">
        {!! $users->links() !!}
    </div>
    <button id="delete-selected" class="btn btn-danger" disabled>Delete Selected</button>
</form>
