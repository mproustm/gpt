{{-- resources/views/admin/admin_partials/admin_sidebar.blade.php --}}
<nav class="sidebar" id="sidebar">

    {{-- الشعار --}}
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <img src="/img/Admin/admin_logo_nobackground.png" class="logo-img" alt="Logo">
    </a>

    <ul class="nav flex-column sidebar-nav">

        <!-- الرئيسية -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-home nav-icon"></i>
                <span class="link-text">الرئيسية</span>
            </a>
        </li>

        <li><hr class="dropdown-divider my-2"></li>

        <!-- إدارة اللاعبين -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.players') }}">
                <i class="fas fa-users nav-icon"></i>
                <span class="link-text">إدارة اللاعبين</span>
            </a>
        </li>

        <li><hr class="dropdown-divider my-2"></li>

        <!-- إدارة الملاعب (قائمة فرعية) -->
        <li class="nav-item">
            {{-- زر يفتح / يطوي القائمة الفرعية --}}
            <a class="nav-link d-flex align-items-center collapsed"
               data-bs-toggle="collapse"
               href="#stadiumMenu"
               role="button"
               aria-expanded="false"
               aria-controls="stadiumMenu">
                <i class="fas fa-futbol nav-icon me-2"></i>
                <span class="link-text flex-grow-1">إدارة الملاعب</span>
                <i class="fas fa-chevron-down small"></i>
            </a>

            <div class="collapse" id="stadiumMenu" data-bs-parent="#sidebar">
                <ul class="nav flex-column ms-3">

                    <!-- أصحاب الملاعب -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.owners') }}">
                            <i class="fas fa-user-tie nav-icon"></i>
                            <span class="link-text">أصحاب الملاعب</span>
                        </a>
                    </li>

                    <!-- الملاعب -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.stadiums') }}">
                            <i class="fas fa-futbol nav-icon"></i>
                            <span class="link-text">الملاعب</span>
                        </a>
                    </li>

                    <!-- خدمات الملاعب -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.services') }}">
                            <i class="fas fa-concierge-bell nav-icon"></i>
                            <span class="link-text">خدمات الملاعب</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li><hr class="dropdown-divider my-2"></li>

        <!-- إدارة الشكاوي -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.support') }}">
                <i class="fas fa-exclamation-circle nav-icon"></i>
                <span class="link-text">إدارة الشكاوي</span>
            </a>
        </li>

        <li><hr class="dropdown-divider my-2"></li>

        <!-- إدارة الإشعارات -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.notifications') }}">
                <i class="fas fa-bell nav-icon"></i>
                <span class="link-text">إدارة الإشعارات</span>
            </a>
        </li>

        <li><hr class="dropdown-divider my-2"></li>

        <!-- التقارير والإحصائيات -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.reports') }}">
                <i class="fas fa-chart-line nav-icon"></i>
                <span class="link-text">التقارير والإحصائيات</span>
            </a>
        </li>

        <li><hr class="dropdown-divider my-2"></li>

        <!-- الإعدادات -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.settings') }}">
                <i class="fas fa-cogs nav-icon"></i>
                <span class="link-text">إعدادات النظام العامة</span>
            </a>
        </li>
    </ul>

    <!-- زر الطي/التوسيع لسطح المكتب -->
    <button id="collapse-btn" class="hamburger desktop-hamburger" aria-label="Collapse sidebar">
        <span></span>
    </button>
</nav>

<!-- زر إظهار/إخفاء الشريط في الموبايل -->
<button id="sidebar-toggle" class="hamburger mobile-hamburger" aria-label="Toggle sidebar">
    <span></span>
</button>
