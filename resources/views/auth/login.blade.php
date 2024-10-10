<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="card mb-3">
        <div class="card-body">
            <div class="pt-4 pb-2">
                <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                <p class="text-center small">Enter your username & password to login</p>
            </div>

            <form class="row g-3 needs-validation" method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <div class="col-12">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="username" name="username" class="form-control @error('username') is-invalid @enderror" id="username" value="{{ old('username') }}" required autofocus autocomplete="username">
                        @error('username')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @else
                            <div class="invalid-feedback">
                                Please enter your username.
                            </div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-12">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" required autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @else
                        <div class="invalid-feedback">
                            Please enter your password.
                        </div>
                    @enderror
                </div>
            
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">{{ __('Remember me') }}</label>
                    </div>
                </div>
            
                <div class="col-12">
                    @if (Route::has('password.request'))
                        <a class="small mb-0" href="{{ route('password.request') }}">
                            Forgot your password?
                        </a>
                    @endif
                </div>
                
                <div class="col-12">
                    <button class="btn btn-primary w-100 text-white" type="submit">Login</button>
                </div>
            
                <div class="col-12">
                    <a href="{{ route('google.login') }}" class="btn btn-outline-primary w-100">Login with Google</a>
                </div>
                
                <div class="col-12">
                    <p class="small mb-0">Don't have an account? <a href="{{ route('register') }}">Create an account</a></p>
                </div>
            </form>
            
        </div>
    </div>
</x-guest-layout>


