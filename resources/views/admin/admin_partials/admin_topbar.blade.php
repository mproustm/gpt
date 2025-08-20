<header class="topbar">
    <!-- Page Title -->
    <span class="page-title">@yield('title')</span>

    <!-- Theme Toggle -->
    <button id="theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- User Dropdown -->
    <div class="dropdown user-dropdown position-relative">
        <button class="btn dropdown-toggle p-0 d-flex align-items-center"
                id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">

            @if(auth()->user()->pic)
                <img src="{{ asset('storage/' . auth()->user()->pic) }}"
                     class="user-avatar rounded-circle me-2"
                     style="width:40px; height:40px; object-fit:cover;"
                     alt="{{ auth()->user()->name }}">
            @else
                <img src="{{ asset('storage/default-avatar.png') }}"
                     class="user-avatar rounded-circle me-2"
                     style="width:40px; height:40px; object-fit:cover;"
                     alt="Default Avatar">
            @endif

            <span class="user-name">{{ auth()->user()->name }}</span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 280px;">
            <!-- notifications... -->
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                    <i class="fas fa-user me-1"></i> الملف الشخصي
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt me-1"></i> تسجيل الخروج
                </a>
            </li>
        </ul>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</header>
