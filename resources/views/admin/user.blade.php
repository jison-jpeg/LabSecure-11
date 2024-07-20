@section('pageTitle', 'Users')
<x-app-layout>
    <main id="main" class="main">
        {{-- Dynamic Page Breadcrumbs --}}
        <div class="pagetitle">
            <h1>Hello, {{ Auth::user()->first_name }}! 👋</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item active">@yield('pageTitle')</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">

                <!-- Left side columns -->
                <div class="col-lg-12">
                    {{-- Alert Logged-in as what auth role --}}
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <strong>Logged-in as {{ Auth::user()->role->name }}!</strong>
                        You can now access the {{ Auth::user()->role->name }} dashboard.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="filter">
                                        <a class="icon" href="#" data-bs-toggle="dropdown"><i
                                                class="bi bi-three-dots"></i></a>
                                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                            <li class="dropdown-header text-start">
                                                <h6>Option</h6>
                                            </li>

                                            <li><a class="dropdown-item" href="#">Export</a></li>
                                            <li><a class="dropdown-item text-danger" href="#">Delete Selected</a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title">User Management</h5>
                                    </div>

                                    <!-- User Table -->
                                    {{-- @include('admin.partials.users', ['users' => $users]) --}}
                                    <!-- End Default Table Example -->

                                    {{-- User Table Livewire --}}
                                    @livewire('user-table')
                                    <!-- End User Table Livewire -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- End Left side columns -->

            </div>
        </section>

    </main>

    {{-- <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('delete-form');
            const checkboxes = document.querySelectorAll('.user-checkbox');
            const deleteButton = document.getElementById('delete-selected');
            const selectAllCheckbox = document.getElementById('select-all');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    toggleDeleteButton();
                });
            });

            selectAllCheckbox.addEventListener('change', function () {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                toggleDeleteButton();
            });

            function toggleDeleteButton() {
                const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
                deleteButton.disabled = !anyChecked;
            }
        });
    </script> --}}
</x-app-layout>
