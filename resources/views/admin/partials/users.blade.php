<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">First Name</th>
            <th scope="col">Middle Name</th>
            <th scope="col">Last Name</th>
            <th scope="col">Suffix</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $key => $user)
            <tr>
                <th scope="row">{{ $key + 1 }}</th>
                <td>{{ $user->first_name }}</td>
                <td>{{ $user->middle_name }}</td>
                <td>{{ $user->last_name }}</td>
                <td>{{ $user->suffix }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center">
    {!! $users->links() !!}
</div>
