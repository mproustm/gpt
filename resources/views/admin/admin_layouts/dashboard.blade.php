<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','لوحة التحكم')</title>

    <!-- مكتبات خارجية -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- CSS الموحد -->
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @stack('styles')
</head>
<body>
<div class="wrapper">

@include('admin.admin_partials.admin_sidebar')

    <button id="sidebar-toggle" class="sidebar-toggle-btn" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <div id="mobile-overlay" class="mobile-overlay"></div>

    <main class="main-content">
@include('admin.admin_partials.admin_topbar')
        @yield('content')
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@stack('scripts')
</body>
</html>
