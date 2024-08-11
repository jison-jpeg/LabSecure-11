<div>
    <div class="row mb-5">
        <div class="col-md-10">
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
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                        placeholder="Search laboratories...">
                </div>
                <div class="col-12 col-md-2">
                    <select wire:model.live="type" name="type" class="form-select">
                        <option value="">Laboratory Type</option>
                        <option value="Computer Laboratory">Computer</option>
                        <option value="EMC Laboratory">EMC</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <button class="btn btn-secondary w-100 mb-1" type="reset" wire:click="clear">Clear
                        Filters</button>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <livewire:create-laboratory />
        </div>
    </div>

    <div>

        <div class="row">
            @foreach ($laboratories as $laboratory)
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-4">
                    <a href="#">
                        <div class="card info-card sales-card lab-card">
                            <div class="action">
                                <a class="icon" href="#" data-bs-toggle="dropdown"><i
                                        class="bi bi-three-dots"></i></a>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                        <h6>Action</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#">View</a></li>
                                    <li><a  @click="$dispatch('edit-mode',{id:{{ $laboratory->id }}})" class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#verticalycentered">Edit</a></li>
                                    <li><a wire:click="delete({{ $laboratory->id }})"  wire:confirm="Are you sure you want to delete laboratory {{ $laboratory->name}} ?" class="dropdown-item text-danger" href="#">Delete LAB
                                            {{ $laboratory->id }}</a></li>
                                </ul>
                            </div>
                            <div class="card-body mt-3">
                                <h5 class="badge rounded-pill {{ $laboratory->status == 'Occupied' ? 'bg-warning text-black' : ($laboratory->status == 'Locked' ? 'bg-danger' : ($laboratory->status == 'Available' ? 'bg-success' : 'bg-secondary')) }}">
                                    {{ $laboratory->status }}
                                </h5>
                                <div class="row mt-4 sub-header">
                                    <div class="col-6 text-start text-truncate">
                                        <h6 class="text-muted">TYPE</h6>
                                        <span>{{ $laboratory->type }}</span>
                                    </div>
                                    <div class="col-6 text-end text-truncate">
                                        <h6 class="text-muted">LOCATION</h6>
                                        <span>{{ $laboratory->location }}</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <h1 class="lab-title">LAB</h1>
                                    </div>
                                    <div class="col-auto">
                                        <h5 class="sub-lab-title">{{ $laboratory->name }}</h5>
                                    </div>
                                </div>
                                <div class="row mt-4 sub-header">
                                    <div class="col-6 text-start text-truncate">
                                        <h6 class="text-truncate">FIRST NAME LAST NAME</h6>
                                        <span class="text-muted">
                                            {{ $laboratory->status == 'Occupied' ? 'Current User' : ($laboratory->status == 'Locked' ? 'Locked By' : 'Recent User') }}
                                        </span>
                                    </div>
                                    <div class="col-6 text-truncate text-end align-self-end">
                                        <span class="text-muted">7 hours ago</span>
                                    </div>
                                </div>                                
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-start">
            {!! $laboratories->links() !!}
        </div>
    </div>
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('refresh-laboratory-table', (event) => {
                //alert('product created/updated')
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
    </script>
</div>
