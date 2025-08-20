@extends('provider.provider_layouts.dashboard2')

@section('page_title', 'التقييمات والمراجعات')

@section('content')
    @php
        $reviews = $reviews ?? collect();
        $avg = $avg ?? 0;
    @endphp

    <div class="stats-card mb-4" style="max-width:260px;">
        <div class="stats-icon"><i class="fas fa-star"></i></div>
        <div class="stats-info">
            <span class="stats-label d-block mb-1">متوسط التقييم</span>
            <span class="stats-value d-block">
                {{ $avg }} <span class="text-muted fs-6">/ 5</span>
            </span>
            <div class="star-rating fs-6 mt-1 text-warning" dir="ltr">
                @php
                    $full    = floor($avg);
                    $decimal = $avg - $full;
                    $hasHalf = $decimal >= 0.25 && $decimal < 0.75;
                    $empty   = 5 - $full - ($hasHalf ? 1 : 0);
                @endphp
                @for ($i = 0; $i < $full; $i++)
                    <i class="fas fa-star"></i>
                @endfor
                @if ($hasHalf)
                    <i class="fas fa-star-half-alt"></i>
                @endif
                @for ($i = 0; $i < $empty; $i++)
                    <i class="far fa-star"></i>
                @endfor
            </div>
        </div>
    </div>

    <div class="content-card">
        <h4 class="mb-3 d-flex align-items-center gap-2">
            <i class="fas fa-comments"></i>
            التقييمات والمراجعات
        </h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>الملعب</th>
                        <th>اللاعب</th>
                        <th>التقييم</th>
                        <th>المراجعة</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($reviews as $i => $review)
    <tr class="text-center">
        <td>{{ $i + 1 }}</td>
        <td>{{ $review->stadium->name ?? '-' }}</td>
        <td>{{ $review->player->name ?? '-' }}</td>
        <td>
            <span class="badge bg-success">{{ $review->rating_number }} / 5</span>
        </td>
        <td class="text-truncate" style="max-width:180px;">{{ $review->description }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($review->timestamp)->format('Y-m-d') }}</td>
    </tr>
@endforeach

                </tbody>
            </table>
        </div>
    </div>
@endsection
