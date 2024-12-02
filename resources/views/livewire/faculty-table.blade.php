<div>
    <!-- Faculty Import Modal -->
    <div wire:ignore.self class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Faculty Members</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <!-- Display Import Summary Message -->
                    @if ($importSummary)
                        <div class="alert alert-info">
                            {{ $importSummary }}
                        </div>
                    @endif

                    <!-- Display Row-level Validation Errors -->
                    @if ($importErrors)
                        <div class="alert alert-danger mt-3">
                            <ul>
                                @foreach ($importErrors as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form wire:submit.prevent="importFaculties">
                        <div class="mb-3">
                            <label for="facultyFile" class="form-label">Upload File</label>
                            <input type="file" class="form-control" id="facultyFile" wire:model="facultyFile"
                                accept=".csv, .xlsx">
                            @error('facultyFile')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Display uploading state on the button -->
                        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="facultyFile">Import</span>
                            <span wire:loading wire:target="facultyFile">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Uploading...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Faculty Modal -->
    <div wire:ignore.self class="modal fade" id="exportFacultyModal" tabindex="-1"
        aria-labelledby="exportFacultyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportFacultyModalLabel">Export Faculties</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3">
                        <!-- College Filter -->
                        <div class="col-12">
                            <label for="college" class="form-label">College</label>
                            <select wire:model="college" id="college" class="form-select">
                                <option value="">All Colleges</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Department Filter -->
                        <div class="col-12">
                            <label for="department" class="form-label">Department</label>
                            <select wire:model="department" id="department" class="form-select">
                                <option value="">All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Export as
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                            <li><a class="dropdown-item" href="#" wire:click.prevent="exportAs('csv')">CSV</a>
                            </li>
                            <li><a class="dropdown-item" href="#" wire:click.prevent="exportAs('excel')">Excel</a>
                            </li>
                            <li><a class="dropdown-item" href="#" wire:click.prevent="exportAs('pdf')">PDF</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row mb-4">
        <div class="col-md-10">
            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Option</h6>
                    </li>
                    <a href="#" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importModal">Import
                        Faculty</a>
                    <a href="#" class="dropdown-item" data-bs-toggle="modal"
                        data-bs-target="#exportFacultyModal">Export
                        Faculty</a>
                    {{-- <li class="dropdown-submenu position-relative">
                        <a class="dropdown-item dropdown-toggle" href="#">Export As</a>
                        <ul class="dropdown-menu position-absolute">
                            <li><a wire:click.prevent="exportAs('csv')" href="#" class="dropdown-item">CSV</a>
                            </li>
                            <li><a wire:click.prevent="exportAs('excel')" href="#" class="dropdown-item">Excel</a>
                            </li>
                            <li><a wire:click.prevent="exportAs('pdf')" href="#" class="dropdown-item">PDF</a>
                            </li>
                        </ul>
                    </li>
                    <li><a class="dropdown-item text-danger" href="#">Delete Selected</a></li> --}}
                </ul>
            </div>
            {{-- Per Page --}}
            <div class="row g-1">
                <div class="col-md-1">
                    <select wire:model.live="perPage" name="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <input wire:model.live.debounce.300ms="search" type="text" name="search"
                        class="form-control" placeholder="Search faculty...">
                </div>

                {{-- Conditionally Display College Filter --}}
                @if (auth()->user()->isAdmin())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="college" name="college" class="form-select">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif(auth()->user()->isDean())
                    {{-- For Dean, the college is fixed and hidden, so no select is needed --}}
                    <input type="hidden" wire:model="college" value="{{ auth()->user()->college_id }}">
                @endif

                {{-- Conditionally Display Department Filter --}}
                @if (auth()->user()->isAdmin() || auth()->user()->isDean())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="department" name="department" class="form-select">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        @if (Auth::user()->isAdmin())
            <div class="col-12 col-md-2">
                <livewire:create-faculty />
            </div>
        @endif
    </div>


    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'username',
                        'displayName' => 'Username',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'email',
                        'displayName' => 'Email',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'first_name',
                        'displayName' => 'First Name',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'middle_name',
                        'displayName' => 'Middle Name',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'last_name',
                        'displayName' => 'Last Name',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'suffix',
                        'displayName' => 'Suffix',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'college.name',
                        'displayName' => 'College',
                    ])
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'department.name',
                        'displayName' => 'Department',
                    ])
                    <th scope="col" class="text-center">Status</th>
                    @if (Auth::user()->isAdmin())
                        <th scope="col" class="text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($faculties as $key => $faculty)
                    <tr wire:key="{{ $faculty->id }}"
                        onclick="window.location='{{ route('faculty.view', ['faculty' => $faculty->id]) }}';"
                        style="cursor: pointer;">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $faculty->username }}</td>
                        <td>{{ $faculty->email }}</td>
                        <td>{{ $faculty->first_name }}</td>
                        <td>{{ $faculty->middle_name }}</td>
                        <td>{{ $faculty->last_name }}</td>
                        <td>{{ $faculty->suffix }}</td>
                        <td>{{ $faculty->college->name ?? 'N/A' }}</td>
                        <td>{{ $faculty->department->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span
                                class="badge {{ $faculty->status == 'active' ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                {{ $faculty->status }}
                            </span>
                        </td>
                        @if (Auth::user()->isAdmin())
                            <td class="text-center">
                                <div class="btn-group dropstart">
                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false"
                                        onclick="event.stopPropagation()">
                                        <i class="bi bi-three-dots"></i>
                                    </a>
                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3"
                                        onclick="event.stopPropagation()">
                                        <li><button type="button" class="dropdown-item" href="#">View</button>
                                        </li>
                                        <li><button @click="$dispatch('edit-mode',{id:{{ $faculty->id }}})"
                                                type="button" class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#verticalycentered">Edit</button></li>
                                        <li><button wire:click="delete({{ $faculty->id }})"
                                                wire:confirm="Are you sure you want to delete '{{ $faculty->first_name }} {{ $faculty->last_name }}'"
                                                type="button" class="dropdown-item text-danger"
                                                href="#">Delete
                                                {{ $faculty->username }}</button>
                                    </ul>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $faculties->links() }}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('close-import-modal', () => {
                var importModal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                importModal.hide();
            });
        });

        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-faculty-table', (event) => {
                var myModalEl = document.querySelector('#verticalycentered')
                var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)

                setTimeout(() => {
                    modal.hide();
                    @this.dispatch('reset-modal');
                });
            })

            var mymodal = document.getElementById('verticalycentered')
            mymodal.addEventListener('hidden.bs.modal', (event) => {
                @this.dispatch('reset-modal');
            })
        })

        document.addEventListener('DOMContentLoaded', function() {
            var dropdowns = document.querySelectorAll('.dropdown-submenu');

            dropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('mouseover', function() {
                    let submenu = this.querySelector('.dropdown-menu');
                    submenu.classList.add('show');
                });

                dropdown.addEventListener('mouseout', function() {
                    let submenu = this.querySelector('.dropdown-menu');
                    submenu.classList.remove('show');
                });
            });
        });
    </script>
</div>
