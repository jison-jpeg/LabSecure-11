@section('pageTitle', 'Subject Attendance')
<x-app-layout>
    <main id="main" class="main">
        {{-- Dynamic Page Breadcrumbs --}}
        <div class="pagetitle">
            <h1>@yield('pageTitle')</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ url()->previous() }}">@yield('pageTitle')</a></li>
                    <li class="breadcrumb-item active">{{ $schedule->subject->name }}</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Title -->

        @livewire('instructor-subject-attendance', ['schedule' => $schedule])

    </main>
</x-app-layout>