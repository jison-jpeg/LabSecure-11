<div class="row">

    <div class="col-xxl-3 col-md-3">
        <div class="card info-card present-card">

            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Filter</h6>
                    </li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'today')" href="#">Today</a></li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'month')" href="#">This Month</a></li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'year')" href="#">This Year</a></li>
                </ul>
            </div>

            <div class="card-body">
                <h5 class="card-title">Present <span>| {{ ucfirst($filter) }}</span></h5>

                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="ps-3">
                        <h6>{{ $presentCount ?? 0 }}</h6>
                        <span class="text-muted small pt-2 ps-1">total</span>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="col-xxl-3 col-md-3">
        <div class="card info-card late-card">

            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Filter</h6>
                    </li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'today')" href="#">Today</a></li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'month')" href="#">This Month</a></li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'year')" href="#">This Year</a></li>
                </ul>
            </div>

            <div class="card-body">
                <h5 class="card-title">Late <span>| {{ ucfirst($filter) }}</span></h5>

                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="ps-3">
                        <h6>{{ $lateCount ?? 0 }}</h6>
                        <span class="text-muted small pt-2 ps-1">total</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="col-xxl-3 col-md-3">
        <div class="card info-card absent-card">

            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Filter</h6>
                    </li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'today')" href="#">Today</a></li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'month')" href="#">This Month</a></li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'year')" href="#">This Year</a></li>
                </ul>
            </div>

            <div class="card-body">
                <h5 class="card-title">Absent <span>| {{ ucfirst($filter) }}</span></h5>

                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="ps-3">
                        <h6>{{ $absentCount ?? 0 }}</h6>
                        <span class="text-muted small pt-2 ps-1">total</span>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="col-xxl-3 col-md-3">
        <div class="card info-card incomplete-card">

            <div class="filter">
                <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li class="dropdown-header text-start">
                        <h6>Filter</h6>
                    </li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'today')" href="#">Today</a></li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'month')" href="#">This Month</a></li>
                    <li><a class="dropdown-item" wire:click="$set('filter', 'year')" href="#">This Year</a></li>
                </ul>
            </div>

            <div class="card-body">
                <h5 class="card-title">Incomplete <span>| {{ ucfirst($filter) }}</span></h5>

                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                    <div class="ps-3">
                        <h6>{{ $incompleteCount ?? 0 }}</h6>
                        <span class="text-muted small pt-2 ps-1">total</span>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
