<nav id="sidebar" class="sidebar shadow">
    <button id="sidebar-toggle" class="hamburger"><span></span></button>

    <div class="sidebar-brand text-center my-3">
        <a href="{{ route('provider.dashboard') }}">
            <img src="{{ asset('provider/provider_logo_nobackground.png') }}"
                 alt="logo"
                 class="logo-img"
                 style="max-width:140px; max-height:80px;">
        </a>
    </div>

    <ul class="nav flex-column sidebar-nav px-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}"
               href="{{ route('provider.dashboard') }}">
                <i class="nav-icon fas fa-home"></i>
                <span class="link-text">الرئيسية</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('provider.stadiums.*') ? 'active' : '' }}"
               href="{{ route('provider.stadiums.index') }}">
                <i class="nav-icon fas fa-futbol"></i>
                <span class="link-text">إدارة الملاعب</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('provider.bookings.*') ? 'active' : '' }}"
               href="{{ route('provider.bookings.index') }}">
                <i class="nav-icon fas fa-calendar-check"></i>
                <span class="link-text">إدارة الحجوزات</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('provider.reviews.*') ? 'active' : '' }}"
               href="{{ route('provider.reviews.index') }}">
                <i class="nav-icon fas fa-star"></i>
                <span class="link-text">التقييمات</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('provider.reports.*') ? 'active' : '' }}"
               href="{{ route('provider.reports.index') }}">
                <i class="nav-icon fas fa-chart-line"></i>
                <span class="link-text">إدارة التقارير</span>
            </a>
        </li>
        <li class="sidebar-divider my-2"></li>
    </ul>
</nav>

<button id="sidebar-toggle" class="hamburger d-lg-none"><span></span></button>
<div id="mobile-overlay" class="mobile-overlay"></div>
