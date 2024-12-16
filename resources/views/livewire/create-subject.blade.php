<div>
    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#verticalycentered">
        Create Subject
    </button>
    <div wire:ignore.self class="modal fade" id="verticalycentered" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $formTitle }}</h5>
                    <button wire:click="close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    @if ($lockError)
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $lockError }}
                        </div>
                    @endif
                    
                    <form wire:submit.prevent="save" class="row g-3 needs-validation" novalidate>
                        <div class="col-md-4">
                            <label for="name" class="form-label">Name</label>
                            <input wire:model.lazy="name" type="text"
                                class="form-control @error('name') is-invalid @enderror" name="name">
                            @error('name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="code" class="form-label">Code</label>
                            <input wire:model.lazy="code" type="text"
                                class="form-control @error('code') is-invalid @enderror" name="code">
                            @error('code')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="description" class="form-label">Description</label>
                            <input wire:model.lazy="description" type="text"
                                class="form-control @error('description') is-invalid @enderror" name="description">
                            @error('description')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="college_id" class="form-label">College</label>
                            <select wire:model.lazy="college_id" class="form-select @error('college_id') is-invalid @enderror" name="college_id">
                                <option value="">Select College</option>
                                @foreach($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                            @error('college_id')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="department_id" class="form-label">Department</label>
                            <select wire:model.lazy="department_id" class="form-select @error('department_id') is-invalid @enderror" name="department_id">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        @if ($editForm)
                            <button wire:click="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" @if ($lockError) disabled @endif>Save Changes</button>
                        @else
                            <button wire:click="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create Subject</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
