<x-guest-layout>
    <div class="card mb-3">
        <div class="card-body">
            <div class="pt-4 pb-2">
                <h5 class="card-title text-center pb-0 fs-4">Reset Your Password</h5>
                <p class="text-center small">Please enter your email address and your new password to reset your password.</p>
            </div>

            <form class="row g-3 needs-validation" method="POST" action="{{ route('password.store') }}" novalidate>
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div class="col-12">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <div class="input-group has-validation">
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Password -->
                <div class="col-12 mt-4">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <div class="input-group has-validation">
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="col-12 mt-4">
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <div class="input-group has-validation">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required autocomplete="new-password">
                        @error('password_confirmation')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary w-100">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
