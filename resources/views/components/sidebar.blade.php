<div>
    <aside id="sidebar" class="sidebar">
        <i class="bi bi-x-lg toggle-sidebar-btn d-block d-sm-block d-md-block d-lg-block d-xl-none"></i>

        <div class="sidebar-header">
            <a href="#">
                <img src="{{ asset('assets/img/logo.png') }}" class="sidebar-logo" alt="" />
            </a>
        </div>

        <ul class="sidebar-nav" id="sidebar-nav">
            <li class="nav-heading">HOME</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('dashboard*') ? '' : 'collapsed' }}" href="{{ url('/dashboard') }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-heading">MENU</li>
            @if (Auth::user()->role->name !== 'student')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('laboratories*') ? '' : 'collapsed' }}"
                    href="{{ url('/laboratories') }}">
                    <i class="bi bi-view-stacked"></i>
                    <span>Laboratories</span>
                </a>
            </li>
            @endif

            @if (Auth::user()->role->name === 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('users*') ? '' : 'collapsed' }}" href="{{ url('/users') }}">
                        <i class="bi bi-people"></i>
                        <span>Users</span>
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <a class="nav-link {{ request()->is('attendances*') ? '' : 'collapsed' }}"
                    href="{{ url('/attendances') }}">
                    <i class="bi bi-clipboard2-check"></i>
                    <span>Attendances</span>
                </a>
            </li>

            @if (Auth::user()->role->name !== 'student')            
            <li class="nav-heading">INSTITUTION</li>

            @if (Auth::user()->role->name === 'admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('courses*') ? '' : 'collapsed' }}" href="{{ url('/courses') }}">
                    <i class="bi bi-bank"></i>
                    <span>Courses</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->is('faculties*') ? '' : 'collapsed' }}"
                    href="{{ url('/faculties') }}">
                    <i class="bi bi-mortarboard"></i>
                    <span>Faculties</span>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link {{ request()->is('students*') ? '' : 'collapsed' }}" href="{{ url('/students') }}">
                    <i class="bi bi-person-workspace"></i>
                    <span>Students</span>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link {{ request()->is('subjects*') ? '' : 'collapsed' }}" href="{{ url('/subjects') }}">
                    <i class="bi bi-book"></i>
                    <span>Subjects</span>
                </a>
            </li>

            {{-- @if (Auth::user()->role->name === 'admin') --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->is('schedules*') ? '' : 'collapsed' }}"
                    href="{{ url('/schedules') }}">
                    <i class="bi bi-calendar2-week"></i>
                    <span>Schedules</span>
                </a>
            </li>
            {{-- @endif --}}

            @if (Auth::user()->role->name === 'instructor')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('classes*') ? '' : 'collapsed' }}"
                    href="{{ url('/classes') }}">
                    <i class="bi bi-border-outer"></i>
                    <span>Classes</span>
                </a>
            </li>
            @endif

            @if (Auth::user()->role->name === 'admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('sections*') ? '' : 'collapsed' }}"
                    href="{{ url('/sections') }}">
                    <i class="bi bi-border-outer"></i>
                    <span>Sections</span>
                </a>
            </li>
            @endif

            <li class="nav-heading">USER</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('profile') ? '' : 'collapsed' }}" href="{{ url('/profile') }}">
                    <i class="bi bi-person"></i>
                    <span>Profile</span>
                </a>
            </li>

            @if (Auth::user()->role->name === 'admin')
            <li class="nav-heading">SETTINGS</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('logs') ? '' : 'collapsed' }}" href="{{ url('/logs') }}">
                    <i class="bx bx-detail"></i>
                    <span>Logs</span>
                </a>
            </li>
            @endif

            {{-- @if (Auth::user()->role !== 'admin')
            <li class="nav-item">
                <a class="nav-link {{ request()->is('reports') ? '' : 'collapsed' }}" href="{{ url('/reports') }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Reports</span>
                </a>
            </li>
            @endif --}}
        </ul>
    </aside>

</div>
