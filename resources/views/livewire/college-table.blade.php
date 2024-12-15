<div>
    <!-- Import College Modal -->
    <div wire:ignore.self class="modal fade" id="importCollegeModal" tabindex="-1" aria-labelledby="importModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Colleges</h5>
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

                    <form wire:submit.prevent="importColleges">
                        <div class="mb-3">
                            <label for="collegeFile" class="form-label">Upload File</label>
                            <input type="file" class="form-control" id="collegeFile" wire:model="collegeFile"
                                accept=".csv, .xlsx">
                            @error('collegeFile')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Display uploading state on the button -->
                        <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="collegeFile">Import</span>
                            <span wire:loading wire:target="collegeFile">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Uploading...
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export College Modal -->
    <div wire:ignore.self class="modal fade" id="exportCollegeModal" tabindex="-1"
        aria-labelledby="exportCollegeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportCollegeModalLabel">Export Colleges</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form class="row g-3">
                        <!-- College Filter -->
                        <div class="col-12">
                            <label for="selectedCollege" class="form-label">Filter by College</label>
                            <select wire:model="selectedCollege" id="selectedCollege" class="form-select">
                                <option value="">All Colleges</option>
                                @foreach ($colleges as $college)
                                    <option value="{{ $college->id }}">{{ $college->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                            data-bs-toggle="dropdown" aria-expanded="false" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="exportAs">Export as</span>
                            <span wire:loading wire:target="exportAs">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Exporting...
                            </span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li>
                                <a class="dropdown-item" href="#" wire:click.prevent="exportAs('csv')">
                                    <span wire:loading.remove wire:target="exportAs('csv')">CSV</span>
                                    <span wire:loading wire:target="exportAs('csv')">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        Exporting CSV...
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" wire:click.prevent="exportAs('excel')">
                                    <span wire:loading.remove wire:target="exportAs('excel')">Excel</span>
                                    <span wire:loading wire:target="exportAs('excel')">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        Exporting Excel...
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" wire:click.prevent="exportAs('pdf')">
                                    <span wire:loading.remove wire:target="exportAs('pdf')">PDF</span>
                                    <span wire:loading wire:target="exportAs('pdf')">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        Exporting PDF...
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="row mb-4 mt-4">
        <div class="col-md-10">
            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Option</h6>
                    </li>
                    <a href="#" class="dropdown-item" data-bs-toggle="modal"
                        data-bs-target="#importCollegeModal">Import Colleges</a>
                    <a href="#" class="dropdown-item" data-bs-toggle="modal"
                        data-bs-target="#exportCollegeModal">Export Colleges</a>
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

                <div class="col-12 col-md-4">
                    <input wire:model.live.debounce.300ms="search" type="text" name="search"
                        class="form-control" placeholder="Search colleges...">
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <livewire:create-college />
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'name',
                        'displayName' => 'Name',
                    ])

                    @include('livewire.includes.table-sortable-th', [
                        'name' => 'description',
                        'displayName' => 'Description',
                    ])
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($colleges as $key => $college)
                    <tr wire:key="{{ $college->id }}"
                        onclick="window.location='{{ route('college.view', ['college' => $college->id]) }}';"
                        style="cursor: pointer;">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $college->name }}</td>
                        <td>{{ Str::limit($college->description, 100, '...') }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false"
                                    onclick="event.stopPropagation()">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3"
                                    onclick="event.stopPropagation()">
                                    <li>
                                        <a href="{{ route('college.view', ['college' => $college->id]) }}"
                                            class="dropdown-item">View</a>
                                    </li>
                                    <li><button @click="$dispatch('edit-mode',{id:{{ $college->id }}})"
                                            type="button" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycentered">Edit</button></li>
                                    <li><button wire:click="delete({{ $college->id }})"
                                            wire:confirm="Are you sure you want to delete '{{ $college->name }}'"
                                            type="button" class="dropdown-item text-danger"
                                            href="#">Delete</button>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $colleges->links() }}
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-college-table', (event) => {
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
