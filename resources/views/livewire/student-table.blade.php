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
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control" placeholder="Search students...">
                </div>
    
                {{-- Conditionally Display College Filter --}}
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
                    {{-- For Dean, the college is fixed and hidden, so no select is needed --}}
                    <input type="hidden" wire:model="college" value="{{ Auth::user()->college_id }}">
                @endif
    
                {{-- Conditionally Display Department Filter --}}
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
    
                {{-- Conditionally Display Schedule Code Filter --}}
                @if(Auth::user()->isDean() || Auth::user()->isChairperson() || Auth::user()->isInstructor())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="scheduleCode" name="scheduleCode" class="form-select">
                            <option value="">Select Schedule Code</option>
                            @forelse ($schedules as $schedule)
                                <option value="{{ $schedule->schedule_code }}">{{ $schedule->schedule_code }}</option>
                            @empty
                                <option value="" disabled>No schedules available</option>
                            @endforelse
                        </select>
                    </div>
                @endif
    
                {{-- Conditionally Display Section Filter --}}
                @if(Auth::user()->isAdmin() || Auth::user()->isDean() || Auth::user()->isChairperson() || Auth::user()->isInstructor())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="section" name="section" class="form-select">
                            <option value="">Select Section</option>
                            @forelse ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @empty
                                <option value="" disabled>No sections available</option>
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
        <div class="col-12 col-md-2">
            <livewire:create-student />
        </div>
    </div>
    
    {{-- Student Table --}}
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
                        'name' => 'section.name',
                        'displayName' => 'Section',
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
                @foreach ($users as $key => $student)
                    <tr wire:key="{{ $student->id }}" onclick="window.location='{{ route('student.view', ['student' => $student->id]) }}';" style="cursor: pointer;">
                        <th scope="row">{{ ($users->currentPage() - 1) * $users->perPage() + $key + 1 }}</th>
                        <td>{{ $student->username }}</td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->section->name ?? 'N/A' }}</td>
                        <td>{{ $student->first_name }}</td>
                        <td>{{ $student->middle_name }}</td>
                        <td>{{ $student->last_name }}</td>
                        <td>{{ $student->suffix }}</td>
                        <td>{{ $student->college->name ?? 'N/A' }}</td>
                        <td>{{ $student->department->name ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge rounded-pill bg-{{ $student->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($student->status) }}</span>
                        </td>
                        @if (Auth::user()->isAdmin())
                            <td class="text-center">
                                <div class="btn-group dropstart">
                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                        <i class="bi bi-three-dots"></i>
                                    </a>
                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3" onclick="event.stopPropagation()">
                                        <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                        <li>
                                            <button 
                                                @click="$dispatch('edit-mode',{id:{{ $student->id }}})"
                                                type="button" 
                                                class="dropdown-item" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#verticalycentered">
                                                Edit
                                            </button>
                                        </li>
                                        <li>
                                            <button 
                                                wire:click="delete({{ $student->id }})"
                                                wire:confirm="Are you sure you want to delete '{{ $student->first_name }} {{ $student->last_name }}'"
                                                type="button" 
                                                class="dropdown-item text-danger" 
                                                href="#">
                                                Delete {{ $student->username }}
                                            </button>
                                        </li>
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
        {{ $users->links() }}
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-student-table', (event) => {
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
