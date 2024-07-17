<div class="card">
    <div class="card-body">
        <h5 class="card-title">Update Password</h5>
        <p>Ensure your account is using a long, random password to stay secure.</p>
        @if (session('status') === 'password-updated')
            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                {{ __('Saved.') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <form method="post" action="{{ route('password.update') }}" class="row g-3">
            @csrf
            @method('put')

            <div class="col-md-4">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="current_password" name="current_password"
                    autocomplete="current-password" required>
                @if ($errors->updatePassword->has('current_password'))
                    <div class="text-danger mt-2">
                        {{ $errors->updatePassword->first('current_password') }}
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password"
                    required>
                @if ($errors->updatePassword->has('password'))
                    <div class="text-danger mt-2">
                        {{ $errors->updatePassword->first('password') }}
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                    autocomplete="new-password" required>
                @if ($errors->updatePassword->has('password_confirmation'))
                    <div class="text-danger mt-2">
                        {{ $errors->updatePassword->first('password_confirmation') }}
                    </div>
                @endif
            </div>

            <div class="text-start">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
