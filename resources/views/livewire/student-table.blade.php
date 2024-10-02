<div>
    <div class="row mb-4">
        <div class="col-md-10">
            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Option</h6>
                    </li>
                    <li><a wire:click.prevent="import" href="#" class="dropdown-item">Import</a></li>
                    <li class="dropdown-submenu position-relative">
                        <a class="dropdown-item dropdown-toggle" href="#">Export As</a>
                        <ul class="dropdown-menu position-absolute">
                            <li><a wire:click.prevent="exportAs('csv')" href="#" class="dropdown-item">CSV</a></li>
                            <li><a wire:click.prevent="exportAs('excel')" href="#" class="dropdown-item">Excel</a></li>
                            <li><a wire:click.prevent="exportAs('pdf')" href="#" class="dropdown-item">PDF</a></li>
                        </ul>
                    </li>
                    <li><a class="dropdown-item text-danger" href="#">Delete Selected</a></li>
                </ul>
            </div>
    
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
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control"
                        placeholder="Search students...">
                </div>
    
                {{-- Admin only filters --}}
                @if (Auth::user()->isAdmin())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="college" name="college" class="form-select">
                            <option value="">Select College</option>
                            @foreach ($colleges as $college)
                                <option value="{{ $college->id }}">{{ $college->name }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="col-12 col-md-2">
                        <select wire:model.live="department" name="department" class="form-select">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
    
                {{-- Instructor-specific filters --}}
                @if (Auth::user()->isInstructor())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="scheduleCode" name="schedule_code" class="form-select">
                            <option value="">Select Schedule Code</option>
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->schedule_code }}">{{ $schedule->schedule_code }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="col-12 col-md-2">
                        <select wire:model.live="section" name="section" class="form-select">
                            <option value="">Select Section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
    
                {{-- Dean filters (college, department, section) --}}
                @if (Auth::user()->isDean())
                    <div class="col-12 col-md-2">
                        <select wire:model.live="scheduleCode" name="scheduleCode" class="form-select">
                            <option value="">Select Schedule Code</option>
                            @foreach ($schedules as $schedule)
                                <option value="{{ $schedule->schedule_code }}">{{ $schedule->schedule_code }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div class="col-12 col-md-2">
                        <select wire:model.live="section" name="section" class="form-select">
                            <option value="">Select Section</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
    
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
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear Filters</button>
                </div>
            </div>
        </div>
    
        @if (Auth::user()->isAdmin())
            <div class="col-12 col-md-2">
                <livewire:create-student />
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
                    @if (Auth::user()->role->name === 'admin')
                        <th scope="col" class="text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $key => $user)
                    <tr wire:key="{{ $user->id }}" onclick="window.location='{{ route('student.view', ['student' => $user->id]) }}';" style="cursor: pointer;">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->section->name ?? 'N/A' }}</td>
                        <td>{{ $user->first_name }}</td>
                        <td>{{ $user->middle_name }}</td>
                        <td>{{ $user->last_name }}</td>
                        <td>{{ $user->suffix }}</td>
                        <td>{{ $user->college->name ?? 'N/A' }}</td>
                        <td>{{ $user->department->name ?? 'N/A' }}</td>
                        @if (Auth::user()->role->name === 'admin')
                            <td class="text-center">
                                <div class="btn-group dropstart">
                                    <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                        <i class="bi bi-three-dots"></i>
                                    </a>
                                    <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3" onclick="event.stopPropagation()">
                                        <li><button type="button" class="dropdown-item" href="#">View</button>
                                        </li>
                                        <li><button @click="$dispatch('edit-mode',{id:{{ $user->id }}})"
                                                type="button" class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#verticalycentered">Edit</button></li>
                                        <li><button wire:click="delete({{ $user->id }})"
                                                wire:confirm="Are you sure you want to delete '{{ $user->first_name }} {{ $user->last_name }}'"
                                                type="button" class="dropdown-item text-danger" href="#">Delete
                                                {{ $user->username }}</button>
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
