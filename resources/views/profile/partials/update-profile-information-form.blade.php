<div class="card flex-fill">
    <div class="card-body">
        <h5 class="card-title">Profile Information</h5>
        <p>Update your account's profile information and email address.</p>
        @if (session('status') === 'profile-updated')
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                {{ __('Saved.') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="send-verification" method="post" action="{{ route('verification.send') }}" style="display: none;">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            @method('patch')
        
            <div class="col-md-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                @if ($errors->has('first_name'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('first_name') }}
                    </div>
                @endif
            </div>
        
            <div class="col-md-3">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                @if ($errors->has('middle_name'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('middle_name') }}
                    </div>
                @endif
            </div>
        
            <div class="col-md-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                @if ($errors->has('last_name'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('last_name') }}
                    </div>
                @endif
            </div>
        
            <div class="col-md-3">
                <label for="suffix" class="form-label">Suffix</label>
                <input type="text" class="form-control" id="suffix" name="suffix" value="{{ old('suffix', $user->suffix) }}">
                @if ($errors->has('suffix'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('suffix') }}
                    </div>
                @endif
            </div>
        
            <div class="col-12">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @if ($errors->has('email'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>
        
            <div class="col-12 text-start">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
        
    </div>
</div>
