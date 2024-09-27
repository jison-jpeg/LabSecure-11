<div>
    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#verticalycentered">
        Create User
    </button>
    <div wire:ignore.self class="modal fade" id="verticalycentered" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $formTitle }}</h5>
                    <button wire:click="close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save" class="row g-3 needs-validation" novalidate>
                        <div class="col-md-4">
                            <label for="first_name" class="form-label">First Name</label>
                            <input wire:model.lazy="first_name" type="text"
                                class="form-control @error('first_name') is-invalid @enderror" name="first_name">
                            @error('first_name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input wire:model="middle_name" type="text"
                                class="form-control @error('middle_name') is-invalid @enderror" name="middle_name">
                            @error('middle_name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input wire:model.lazy="last_name" type="text"
                                class="form-control @error('last_name') is-invalid @enderror" name="last_name">
                            @error('last_name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="username" class="form-label">Username</label>
                            <input wire:model.lazy="username" type="text"
                                class="form-control @error('username') is-invalid @enderror" name="username">
                            @error('username')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email</label>
                            <input wire:model.lazy="email" type="email"
                                class="form-control @error('email') is-invalid @enderror" name="email">
                            @error('email')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label">Password</label>
                            <input wire:model.lazy="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password">
                            @error('password')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="role_id" class="form-label">Role</label>
                            <select wire:model.lazy="role_id" class="form-select @error('role_id') is-invalid @enderror"
                                name="role_id">
                                <option value="">Select Role</option>
                                <option value="1">Admin</option>
                                <option value="2">Instructor</option>
                                <option value="3">Student</option>
                                <option value="4">Dean</option>
                                <option value="5">Chairperson</option>
                            </select>
                            @error('role_id')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        @if ($editForm)
                        <button wire:click="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button wire:click="update" type="button" class="btn btn-primary">Save changes</button>
                        @else
                        <button wire:click="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button wire:click="save" type="button" class="btn btn-primary">Create user</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
