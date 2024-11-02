<div>
    <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#verticalycentered">Add
        Laboratory
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
                            <label for="type" class="form-label">Type</label>
                            <input wire:model.lazy="type" type="text"
                                class="form-control @error('type') is-invalid @enderror" name="type">
                            @error('type')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="location" class="form-label">Location</label>
                            <input wire:model="location" type="text"
                                class="form-control @error('location') is-invalid @enderror" name="location">
                            @error('location')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            @if ($editForm)
                                <button wire:click="close" type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button wire:click="update" type="button" class="btn btn-primary">Save changes</button>
                            @else
                                <button wire:click="close" type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Close</button>
                                <button wire:click="save" type="button" class="btn btn-primary">Create laboratory</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
