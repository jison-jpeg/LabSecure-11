<div>
    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#verticalycentered">
        Create Schedule
    </button>
    <div wire:ignore.self class="modal fade" id="verticalycentered" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $formTitle }}</h5>
                    <button wire:click="close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save" class="row g-3 needs-validation" novalidate>
                        <div class="col-md-4">
                            <label for="subject" class="form-label">Subject</label>
                            <input wire:model.lazy="subject" type="text"
                                class="form-control @error('subject') is-invalid @enderror" name="subject">
                            @error('subject')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="room" class="form-label">Room</label>
                            <input wire:model="room" type="text"
                                class="form-control @error('room') is-invalid @enderror" name="room">
                            @error('room')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="time" class="form-label">Time</label>
                            <input wire:model.lazy="time" type="text"
                                class="form-control @error('time') is-invalid @enderror" name="time">
                            @error('time')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="day" class="form-label">Day</label>
                            <input wire:model.lazy="day" type="text"
                                class="form-control @error('day') is-invalid @enderror" name="day">
                            @error('day')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="teacher" class="form-label">Teacher</label>
                            <input wire:model.lazy="teacher" type="text"
                                class="form-control @error('teacher') is-invalid @enderror" name="teacher">
                            @error('teacher')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="section" class="form-label">Section</label>
                            <input wire:model.lazy="section" type="text"
                                class="form-control @error('section') is-invalid @enderror" name="section">
                            @error('section')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror

</div>
