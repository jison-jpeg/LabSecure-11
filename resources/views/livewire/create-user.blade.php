<div>
    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#verticalycentered">
        Create User
    </button>
    <div wire:ignore.self class="modal fade" id="verticalycentered" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $formTitle }}</h5>
                    <button wire:click="close" type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save" class="row g-3 needs-validation" novalidate>
                        <div class="col-md-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input wire:model.lazy="first_name" type="text"
                                class="form-control @error('first_name') is-invalid @enderror" name="first_name">
                            @error('first_name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input wire:model.lazy="middle_name" type="text"
                                class="form-control @error('middle_name') is-invalid @enderror" name="middle_name">
                            @error('middle_name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input wire:model.lazy="last_name" type="text"
                                class="form-control @error('last_name') is-invalid @enderror" name="last_name">
                            @error('last_name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-1">
                            <label for="suffix" class="form-label">Suffix</label>
                            <input wire:model.lazy="suffix" type="text"
                                class="form-control @error('suffix') is-invalid @enderror" name="suffix">
                            @error('suffix')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select wire:model.lazy="status" class="form-select @error('status') is-invalid @enderror"
                                name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
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
                        <div @class([
                            'col-md-2' => $this->isRoleStudent(),
                            'col-md-6' => $this->isRoleDean(),
                            'col-md-4' => !$this->isRoleStudent() && !$this->isRoleDean(),
                        ])>
                            <label for="role" class="form-label">Role</label>
                            <select wire:model.lazy="role_id" id="role" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">Select Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                            @error('role_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- College Field with Dynamic Column Size for Dean -->
                        @if (!$this->isRoleAdmin())
                            <div @class([
                                'col-md-6' => $this->isRoleDean(),
                                'col-md-4' => !$this->isRoleDean(),
                            ])>
                                <label for="college" class="form-label">College</label>
                                <select wire:model.lazy="selectedCollege" id="college" class="form-select @error('selectedCollege') is-invalid @enderror" required>
                                    <option value="">Select College</option>
                                    @foreach ($colleges as $college)
                                        <option value="{{ $college->id }}">{{ $college->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedCollege')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif

                        <!-- Department Field -->
                        @if (!$this->isRoleAdmin() && ($this->isRoleChairperson() || $this->isRoleInstructor() || $this->isRoleStudent()))
                            <div class="col-md-4">
                                <label for="department" class="form-label">Department</label>
                                <select wire:model.lazy="selectedDepartment" id="department" class="form-select @error('selectedDepartment') is-invalid @enderror" required>
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedDepartment')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif

                        <!-- Section Field (Only for Students) with Dynamic Column Size -->
                        @if ($this->isRoleStudent())
                            <div @class([
                                'col-md-2' => $this->isRoleStudent(),
                                'col-md-4' => !$this->isRoleStudent(),
                            ])>
                                <label for="section" class="form-label">Section</label>
                                <select wire:model.lazy="selectedSection" id="section" class="form-select @error('selectedSection') is-invalid @enderror" required>
                                    <option value="">Select Section</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedSection')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endif
                </div>
                <div class="modal-footer">
                    @if ($editForm)
                        <button wire:click="close" type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button>
                        <button wire:click="update" type="button" class="btn btn-primary">Save changes</button>
                    @else
                        <button wire:click="close" type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button>
                        <button wire:click="save" type="button" class="btn btn-primary">Create user</button>
                    @endif
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
