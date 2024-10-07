<!-- resources/views/livewire/class-table.blade.php -->
<div>
    <div class="row mb-4">
        <div class="col-md-10">
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

                <div class="col-12 col-md-1">
                    <select wire:model.live="yearLevel" name="yearLevel" class="form-select">
                        <option value="">All Year Levels</option>
                        @foreach($yearLevels as $level)
                            <option value="{{ $level->year_level }}">{{ $level->year_level }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select wire:model.live="semester" name="semester" class="form-select">
                        <option value="">All Semesters</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->semester }}">{{ $semester->semester }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <select wire:model.live="schoolYear" name="schoolYear" class="form-select">
                        <option value="">All School Years</option>
                        @foreach($schoolYears as $year)
                            <option value="{{ $year->school_year }}">{{ $year->school_year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <input wire:model.live.debounce.300ms="search" type="text" name="search" class="form-control"
                        placeholder="Search classes...">
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-auto">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    @include('livewire.includes.table-sortable-th', ['name' => 'name', 'displayName' => 'Name'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'college.name', 'displayName' => 'College'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'department.name', 'displayName' => 'Department'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'year_level', 'displayName' => 'Year Level'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'semester', 'displayName' => 'Semester'])
                    @include('livewire.includes.table-sortable-th', ['name' => 'school_year', 'displayName' => 'School Year'])
                    @if (Auth::user()->role->name === 'admin')
                    <th scope="col" class="text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($sections as $key => $section)
                    <tr wire:key="{{ $section->id }}">
                        <th scope="row">{{ $key + 1 }}</th>
                        <td><a href="{{ route('viewSection', $section->id) }}">{{ $section->name }}</a></td>
                        <td>{{ $section->college->name }}</td>
                        <td>{{ $section->department->name }}</td>
                        <td>{{ $section->year_level }}</td>
                        <td>{{ $section->semester }}</td>
                        <td>{{ $section->school_year }}</td>
                        @if (Auth::user()->isAdmin())
                        <td class="text-center">
                            <div class="btn-group dropstart">
                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </a>
                                <ul class="dropdown-menu table-action table-dropdown-menu-arrow me-3">
                                    <li><button type="button" class="dropdown-item" href="#">View</button></li>
                                    <li><button @click="$dispatch('edit-class',{id:{{ $section->id }}})"
                                            type="button" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#verticalycenteredclass">Edit</button></li>
                                    <li><button wire:click="delete({{ $section->id }})"
                                            wire:confirm="Are you sure you want to delete '{{ $section->name }}'"
                                            type="button" class="dropdown-item text-danger"
                                            href="#">Delete</button>
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
        {{ $sections->links() }}
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-class-table', (event) => {
                var myModalEl = document.querySelector('#verticalycenteredclass')
                var modal = bootstrap.Modal.getOrCreateInstance(myModalEl)

                setTimeout(() => {
                    modal.hide();
                    @this.dispatch('reset-modal');
                });
            })

            var mymodal = document.getElementById('verticalycenteredclass')
            mymodal.addEventListener('hidden.bs.modal', (event) => {
                @this.dispatch('reset-modal');
            })
        })
    </script>
</div>
