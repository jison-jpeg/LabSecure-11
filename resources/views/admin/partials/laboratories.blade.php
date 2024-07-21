<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xxl-5 mb-5 mt-5">
    @foreach ($laboratories as $laboratory)
        <div class="col mb-4">
            <div class="card lab-card h-100">
                <div class="filter position-relative">
                    {{-- If authenticated user is not admin, hide the button --}}
                    @if (Auth::user()->role->name === 'admin')
                        <a class="icon position-absolute top-0 end-0 me-3 p-2" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li class="dropdown-header text-start">
                                <h6>OPTIONS</h6>
                            </li>
                            <li>
                                <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editModal">Edit</a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal">
                                    Delete Comlab {{ $lab->name }}
                                </a>
                            </li>
                        </ul>
                    @endif
                </div>
                <a class="card-body" data-bs-toggle="modal" data-bs-target="#viewModal{{ $lab->id }}">
                    <div class="card-badge mb-3">
                        <span
                            class="badge rounded-pill bg-{{ in_array($lab->status, ['Occupied', 'Locked', 'N/A']) ? 'danger' : 'success' }}">
                            {{ $lab->status ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <span class="text-muted small">{{ $lab->type }}</span>
                        <h6 class="mt-2">LAB {{ $lab->name }}</h6>
                        <span class="text-muted small">{{ $lab->location }}</span>
                    </div>
                    <div class="mt-4">
                        <div class="fw-bold">Recent:</div>
                        <div class="text small">Recent User Name</div>
                        <div class="text-muted small">Time</div>
                    </div>
                </a>
            </div>
        </div>
    @endforeach
</div>

<div class="d-flex justify-content-start">
    {!! $laboratories->links() !!}
</div>
