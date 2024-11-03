<div>
    <div class="row mb-4">
        <div class="col-md-10">
            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Options</h6>
                    </li>
                    <li><a wire:click.prevent="import" href="#" class="dropdown-item">Import</a></li>
                    <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteSelected">Delete Selected</a></li>
                    {{-- Add more options as needed --}}
                    <li><hr class="dropdown-divider"></li>
                    <li><a wire:click.prevent="exportAs('csv')" href="#" class="dropdown-item">Export as CSV</a></li>
                    <li><a wire:click.prevent="exportAs('excel')" href="#" class="dropdown-item">Export as Excel</a></li>
                    {{-- Add PDF export if implemented --}}
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
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control" placeholder="Search sections...">
                </div>
    
                {{-- Conditionally Display College Filter for Admin --}}
                @if(Auth::user()->isAdmin())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="college" name="college" class="form-select">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif(Auth::user()->isDean())
                    {{-- For Dean, the college is fixed and hidden --}}
                    <input type="hidden" wire:model="college" value="{{ Auth::user()->college_id }}">
                @endif
    
                {{-- Conditionally Display Department Filter for Admin and Dean --}}
                @if(Auth::user()->isAdmin() || Auth::user()->isDean())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="department" name="department" class="form-select" @if(Auth::user()->isAdmin() && !$college) disabled @endif>
                            <option value="">Select Department</option>
                            @forelse ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @empty
                                <option value="" disabled>No departments available</option>
                            @endforelse
                        </select>
                    </div>
                @endif
    
                {{-- Clear Filters Button --}}
                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear Filters</button>
                </div>
            </div>
        </div>
        @if(Auth::user()->isAdmin())
        <div class="col-12 col-md-2">
            <livewire:create-section />
        </div>
        @endif
    </div>

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', ['name' => 'name', 'displayName' => 'Code'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'college.name', 'displayName' => 'College'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'department.name', 'displayName' => 'Department'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'year_level', 'displayName' => 'Year Level'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'semester', 'displayName' => 'Semester'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'school_year', 'displayName' => 'School Year'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'created_at', 'displayName' => 'Created At'])
                    <th scope="col" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sections as $key => $section)
                    <tr wire:key="{{ $section->id }}" onclick="window.location='{{ route('section.view', ['section' => $section->id])}}';" style="cursor: pointer;">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $section->name }}</td>
                        <td>{{ $section->college->name }}</td>
                        <td>{{ $section->department->name }}</td>
                        <td>{{ $section->year_level }}</td>
                        <td>{{ $section->semester }}</td>
                        <td>{{ $section->school_year }}</td>
                        <td>{{ $section->created_at->diffForHumans() }}</td>
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3" onclick="event.stopPropagation()">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button @click="$dispatch('edit-mode',{id:{{ $section->id }}})"
                                            type="button" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycenteredsection">Edit</button></li>
                                    <li><button wire:click="delete({{ $section->id }})"
                                            wire:confirm="Are you sure you want to delete '{{ $section->name }}'"
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
        {{ $sections->links() }}
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-section-table', (event) => {
                var myModalEl = document.querySelector('#verticalycenteredsection')
                var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)

                setTimeout(() => {
                    modal.hide();
                    @this.dispatch('reset-modal');
                });
            })

            var mymodal = document.getElementById('verticalycenteredsection')
            mymodal.addEventListener('hidden.bs.modal', (event) => {
                @this.dispatch('reset-modal');
            })
        })
    </script>
</div>
