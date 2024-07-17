<x-guest-layout>
    <div class="card mb-3">
        <div class="card-body">
            <div class="pt-4 pb-2">
                <h5 class="card-title text-center pb-0 fs-4">Create Your Account</h5>
                <p class="text-center small">Enter your details to register</p>
            </div>

            <form class="row g-3 needs-validation" method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <!-- Name -->
                <div class="col-12">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @else
                        <div class="invalid-feedback">
                            Please enter your name.
                        </div>
                    @enderror
                </div>

                <!-- Email Address -->
                <div class="col-12">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email') }}" required autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @else
                            <div class="invalid-feedback">
                                Please enter your email.
                            </div>
                        @enderror
                    </div>
                </div>

                {{-- Dropdown --}}
                <div class="col-12">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" name="role" id="role" required>
                        <option selected disabled value="">Select Role</option>
                        <option value="sme">SME</option>
                        <option value="genpub">General Public</option>
                    </select>
                    <div class="invalid-feedback">
                        Please select a role.
                    </div>
                </div>

                <!-- Password -->
                <div class="col-12">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" required autocomplete="new-password">
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

                <!-- Confirm Password -->
                <div class="col-12">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @else
                        <div class="invalid-feedback">
                            Please confirm your password.
                        </div>
                    @enderror
                </div>

                <div class="col-12">
                    <button class="btn btn-warning text-white w-100" type="submit">Register</button>
                </div>

                <div class="col-12">
                    <p class="small mb-0">Already registered? <a href="{{ route('login') }}">Login</a></p>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
