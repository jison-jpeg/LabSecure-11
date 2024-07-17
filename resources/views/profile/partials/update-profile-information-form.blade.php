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

        <form method="post" action="{{ route('profile.update') }}" class="row g-3">
            @csrf
            @method('patch')

            <div class="col-md-4">
                <label for="name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="name" name="name"
                    value="{{ old('name', $user->name) }}" required>
                @if ($errors->has('name'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('name') }}
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                @if ($errors->has('middle_name'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('middle_name') }}
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                @if ($errors->has('last_name'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('last_name') }}
                    </div>
                @endif
            </div>

            <div class="col-12">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="{{ old('email', $user->email) }}" required>
                @if ($errors->has('email'))
                    <div class="text-danger mt-2">
                        {{ $errors->first('email') }}
                    </div>
                @endif

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                            {{ __('Your email address is unverified.') }}
                            <button form="send-verification"
                                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="col-12 text-start">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
