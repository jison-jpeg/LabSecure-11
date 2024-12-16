<div>
    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#verticalycenteredsection">
        Create Section
    </button>
    <div wire:ignore.self class="modal fade" id="verticalycenteredsection" tabindex="-1">
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
                                class="form-control @error('name') is-invalid @enderror" name="name" placeholder="ex. A">
                            @error('name')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="college_id" class="form-label">College</label>
                            <select wire:model.lazy="college_id" class="form-select @error('college_id') is-invalid @enderror"
                                name="college_id">
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
                            <select wire:model.lazy="department_id" class="form-select @error('department_id') is-invalid @enderror"
                                name="department_id">
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
                        <div class="col-md-4">
                            <label for="year_level" class="form-label">Year Level</label>
                            <select wire:model.lazy="year_level" class="form-select @error('year_level') is-invalid @enderror"
                                name="year_level">
                                <option value="">Select Year Level</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                                <option value="5">5th Year</option>
                                <option value="irregular">Irregular</option>

                            </select>
                            @error('year_level')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="semester" class="form-label">Semester</label>
                            <select wire:model.lazy="semester" class="form-select @error('semester') is-invalid @enderror"
                                name="semester">
                                <option value="">Select Semester</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="summer">Summer</option>
                            </select>
                            @error('semester')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="school_year" class="form-label">School Year</label>
                            <input wire:model.lazy="school_year" id="school_year" type="text" placeholder="ex. YYYY-YYYY"
                                class="form-control @error('school_year') is-invalid @enderror" name="school_year">
                            @error('school_year')
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
                            <button type="submit" class="btn btn-primary">Create Section</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const schoolYearInput = document.getElementById('school_year');

        schoolYearInput.addEventListener('input', function () {
            let value = schoolYearInput.value.replace(/\D/g, '');
            if (value.length > 4) {
                value = value.substring(0, 4) + '-' + value.substring(4, 8);
            }
            schoolYearInput.value = value;
        });
    });
</script>
