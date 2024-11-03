<div>
    <!-- Edit Attendance Modal -->
    <div wire:ignore.self class="modal fade" id="verticalycentered" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            @if($editForm)
                <form wire:submit.prevent="update">
            @else
                <form wire:submit.prevent="save">
            @endif
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAttendanceModalLabel">{{ $formTitle }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="$dispatch('reset-modal')"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Status Field -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select wire:model.defer="status" id="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="late">Late</option>
                                <option value="excused">Excused</option>
                                <option value="incomplete">Incomplete</option>
                            </select>
                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Remarks Field -->
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea wire:model.defer="remarks" id="remarks" class="form-control" rows="3"></textarea>
                            @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- Close Button -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$dispatch('reset-modal')">Close</button>
                        <!-- Save/Update Button -->
                        @if($editForm)
                            <button type="submit" class="btn btn-primary">Update Attendance</button>
                        @else
                            <button type="submit" class="btn btn-primary">Save Attendance</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- JavaScript to Handle Modal Events --}}
    <script>
        document.addEventListener('livewire:load', function () {
            // Listen for the 'edit-mode' event to open the modal
            Livewire.on('edit-mode', () => {
                var editModal = new bootstrap.Modal(document.getElementById('verticalycentered'));
                editModal.show();
            });

            // Listen for the 'reset-modal' event to hide the modal
            Livewire.on('reset-modal', () => {
                var editModal = bootstrap.Modal.getInstance(document.getElementById('verticalycentered'));
                if (editModal) {
                    editModal.hide();
                }
            });

            // Optionally, listen for 'refresh-attendance-table' to perform actions like reloading data
            Livewire.on('refresh-attendance-table', () => {
                // Implement any additional logic if needed
            });
        });
    </script>
</div>
