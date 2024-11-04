<div>
    <div wire:ignore.self class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Code Field -->
                    <div class="mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" wire:model.defer="code" id="code" class="form-control">
                        @error('code') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" wire:model.defer="name" id="name" class="form-control">
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <!-- Description Field -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea wire:model.defer="description" id="description" class="form-control" rows="3"></textarea>
                        @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <!-- College Field -->
                    <div class="mb-3">
                        <label for="collegeId" class="form-label">College</label>
                        <select wire:model.defer="collegeId" id="collegeId" class="form-select">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                        @error('collegeId') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <!-- Department Field -->
                    <div class="mb-3">
                        <label for="departmentId" class="form-label">Department</label>
                        <select wire:model.defer="departmentId" id="departmentId" class="form-select">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('departmentId') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Subject</button>
                </div>
            </div>
        </div>
    </div>
</div>
