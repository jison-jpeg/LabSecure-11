<x-guest-layout>
    <!-- Session Status -->
    {{-- <x-auth-session-status class="mb-4" :status="session('status')" /> --}}

    <div class="card mb-3">
        <div class="card-body">
            <div class="pt-4 pb-2">
                <h5 class="card-title text-center pb-0 fs-4">Forgot your password?</h5>
                <p class="text-center small">No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</p>
            </div>
    
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
    
            <form class="row g-3 needs-validation" method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                <div class="col-12">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group has-validation">
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-12 mt-4">
                    <button class="btn btn-primary w-100" type="submit">Submit</button>
                </div>
            
                <div class="col-12">
                    <p class="small mb-0">Already have an account? <a href="{{ route('login') }}">Login to your account.</a></p>
                </div>
            </form>
        </div>
    </div>
    
</x-guest-layout>

{{-- <x-guest-layout>
    <div class="mb-4 text-muted">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
</x-guest-layout> --}}


