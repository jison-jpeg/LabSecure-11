<div>
    <section class="section dashboard">
        <div class="row">
            <!-- Department Overview -->
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card">
                            <a href="#">
                                <div class="card-body">
                                    <h5 class="card-title">Total Students</h5>
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div class="ps-3 mt-2 pagetitle">
                                            <h1>{{ $totalStudents }}</h1>
                                            <span class="text-muted small pt-2 ps-1">total</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card">
                            <a href="#">
                                <div class="card-body">
                                    <h5 class="card-title">Total Instructors</h5>
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div class="ps-3 mt-2 pagetitle">
                                            <h1>{{ $totalInstructors }}</h1>
                                            <span class="text-muted small pt-2 ps-1">total</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-xxl-4 col-md-4">
                        <div class="card info-card">
                            <a href="#">
                                <div class="card-body">
                                    <h5 class="card-title">Total Sections</h5>
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-list-ul"></i>
                                        </div>
                                        <div class="ps-3 mt-2 pagetitle">
                                            <h1>{{ $sections->count() }}</h1>
                                            <span class="text-muted small pt-2 ps-1">total</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Department Description -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $department->name }}</h5>
                        <p class="mb-0"><strong>College:</strong> {{ $department->name ?? 'N/A' }}</p>
                        <p><strong>Description:</strong> {{ $department->description }}</p>
                        <p class="mb-0"><strong>Chairperson:</strong> {{ $department->chairperson ?? 'N/A' }}</p>

                    </div>
                </div>

                <!-- Sections List -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sections</h5>
                        <div class="row">
                            <div class="col-8">
                                <ul class="list-unstyled">
                                    @foreach ($sections as $section)
                                        <li>{{ $section->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-4 text-end">
                                <ul class="list-unstyled">
                                    @foreach ($sections as $section)
                                        <li><a href="{{ route('section.view', ['section' => $section->id]) }}"
                                                class="text-primary">View Details</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subjects List -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Subjects</h5>
                        <div class="row">
                            <div class="col-8">
                                <ul class="list-unstyled">
                                    @foreach ($subjects as $subject)
                                        <li>{{ $subject->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-4 text-end">
                                <ul class="list-unstyled">
                                    @foreach ($subjects as $subject)
                                        <li><a href="{{ route('subject.view', ['subject' => $subject->id]) }}"
                                                class="text-primary">View Details</a></li>
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
                    @livewire('logs-widget')
                </div>
            </div>
        </div>
    </section>
</div>
