<div>
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="logo d-flex align-items-center">
                <img src="{{ asset('assets/img/logo.png') }}" alt="">
                <span class="d-none d-lg-block">{{ config('app.name', 'Laravel') }}</span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div><!-- End Logo -->

        <x-search-bar />

        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">
                {{-- <x-notifications /> --}}
                {{-- <x-messages /> --}}
                <x-profile />
            </ul>
        </nav>
    </header>
</div>
