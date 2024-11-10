<div>
    <div wire:ignore.self class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSubjectModalLabel">Edit Subject</h5>
                    <button wire:click="close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="update" class="row g-3 needs-validation" novalidate>
                        <!-- Code Field -->
                        <div class="col-md-6">
                            <label for="code" class="form-label">Code</label>
                            <input wire:model.lazy="code" type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code">
                            @error('code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Name Field -->
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input wire:model.lazy="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- College Field -->
                        <div class="col-md-6">
                            <label for="collegeId" class="form-label">College</label>
                            <select wire:model.lazy="collegeId" id="collegeId" class="form-select @error('collegeId') is-invalid @enderror" name="collegeId">
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                            @error('collegeId')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Department Field -->
                        <div class="col-md-6">
                            <label for="departmentId" class="form-label">Department</label>
                            <select wire:model.lazy="departmentId" id="departmentId" class="form-select @error('departmentId') is-invalid @enderror" name="departmentId">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('departmentId')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                         <!-- Description Field -->
                         <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea wire:model.lazy="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3" name="description"></textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button wire:click="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
