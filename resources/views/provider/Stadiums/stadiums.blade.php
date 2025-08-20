{{-- resources/views/provider/Stadiums/stadiums.blade.php --}}
@extends('provider.provider_layouts.dashboard2')

@section('page_title', 'إدارة الملاعب')

@section('content')
    <!-- فلتر الحالة -->
    <form method="GET" action="" class="filter-form d-flex align-items-center gap-3 mb-3">
        <label for="status" class="fw-bold mb-0">فلترة حسب الحالة:</label>
        <select name="status" id="status" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">الكل</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>نشط</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>غير نشط</option>
        </select>
    </form>

    <!-- جدول الملاعب -->
    <div class="content-card p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
                        <th>الملعب</th>
                        <th>العنوان</th>
                        <th>نوع الأرضية</th>
                        <th style="min-width:170px;">الوصف</th>
                        <th>السعر/ساعة</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stadiums as $index => $stadium)
                        <tr class="text-center">
                            <td>{{ $index + 1 }}</td>
                            
                            <td>{{ $stadium['name'] }}</td>
                            <td>{{ $stadium['location'] }}</td>
                            <td>{{ $stadium['type'] }}</td>
                            <td class="text-truncate" style="max-width:200px;">{{ $stadium['description'] }}</td>
                            <td>{{ $stadium['price'] }} د.ل</td>
                            <td>
                                @if($stadium['status'] === 'active')
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-danger d-block">غير نشط</span>
                                    <small class="text-muted d-block mt-1">
                                        @if(isset($stadium['status_changed_by']) && $stadium['status_changed_by'] === 'admin')
                                            معطل من قبل الإدارة
                                        @elseif(isset($stadium['reason']))
                                            {{ $stadium['reason'] }}
                                        @else
                                            بدون سبب
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($stadium['status'] === 'active')
                                    <!-- زر التعطيل -->
                                    <button class="btn btn-sm btn-outline-danger open-disable-modal"
                                            data-id="{{ $stadium['id'] }}"
                                            data-name="{{ $stadium['name'] }}">
                                        <i class="fas fa-ban"></i> تعطيل
                                    </button>
                                @else
                                    @if(isset($stadium['status_changed_by']) && $stadium['status_changed_by'] === 'admin')
                                        <span class="text-danger small">لا يمكنك تفعيل هذا الملعب (معطل من الإدارة)</span>
                                    @else
                                        <!-- زر التفعيل -->
                                        <form action="{{ route('provider.stadiums.toggle', $stadium['id']) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check"></i> تفعيل
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">لا توجد ملاعب متاحة حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal اختيار سبب التعطيل -->
    <div class="modal fade" id="disableModal" tabindex="-1" aria-labelledby="disableModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="disableModalLabel">تعطيل ملعب</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="disableForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <p class="mb-2">
                            اختر سبب التعطيل للملعب <strong id="stadiumName"></strong>:
                        </p>
                        <select name="reason" class="form-select" required>
                            <option value="" disabled selected>سبب التعطيل</option>
                            <option value="صيانة">صيانة</option>
                            <option value="فعالية خاصة">فعالية خاصة</option>
                            <option value="أعمال تطوير">أعمال تطوير</option>
                            <option value="ظروف جوية">ظروف جوية</option>
                            <option value="أخرى">أخرى</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-danger">تأكيد التعطيل</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalEl   = document.getElementById('disableModal');
    const stadiumNm = document.getElementById('stadiumName');
    const form      = document.getElementById('disableForm');
    const bsModal   = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.open-disable-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const id   = btn.dataset.id;
            const name = btn.dataset.name;

            stadiumNm.textContent = name;
            form.action = `/provider/stadiums/${id}`;   // PATCH route
            bsModal.show();
        });
    });
});
</script>
@endpush
