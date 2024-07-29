<div>
    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#verticalycentereddepartment">
        Create Department
    </button>
    <div wire:ignore.self class="modal fade" id="verticalycentereddepartment" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $formTitle }}</h5>
                    <button wire:click="close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save" class="row g-3 needs-validation" novalidate>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name</label>
                            <input wire:model.lazy="name" type="text"
                                class="form-control @error('name') is-invalid @enderror" name="name">
                            @error('name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-6">
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
                    </div>
                    <div class="modal-footer">
                        @if ($editForm)
                        <button wire:click="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button wire:click="update" type="button" class="btn btn-primary">Save changes</button>
                        @else
                        <button wire:click="close" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button wire:click="save" type="button" class="btn btn-primary">Create Department</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
