@section('pageTitle', 'View Class')
<x-app-layout>
    <main id="main" class="main">
        {{-- Dynamic Page Breadcrumbs --}}
        <div class="pagetitle">
            <h1>Hello, {{ Auth::user()->first_name }}! 👋</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url()->previous() }}">@yield('pageTitle')</a></li>
                    <li class="breadcrumb-item active">{{ $schedule->schedule_code }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Title -->

        @livewire('view-schedule', ['schedule' => $schedule])

    </main>
</x-app-layout>
