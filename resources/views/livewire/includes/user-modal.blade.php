{{-- Add User --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row g-3 needs-validation" novalidate>
                <div class="col-md-4">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" wire:model="first_name" required>
                    <div class="invalid-feedback">
                        Please enter a valid first name.
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" wire:model="middle_name">
                    <div class="invalid-feedback">
                        Please enter a valid middle name.
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" wire:model="last_name" required>
                    <div class="invalid-feedback">
                        Please enter a valid last name.
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">Institutional Email</label>
                    <input type="email" class="form-control" id="email" wire:model="email" required>
                    <div class="invalid-feedback">
                        Please provide a unique and valid institutional email address.
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" wire:model="username" required>
                    <div class="invalid-feedback">
                        Please provide a unique and valid username.
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" wire:model="role_id" required>
                        <option selected disabled value="">Choose...</option>
                        <option value="1">Admin</option>
                        <option value="2">College Dean</option>
                        <option value="3">Chairpeson</option>
                        <option value="4">Instructor</option>
                    </select>
                    <div class="invalid-feedback">
                        Please select a role.
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" wire:model="password" required>
                    <div class="invalid-feedback">
                        Please provide a valid password.
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation"
                        wire:model="password_confirmation" required>
                    <div class="invalid-feedback">
                        Please confirm your password.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" wire:click="create">Save changes</button>
            </div>
            </form>
        </div>
    </div>
</div>
{{-- End Add User --}}

