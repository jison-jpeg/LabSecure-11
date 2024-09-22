<div>
    <section class="section dashboard">
        <div class="row">

            <!-- College Overview -->
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card sales-card">
                            <a href="#">
                                <div class="card-body">
                                    <h5 class="card-title">Total Students</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div class="ps-3 mt-2 pagetitle">
                                            <h1>{{ $studentsCount }}</h1>
                                            <span class="text-muted small pt-2 ps-1">total</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card present-card">
                            <a href="#">
                                <div class="card-body">
                                    <h5 class="card-title"> Total Instructors</h5>
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
                            </a>
                        </div>
                    </div>

                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card customers-card">
                            <a href="#">
                                <div class="card-body">
                                    <h5 class="card-title">Total Departments</h5>
                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                        <div class="ps-3 mt-2 pagetitle">
                                            <h1>{{ $departmentsCount }}</h1>
                                            <span class="text-muted small pt-2 ps-1">total</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $college->name }}</h5>
                        <p class="mb-0">Description: {{ $college->description }}</p>
                        <p class="mb-0">College Dean: N/A</p>

                    </div>
                </div>

                <!-- Department List -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Departments</h5>
                        <div class="row">
                            <div class="col-8">
                                <ul class="list-unstyled">
                                    @foreach ($departments as $department)
                                        <li>{{ $department->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-4 text-end">
                                <ul class="list-unstyled">
                                    @foreach ($departments as $department)
                                        <li><a href="{{ route('department.view', ['department' => $department->id]) }}" class="text-primary">View Details</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side columns -->
            <div class="col-lg-4 d-flex flex-column">
                <div class="card h-100">
                    @livewire('logs-widget', ['collegeId' => $college->id])  <!-- Pass the college ID -->
                </div>
            </div>
        </div>
    </section>
</div>
