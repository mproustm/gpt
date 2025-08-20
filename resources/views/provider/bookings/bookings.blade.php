{{-- resources/views/provider/bookings/bookings.blade.php --}}
@extends('provider.provider_layouts.dashboard2')

@section('title', 'إدارة الحجوزات')
@section('page_title', 'إدارة الحجوزات')

@section('content')

    {{-- رسائل الجلسة --}}
    @foreach (['success','error'] as $msg)
        @if (session($msg))
            <div class="alert alert-{{$msg === 'success' ? 'success' : 'danger'}} alert-dismissible fade show" role="alert">
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    {{-- تبويبات --}}
    <ul class="nav nav-pills mb-4" id="bookingTab">
        <li class="nav-item">
            <button class="nav-link {{ !request('tab') ? 'active' : '' }}"
                    data-bs-toggle="tab" data-bs-target="#addBooking">
                إضافة حجز
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request('tab')==='view' || request()->has('player') ? 'active' : '' }}"
                    data-bs-toggle="tab" data-bs-target="#viewBookings">
                عرض الحجوزات
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request('tab')==='archived' ? 'active' : '' }}"
                    data-bs-toggle="tab" data-bs-target="#archivedBookings">
                الحجوزات المؤرشفة
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- إضافة حجز --}}
        <div class="tab-pane fade {{ !request('tab') ? 'show active' : '' }}" id="addBooking">
            <div class="content-card">
                <form method="POST" action="{{ route('provider.bookings.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم اللاعب</label>
                            <input name="player_name" type="text" class="form-control"
                                   placeholder="مثال: أحمد محمد" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اختر الملعب</label>
                            <select name="stadium_id" id="stadium-id" class="form-select" required>
                                @foreach ($stadiums as $s)
                                    <option value="{{ $s->id }}"
                                        {{ (string) $s->id === (string) request('stadium_id') ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">التاريخ</label>
                            <input name="date" id="res-date" type="date"
                                   value="{{ request('date', now()->toDateString()) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">من</label>
                            <select name="from" id="time-from" class="form-select" required></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">إلى</label>
                            <input name="to" id="time-to" type="text"
                                   value="{{ request('to') }}"
                                   class="form-control" readonly required>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> إضافة الحجز
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- عرض الحجوزات --}}
        <div class="tab-pane fade {{ request('tab')==='view' || request()->has('player') ? 'show active' : '' }}"
             id="viewBookings">

            <div class="content-card mb-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">بحث باسم اللاعب</label>
                        <input id="booking-search-input" type="text"
                               class="form-control form-control-sm"
                               placeholder="ابدأ الكتابة للبحث...">
                    </div>

                    <!-- قائمة التصفية بالحالة -->
                    <div class="col-md-4">
                        <label class="form-label">تصفية حسب الحالة</label>
                        <select id="status-filter" class="form-select form-select-sm">
                            <option value="">الكل</option>
                            <option value="confirmed">مؤكد</option>
                            <option value="pending">معلّق</option>
                            <option value="playing">جارية</option>
                            <option value="finished">مكتمل</option>
                            <option value="cancelled">ملغاة</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>اسم اللاعب</th>
                                <th>الملعب</th>
                                <th>التاريخ</th>
                                <th>من</th>
                                <th>إلى</th>
                                <th>الحالة</th>
                                <th>الإجراء</th>
                            </tr>
                        </thead>
                        @php
                          $statusMap = [
                            'finished'  => 'مكتمل',
                            'cancelled' => 'ملغاة',
                            'playing'   => 'جارية',
                            'pending'   => 'معلّق',
                            'confirmed' => 'مؤكد',
                          ];
                        @endphp
                        <tbody id="booking-table-body">
                            @forelse ($reservations as $b)
                                @php
                                  $rowStatus = $b['status'] ?? '';
                                  $rowStatus = $rowStatus === 'canceled' ? 'cancelled' : $rowStatus;
                                  $statusLabel = $statusMap[$rowStatus] ?? $rowStatus;
                                @endphp
                                <tr class="text-center" data-status="{{ $rowStatus }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $b['player'] }}</td>
                                    <td>{{ $b['stadium'] }}</td>
                                    <td>{{ $b['date'] }}</td>
                                    <td>{{ $b['from'] }}</td>
                                    <td>{{ $b['to'] }}</td>
                                    <td>{{ $statusLabel }}</td>
                                    <td>
                                        @if ($b['type'] === 'player')
                                            @if ($b['tor'] === 'مبدئي')
                                                <form method="POST" action="{{ route('provider.bookings.archive', $b['id']) }}" style="display:inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من أرشفة هذا الحجز؟')">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                </form>
                                            @elseif ($b['tor'] === 'كامل')
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    لا يمكن الأرشفة
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-warning" disabled>
                                                    غير معروف
                                                </button>
                                            @endif
                                        @else
                                            {{-- Owner reservation: always allow archiving --}}
                                            <form method="POST" action="{{ route('provider.bookings.archive', $b['id']) }}" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('هل أنت متأكد من أرشفة هذا الحجز؟')">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">لا توجد حجوزات.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- الحجوزات المؤرشفة --}}
        <div class="tab-pane fade {{ request('tab')==='archived' ? 'show active' : '' }}"
             id="archivedBookings">

            <div class="content-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>اسم اللاعب</th>
                                <th>الملعب</th>
                                <th>تاريخ الأرشفة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($archived ?? [] as $row)
                                <tr class="text-center">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['player'] }}</td>
                                    <td>{{ $row['stadium'] }}</td>
                                    <td>{{ $row['archived_at'] }}</td>
                                    <td>
                                        @if ($row['player_id'])
                                            <button type="button" class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#complaintModal"
                                                    data-player-id="{{ $row['player_id'] }}"
                                                    data-player-name="{{ e($row['player']) }}">
                                                <i class="fas fa-flag"></i> شكوى
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        لا توجد حجوزات مؤرشفة.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- Complaint Modal --}}
    <div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="complaintModalLabel">تقديم شكوى ضد لاعب</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="POST" action="{{ route('provider.complaints.store') }}">
              @csrf
              <div class="modal-body">
                  <p>أنت على وشك تقديم شكوى ضد اللاعب: <strong id="modal-player-name"></strong></p>
                  <input type="hidden" name="player_id" id="modal-player-id">
                  <div class="mb-3">
                      <label for="complaint-message" class="form-label">سبب الشكوى</label>
                      <textarea class="form-control" id="complaint-message" name="message" rows="4" required placeholder="اشرح سبب الشكوى بالتفصيل..."></textarea>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-danger">إرسال الشكوى</button>
              </div>
          </form>
        </div>
      </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const stadSel = document.getElementById('stadium-id');
    const dateSel = document.getElementById('res-date');
    const fromSel = document.getElementById('time-from');
    const toInput = document.getElementById('time-to');

    // Read prefill from query params
    const params = new URLSearchParams(location.search);
    const preStadium = params.get('stadium_id');
    const preDate    = params.get('date');
    const preFrom    = params.get('from');
    const preTo      = params.get('to');

    if (preStadium) stadSel.value = preStadium;
    if (preDate)    dateSel.value = preDate;
    if (preTo)      toInput.value = preTo;

    // label like 5:00, 6:00 ... for 17:00..23:00
    function toFriendlyLabel(hhmm) {
        const h = parseInt(hhmm.split(':')[0], 10);
        const h12 = ((h + 11) % 12) + 1; // 17 -> 5, 18 -> 6, ... 23 -> 11
        return `${h12}:00`;
    }

    async function loadSlots() {
        fromSel.innerHTML = '<option>تحميل...</option>';
        const q = new URLSearchParams({ stadium_id: stadSel.value, date: dateSel.value });
        const res = await fetch(`/provider/available-slots?${q}`);
        const freeSlots = await res.json(); // e.g. ['17:00','18:00',...]

        fromSel.innerHTML = '';
        if (!freeSlots.length) {
            fromSel.innerHTML = '<option disabled>لا أوقات متاحة</option>';
            toInput.value = '';
            return;
        }
        freeSlots.forEach(t => fromSel.add(new Option(toFriendlyLabel(t), t)));

        // If we came with a suggested slot, select it
        if (preFrom && freeSlots.includes(preFrom)) {
            fromSel.value = preFrom;
            if (!toInput.value) {
                const hh = (parseInt(preFrom.split(':')[0], 10) + 1) % 24;
                toInput.value = `${hh.toString().padStart(2, '0')}:00`;
            }
        } else {
            const start = fromSel.value || freeSlots[0];
            const hh = (parseInt(start.split(':')[0], 10) + 1) % 24;
            toInput.value = `${hh.toString().padStart(2, '0')}:00`;
        }
    }

    fromSel.addEventListener('change', () => {
        const start = fromSel.value;
        if (!start) { toInput.value = ''; return; }
        const hh = (parseInt(start.split(':')[0], 10) + 1) % 24; // wrap at midnight
        toInput.value = `${hh.toString().padStart(2, '0')}:00`;
    });

    stadSel.addEventListener('change', loadSlots);
    dateSel.addEventListener('change', loadSlots);
    loadSlots();

    // filter in "عرض الحجوزات"
    const input = document.getElementById('booking-search-input');
    const tbody = document.getElementById('booking-table-body');
    const statusSel = document.getElementById('status-filter');
    let timer;

    const initialStatus = params.get('status') || '';
    if (statusSel && initialStatus) statusSel.value = initialStatus;

    let searchQ = '';
    let statusQ = statusSel ? statusSel.value : '';

    function applyFilters() {
        tbody.querySelectorAll('tr').forEach(tr => {
            const name = tr.cells[1]?.innerText.toLowerCase() || '';
            const rowStatus = tr.getAttribute('data-status') || '';
            const matchesSearch = !searchQ || name.includes(searchQ);
            const matchesStatus = !statusQ || rowStatus === statusQ;
            tr.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    input?.addEventListener('keyup', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            searchQ = input.value.trim().toLowerCase();
            applyFilters();
        }, 250);
    });

    statusSel?.addEventListener('change', () => {
        statusQ = statusSel.value;
        applyFilters();
    });

    // complaint modal
    const complaintModal = document.getElementById('complaintModal');
    if (complaintModal) {
        complaintModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const playerId = button.getAttribute('data-player-id');
            const playerName = button.getAttribute('data-player-name');

            complaintModal.querySelector('#modal-player-name').textContent = playerName;
            complaintModal.querySelector('#modal-player-id').value = playerId;
        });
    }

    // initial filter pass
    applyFilters();
});
</script>
@endpush
