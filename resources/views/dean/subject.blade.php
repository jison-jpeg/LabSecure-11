@section('pageTitle', 'Subjects')
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

                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title">Subject Management</h5>
                                    </div>

                                    {{-- User Table Livewire --}}
                                    @livewire('subject-table')
                                    <!-- End User Table Livewire -->

                            </div>
                        </div>
                    </div>

                </div>
                <!-- End Left side columns -->

            </div>
        </section>

    </main>
</x-app-layout>
