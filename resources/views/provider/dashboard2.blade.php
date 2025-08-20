{{-- resources/views/provider/dashboard/index.blade.php --}}
@extends('provider.provider_layouts.dashboard2')

@section('page_title', 'الرئيسية')

@section('content')

    {{-- ========== بطاقات الإحصاء ========== --}}
    <div class="row g-4 mb-4">

        {{-- حجوزات الشهر --}}
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stats-info">
                    <span class="stats-label">حجوزات الشهر</span>
                    <h3 class="stats-value">{{ number_format($bookingsMonthCount ?? 0) }}</h3>
                    <span class="stats-subvalue">{{ number_format($bookingsToday ?? 0) }} اليوم</span>
                </div>
            </div>
        </div>

        {{-- إيرادات الشهر --}}
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-coins"></i></div>
                <div class="stats-info">
                    <span class="stats-label">إيرادات الشهر</span>
                    <h3 class="stats-value">{{ number_format($monthRevenue ?? 0, 0) }}&nbsp;د.ل</h3>
                    <span class="stats-subvalue">منها للمدير: {{ number_format($adminDue ?? 0, 0) }}&nbsp;د.ل</span>
                </div>
            </div>
        </div>

        {{-- متوسط التقييم --}}
        <div class="col-xl-4 col-md-6">
            <div class="stats-card">
                <div class="stats-icon"><i class="fas fa-star"></i></div>
                <div class="stats-info">
                    <span class="stats-label">متوسط التقييم</span>
                    <h3 class="stats-value">{{ number_format($avgRating ?? 0, 1) }}</h3>
                    <span class="stats-subvalue">من {{ number_format($reviewsCount ?? 0) }} مراجعة</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== الجداول ========== --}}
    <div class="row">
        {{-- جدول: الساعات المتاحة --}}
        <div class="col-lg-12">
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-clock"></i><span>الساعات المتاحة</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>من</th>
                            <th>إلى</th>
                            <th>الملعب</th>
                            <th class="text-center">حجز</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse(($closestSlots ?? []) as $i => $slot)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $slot['date'] }}</td>
                                <td>{{ $slot['from'] }}</td>
                                <td>{{ $slot['to'] }}</td>
                                <td>{{ $slot['stadium_name'] }}</td>
                                <td class="text-center">
                                    <a class="btn btn-sm btn-primary"
                                       title="حجز هذا الموعد"
                                       href="{{ route('provider.bookings.index', [
                                            'stadium_id' => $slot['stadium_id'],
                                            'date'       => $slot['date'],
                                            'from'       => $slot['from'],
                                            'to'         => $slot['to'],
                                        ]) }}">
                                        <i class="fas fa-calendar-plus"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">لا توجد مواعيد متاحة.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection
