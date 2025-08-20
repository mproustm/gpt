{{-- resources/views/admin/dashboard/index.blade.php --}}
@extends('admin.admin_layouts.dashboard')

@section('content')
    {{-- Fallbacks عشان لو الصفحة اتفتحت من غير المتغيرات لأي سبب --}}
    @php
        $totalPlayers          = $totalPlayers          ?? 0;
        $activePlayersCount    = $activePlayersCount    ?? 0;
        $totalStadiums         = $totalStadiums         ?? 0;
        $availableStadiums     = $availableStadiums     ?? 0;
        $totalReservations     = $totalReservations     ?? 0;
        $reservationsThisMonth = $reservationsThisMonth ?? 0;
        $newUsersThisMonth     = $newUsersThisMonth     ?? 0;
        $adminRevenueTotal     = $adminRevenueTotal     ?? 0;
        $adminRevenueMonth     = $adminRevenueMonth     ?? 0;
        $topPlayers            = $topPlayers            ?? [];
        $topStadiums           = $topStadiums           ?? [];
    @endphp

    {{-- ======= بطاقات الإحصاءات ======= --}}
    <div class="row g-4 mb-4">

        {{-- إجمالي اللاعبين --}}
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon bg-players"><i class="fas fa-users"></i></div>
                <div class="stats-info">
                    <span class="stats-label">إجمالي اللاعبين</span>
                    <h3 class="stats-value">{{ number_format($totalPlayers) }}</h3>
                    <span class="stats-subvalue">{{ number_format($activePlayersCount) }} لاعب نشط (آخر 30 يومًا)</span>
                </div>
            </div>
        </div>

        {{-- إجمالي الملاعب --}}
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon bg-stadiums"><i class="fas fa-futbol"></i></div>
                <div class="stats-info">
                    <span class="stats-label">إجمالي الملاعب</span>
                    <h3 class="stats-value">{{ number_format($totalStadiums) }}</h3>
                    <span class="stats-subvalue">{{ number_format($availableStadiums) }} ملعب متاح</span>
                </div>
            </div>
        </div>

        {{-- إجمالي الحجوزات --}}
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon bg-bookings"><i class="fas fa-calendar-check"></i></div>
                <div class="stats-info">
                    <span class="stats-label">إجمالي الحجوزات</span>
                    <h3 class="stats-value">{{ number_format($totalReservations) }}</h3>
                    <span class="stats-subvalue">{{ number_format($reservationsThisMonth) }} هذا الشهر</span>
                </div>
            </div>
        </div>

        {{-- مستخدمون جدد --}}
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon bg-stadiums"><i class="fas fa-user-plus"></i></div>
                <div class="stats-info">
                    <span class="stats-label">مستخدمون جدد</span>
                    <h3 class="stats-value">{{ number_format($newUsersThisMonth) }}</h3>
                    <span class="stats-subvalue">منذ بداية الشهر</span>
                </div>
            </div>
        </div>

        {{-- *** تمت إزالة بطاقة "عدد الشكاوي" بناءً على طلبك *** --}}

        {{-- إجمالي إيرادات المشرف (10%) --}}
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon bg-players"><i class="fas fa-coins"></i></div>
                <div class="stats-info">
                    <span class="stats-label">إجمالي الإيرادات (عمولة المشرف 10%)</span>
                    <h3 class="stats-value">{{ number_format($adminRevenueTotal, 2) }}&nbsp;د.ل</h3>
                    <span class="stats-subvalue">{{ number_format($adminRevenueMonth, 2) }}&nbsp;د.ل هذا الشهر</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ======= الجداول ======= --}}
    <div class="row">
        {{-- أكثر اللاعبين حجزاً --}}
        <div class="col-lg-6">
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-user"></i><span>أكثر اللاعبين حجزاً</span>
                </div>
                <table class="table table-hover">
                    <thead>
                        <tr><th>#</th><th>اسم اللاعب</th><th>عدد الحجوزات</th></tr>
                    </thead>
                    <tbody>
                        @forelse($topPlayers as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $p['name'] }}</td>
                                <td>{{ number_format($p['count']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">لا توجد بيانات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- أكثر الملاعب حجزاً --}}
        <div class="col-lg-6">
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-star"></i><span>أكثر الملاعب حجزاً</span>
                </div>
                <table class="table table-hover">
                    <thead>
                        <tr><th>#</th><th>اسم الملعب</th><th>عدد الحجوزات</th></tr>
                    </thead>
                    <tbody>
                        @forelse($topStadiums as $i => $s)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $s['name'] }}</td>
                                <td>{{ number_format($s['count']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">لا توجد بيانات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
