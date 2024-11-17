@section('pageTitle', 'Courses')
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
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">College and Department Management</h5>
                                    <!-- Bordered Tabs Justified -->
                                    <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified"
                                        role="tablist">
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100 active" id="home-tab" data-bs-toggle="tab"
                                                data-bs-target="#bordered-justified-home" type="button" role="tab"
                                                aria-controls="home" aria-selected="true">Colleges</button>
                                        </li>
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab"
                                                data-bs-target="#bordered-justified-profile" type="button"
                                                role="tab" aria-controls="profile" aria-selected="false"
                                                tabindex="-1">Departments</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                        <div class="tab-pane fade active show" id="bordered-justified-home"
                                            role="tabpanel" aria-labelledby="home-tab">
                                            @livewire('college-table')
                                        </div>
                                        <div class="tab-pane fade" id="bordered-justified-profile" role="tabpanel"
                                            aria-labelledby="profile-tab">
                                            @livewire('department-table')
                                        </div>
                                    </div><!-- End Bordered Tabs Justified -->

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
                <!-- End Left side columns -->

            </div>
        </section>

    </main>
</x-app-layout>
