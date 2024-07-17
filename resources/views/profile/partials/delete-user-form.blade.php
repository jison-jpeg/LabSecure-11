<div class="card">
    <div class="card-body">
        <h5 class="card-title">Delete Account</h5>
        <p>Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.</p>
        <div class="text-start">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                DELETE ACCOUNT
            </button>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.</p>
                <form id="deleteAccountForm" method="post" action="{{ route('profile.destroy') }}" class="row g-3">
                    @csrf
                    @method('delete')
                    <div class="col-12">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                        @if ($errors->userDeletion->has('password'))
                            <div class="text-danger mt-2">
                                {{ $errors->userDeletion->first('password') }}
                            </div>
                        @endif
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
                </form>
        </div>
    </div>
</div>

<!-- JavaScript to keep modal open if there's an error -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var hasErrors = {{ $errors->userDeletion->any() ? 'true' : 'false' }};
        if (hasErrors) {
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    });
</script>
