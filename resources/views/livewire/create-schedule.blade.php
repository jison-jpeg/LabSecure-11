<div>
    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#verticalycentered">
        Create Schedule
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
                    @if ($conflicts)
                        <div class="alert alert-danger">
                            <h5>Conflicting Schedules:</h5>
                            <ul>
                                @foreach ($conflicts as $conflict)
                                    <li>
                                        {{ $conflict->instructor->first_name }} {{ $conflict->instructor->last_name }}
                                        has {{ $conflict->subject->name }} on
                                        {{ implode(', ', json_decode($conflict->days_of_week)) }}
                                        from {{ $conflict->start_time }} to {{ $conflict->end_time }}
                                        in section {{ $conflict->section->name }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form wire:submit.prevent="save" class="row g-3 needs-validation" novalidate>
                        <div class="col-md-4">
                            <label for="subject_id" class="form-label">Subject</label>
                            <select wire:model.lazy="subject_id"
                                class="form-select @error('subject_id') is-invalid @enderror" name="subject_id">
                                <option value="">Select Subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="instructor_id" class="form-label">Instructor</label>
                            <select wire:model.lazy="instructor_id"
                                class="form-select @error('instructor_id') is-invalid @enderror" name="instructor_id">
                                <option value="">Select Instructor</option>
                                @foreach ($instructors as $instructor)
                                    <option value="{{ $instructor->id }}">{{ $instructor->first_name }}
                                        {{ $instructor->last_name }}</option>
                                @endforeach
                            </select>
                            @error('instructor_id')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="laboratory_id" class="form-label">Laboratory</label>
                            <select wire:model.lazy="laboratory_id"
                                class="form-select @error('laboratory_id') is-invalid @enderror" name="laboratory_id">
                                <option value="">Select Laboratory</option>
                                @foreach ($laboratories as $laboratory)
                                    <option value="{{ $laboratory->id }}">{{ $laboratory->name }}</option>
                                @endforeach
                            </select>
                            @error('laboratory_id')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- College Selection -->
                        <div class="col-md-4">
                            <label for="college_id" class="form-label">College</label>
                            <select wire:model.lazy="college_id"
                                class="form-select @error('college_id') is-invalid @enderror" name="college_id"
                                required>
                                <option value="">Select College</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                            @error('college_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Department Selection -->
                        <div class="col-md-4">
                            <label for="department_id" class="form-label">Department</label>
                            <select wire:model.lazy="department_id"
                                class="form-select @error('department_id') is-invalid @enderror" name="department_id"
                                required>
                                <option value="">Select Department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Year Level Selection -->
                        <div class="col-md-2">
                            <label for="year_level" class="form-label">Year Level</label>
                            <select wire:model.lazy="year_level"
                                class="form-select @error('year_level') is-invalid @enderror" name="year_level"
                                required>
                                <option value="">Select Year Level</option>
                                @foreach ($year_levels as $level)
                                    <option value="{{ $level }}">{{ $level }}</option>
                                @endforeach
                            </select>
                            @error('year_level')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Section Selection -->
                        <div class="col-md-2">
                            <label for="section_id" class="form-label">Section</label>
                            <select wire:model.lazy="section_id"
                                class="form-select @error('section_id') is-invalid @enderror" name="section_id"
                                required>
                                <option value="">Select Section</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                                @endforeach
                            </select>
                            @error('section_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="days_of_week" class="form-label">Days of Week</label>
                            <select wire:model.lazy="days_of_week" multiple
                                class="form-select @error('days_of_week') is-invalid @enderror" name="days_of_week">
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                            @error('days_of_week')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input wire:model.lazy="start_time" type="time"
                                class="form-control @error('start_time') is-invalid @enderror" name="start_time">
                            @error('start_time')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="end_time" class="form-label">End Time</label>
                            <input wire:model.lazy="end_time" type="time"
                                class="form-control @error('end_time') is-invalid @enderror" name="end_time">
                            @error('end_time')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                </div>
                <div class="modal-footer">
                    @if ($editForm)
                        <button wire:click="close" type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button>
                        <button wire:click="update" type="button" class="btn btn-primary">Save changes</button>
                    @else
                        <button wire:click="close" type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Close</button>
                        <button wire:click="save" type="button" class="btn btn-primary">Create Schedule</button>
                    @endif
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
