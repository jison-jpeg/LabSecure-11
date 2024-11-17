@section('pageTitle', 'Dashboard')
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
                <div class="col-lg-8 d-flex flex-column">
                    

                    {{-- User Stats --}}
                    @livewire('user-stats')

                    {{-- Lab Stats --}}
                    <div class="card h-100">
                        @livewire('lab-stats')
                    </div>
                    {{-- @livewire('attendance-rank') --}}


                </div>
                <!-- End Left side columns -->

                <!-- Right side columns -->
                <div class="col-lg-4 d-flex flex-column">
                    <!-- Recent Activity -->
                    <div class="card h-100">
                        @livewire('logs-widget')
                    </div>
                    <!-- End Recent Activity -->
                </div>
                <!-- End Right side columns -->


            </div>
        </section>

    </main>
</x-app-layout>
