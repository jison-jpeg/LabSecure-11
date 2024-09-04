<div class="row">
    <div class="col-xxl-3 col-md-3">
        <div class="card info-card sales-card">
            <div class="card-body mt-4">
                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="ps-3">
                        <h6>{{ $presentCount ?? 0 }}</h6>
                        <span class="text-muted small pt-2 ps-1">Present</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-3">
        <div class="card info-card sales-card">
            <div class="card-body mt-4">
                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="ps-3">
                        <h6>{{ $lateCount ?? 0}}</h6>
                        <span class="text-muted small pt-2 ps-1">Late</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-3">
        <div class="card info-card sales-card">
            <div class="card-body mt-4">
                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="ps-3">
                        <h6>{{ $absentCount ?? 0}}</h6>
                        <span class="text-muted small pt-2 ps-1">Absent</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xxl-3 col-md-3">
        <div class="card info-card sales-card">
            <div class="card-body mt-4">
                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                    <div class="ps-3">
                        <h6>{{ $incompleteCount ?? 0}}</h6>
                        <span class="text-muted small pt-2 ps-1">Incomplete</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
