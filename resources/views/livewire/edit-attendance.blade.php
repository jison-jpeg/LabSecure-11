<div>
    <!-- Edit Attendance Modal -->
    <div wire:ignore.self class="modal fade" id="verticalycentered" tabindex="-1" aria-labelledby="editAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $formTitle }}</h5>
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
                    <button type="button" class="btn btn-primary" wire:click="{{ $editForm ? 'update' : 'save' }}">
                        {{ $editForm ? 'Update Attendance' : 'Save Attendance' }}
                    </button>                    
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript to Handle Modal Events --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
    @this.on('refresh-attendance-table', () => {
        let myModalEl = document.querySelector('#verticalycentered');
        let modal = bootstrap.Modal.getOrCreateInstance(myModalEl);

        setTimeout(() => {
            modal.hide();
            @this.dispatch('reset-modal');
        }); // Adjust delay if needed
    });

    let myModal = document.getElementById('verticalycentered');
    myModal.addEventListener('hidden.bs.modal', () => {
        @this.dispatch('reset-modal');
    });
});

    </script>
</div>
