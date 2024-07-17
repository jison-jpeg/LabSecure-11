@section('pageTitle', 'Laboratory')
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

                    {{-- Add Button --}}
                    <div class="mt-3 mb-3">
                        @if (Auth::user()->role->name === 'admin')
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                            Add Laboratory
                        </button>
                        @endif
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Computer Laboratories</h5>
                            {{-- @include('components.cards.lab') --}}

                            {{-- <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <li class="page-item {{ $laboratories->previousPageUrl() ? '' : 'disabled' }}">
                                        <a class="page-link" href="{{ $laboratories->previousPageUrl() }}" tabindex="-1"
                                            aria-disabled="true">Previous</a>
                                    </li>
                                    @for ($i = 1; $i <= $laboratories->lastPage(); $i++)
                                        <li class="page-item {{ $i == $laboratories->currentPage() ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $laboratories->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endfor
                                    <li class="page-item {{ $laboratories->nextPageUrl() ? '' : 'disabled' }}">
                                        <a class="page-link" href="{{ $laboratories->nextPageUrl() }}">Next</a>
                                    </li>
                                </ul>
                            </nav> --}}
                        </div>



                    </div>

                </div>
                <!-- End Left side columns -->

            </div>
        </section>

    </main>
</x-app-layout>
