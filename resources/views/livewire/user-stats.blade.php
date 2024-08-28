<div class="row">
    <div class="col-xxl-4 col-md-4">
        <div class="card info-card sales-card">
            <div class="card-body">
                <h5 class="card-title">Users</h5>

                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3 mt-2 pagetitle">
                        <h1>{{ $usersCount }}</h1>
                        <span class="text-muted small pt-2 ps-1">total</span>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <div class="col-xxl-4 col-md-4">
        <div class="card info-card revenue-card">

            <div class="card-body">
                <h5 class="card-title">Instructors</h5>

                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="ps-3 mt-2 pagetitle">
                        <h1>{{ $instructorsCount }}</h1>
                        <span class="text-muted small pt-2 ps-1">total</span>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <div class="col-xxl-4 col-md-4">

        <div class="card info-card customers-card">

            <div class="card-body">
                <h5 class="card-title">Students</h5>

                <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="ps-3 mt-2 pagetitle">
                        <h1>{{ $studentsCount }}</h1>
                        <span class="text-muted small pt-2 ps-1">total</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
