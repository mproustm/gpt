<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    {{-- تفعيل الوضع الداكن قبل تحميل الـ CSS --}}
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>مزود – @yield('title','لوحة التحكم')</title>

    {{-- مكتبات خارجية --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- النُّسق الموحد للمزوّد --}}
    <link rel="stylesheet" href="{{ asset('css/provider.css') }}">

    @stack('styles')
</head>
<body>

<div class="wrapper">

    {{-- الشريط الجانبي --}}
    @include('provider.provider_partials.provider_sidebar')

    {{-- زر إظهار الشريط في الموبايل (نفس الموجود داخل الـ sidebar) --}}
    <button id="sidebar-toggle" class="sidebar-toggle-btn d-lg-none" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>
    <div id="mobile-overlay" class="mobile-overlay"></div>

    {{-- التوب بار + المحتوى --}}
    <main class="main-content">
        @include('provider.provider_partials.provider_topbar')
        @yield('content')
    </main>

</div>

{{-- سكربتات --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/provider.js') }}"></script>
@stack('scripts')
</body>
</html>
