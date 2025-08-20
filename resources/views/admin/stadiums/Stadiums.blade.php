{{-- resources/views/admin/stadiums/stadiums.blade.php --}}
@extends('admin.admin_layouts.dashboard')

@php($title = 'الملاعب')

@section('content')
    {{-- تنبيهات --}}
    @foreach (['success'=>'check-circle','error'=>'times-circle'] as $k=>$icon)
        @if(session($k))
            <div class="alert alert-{{ $k=='success'?'success':'danger' }} alert-dismissible fade show mb-4">
                <i class="fas fa-{{ $icon }} me-1"></i> {{ session($k) }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach
    {{-- تنبيه AJAX (يُملأ عبر JS) --}}
    <div id="ajax-flash" class="alert d-none alert-success alert-dismissible fade show mb-4">
        <i class="fas fa-check-circle me-1"></i> <span></span>
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="content-card">
        <div class="content-card-header">
            <i class="fas fa-futbol me-2"></i>قائمة الملاعب
        </div>

        {{-- شريط البحث --}}
        <div class="mb-3">
            <input id="stadium-search" type="text"
                   class="form-control"
                   style="width:50%;"  {{-- نصف العرض --}}
                   placeholder="ابحث باسم الملعب…">
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="stadiums-table">
                <thead>
                    <tr>
                        <th>اسم الملعب</th>
                        <th>مالك الملعب</th>
                        <th>العنوان</th>
                        <th>الوصف</th>
                        <th>الحالة</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stadiums as $s)
                        <tr data-id="{{ $s->id }}">
                            <td>{{ $s->name }}</td>
                            <td>{{ optional($s->owner)->name ?? '—' }}</td>
                            <td>{{ $s->address }}</td>
                            <td>{{ $s->description }}</td>
                            <td>
                                <span class="status-badge {{ $s->status ? 'status-active' : 'status-inactive' }}">
                                    {{ $s->status ? 'نشط' : 'معطّل' }}
                                </span>
                            </td>
                            <td class="text-center">
                                {{-- زر التعديل (يفتح مودال) --}}
                                <button type="button"
                                        class="btn btn-sm btn-primary me-1 btn-edit"
                                        data-id="{{ $s->id }}" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- زر تفعيل/تعطيل --}}
                                <form method="POST" action="{{ route('admin.stadiums') }}"
                                      class="d-inline toggle-status">
                                    @csrf
                                    <input type="hidden"
                                           name="{{ $s->status ? 'deactivate_id' : 'activate_id' }}"
                                           value="{{ $s->id }}">
                                    <button class="btn btn-sm {{ $s->status ? 'btn-warning' : 'btn-success' }}"
                                            title="{{ $s->status ? 'تعطيل' : 'تفعيل' }}">
                                        <i class="fas {{ $s->status ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">لا توجد بيانات</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{ $stadiums->withQueryString()->links() }}
        </div>
    </div>

    {{-- المودال: تعديل ملعب (بدون حقل المالك) --}}
    <div class="modal fade" id="editStadiumModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="edit-stadium-form" method="POST" action="{{ route('admin.stadiums') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="edit_id" id="edit-id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>تعديل بيانات الملعب</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            {{-- أُزيل حقل مالك الملعب --}}
                            <div class="col-md-6">
                                <label class="form-label">اسم الملعب</label>
                                <input type="text" name="name" id="edit-name" class="form-control" required maxlength="100">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">السعر</label>
                                <input type="number" step="0.01" name="price" id="edit-price" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">العنوان</label>
                                <input type="text" name="address" id="edit-address" class="form-control" required maxlength="255">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">رابط الموقع (اختياري)</label>
                                <input type="url" name="location_url" id="edit-location_url" class="form-control" maxlength="255" placeholder="https://maps.google.com/...">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">الوصف</label>
                                <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label d-block">الحالة</label>
                                <select name="status" id="edit-status" class="form-select" required>
                                    <option value="1">نشط</option>
                                    <option value="0">معطّل</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label d-block">صورة الملعب (اختياري)</label>
                                <input type="file" name="stad_pic" class="form-control" accept="image/*">
                                <small id="current-pic" class="text-muted d-block mt-1"></small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>حفظ
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.status-badge { padding:4px 10px; border-radius:8px; font-size:.75rem; }
.status-active  { background:#d1e7dd; color:#0f5132; }
.status-inactive{ background:#f8d7da; color:#842029; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('stadium-search');
    const tableBody = document.querySelector('#stadiums-table tbody');
    const editBase  = `{{ route('admin.stadiums') }}`;
    const modalEl   = document.getElementById('editStadiumModal');
    const modal     = new bootstrap.Modal(modalEl);

    const form      = document.getElementById('edit-stadium-form');
    const flashBox  = document.getElementById('ajax-flash');
    const flashSpan = flashBox.querySelector('span');

    // عناصر النموذج
    const f_id     = document.getElementById('edit-id');
    const f_name   = document.getElementById('edit-name');
    const f_price  = document.getElementById('edit-price');
    const f_addr   = document.getElementById('edit-address');
    const f_loc    = document.getElementById('edit-location_url');
    const f_desc   = document.getElementById('edit-description');
    const f_status = document.getElementById('edit-status');
    const f_pic    = document.querySelector('input[name="stad_pic"]');
    const f_picInfo= document.getElementById('current-pic');

    const showSuccess = (msg='تم الحفظ بنجاح') => {
        flashSpan.textContent = msg;
        flashBox.classList.remove('d-none');
        setTimeout(() => flashBox.classList.add('d-none'), 3000);
    };

    // بحث (Debounce)
    let timer;
    input.addEventListener('keyup', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            fetch(`${editBase}?q=${encodeURIComponent(input.value)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                tableBody.innerHTML = '';
                if (!data.length) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">لا توجد بيانات</td></tr>';
                    return;
                }
                data.forEach(s => {
                    tableBody.innerHTML += `
                        <tr data-id="${s.id}">
                            <td>${s.name ?? ''}</td>
                            <td>${s.owner ?? '—'}</td>
                            <td>${s.address ?? ''}</td>
                            <td>${s.description ?? ''}</td>
                            <td>
                                <span class="status-badge ${s.status=='نشط'?'status-active':'status-inactive'}">
                                    ${s.status}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-sm btn-primary me-1 btn-edit"
                                        data-id="${s.id}" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.stadiums') }}" class="d-inline toggle-status">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <input type="hidden" name="${s.status=='نشط'?'deactivate_id':'activate_id'}" value="${s.id}">
                                    <button class="btn btn-sm ${s.status=='نشط'?'btn-warning':'btn-success'}"
                                            title="${s.status=='نشط'?'تعطيل':'تفعيل'}">
                                        <i class="fas ${s.status=='نشط'?'fa-toggle-off':'fa-toggle-on'}"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>`;
                });
            });
        }, 300);
    });

    // تفويض حدث: إرسال فورم التفعيل/التعطيل بعد البناء الديناميكي
    tableBody.addEventListener('submit', async (ev) => {
        if (ev.target.matches('form.toggle-status')) {
            ev.preventDefault();
            const fd = new FormData(ev.target);
            await fetch(ev.target.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            showSuccess('تم تحديث حالة الملعب');
            input.dispatchEvent(new Event('keyup'));
        }
    });

    // تفويض حدث: فتح المودال وجلب بيانات الملعب
    tableBody.addEventListener('click', async (ev) => {
        const btn = ev.target.closest('.btn-edit');
        if (!btn) return;
        const id = btn.getAttribute('data-id');
        const r  = await fetch(`${editBase}?fetch_id=${encodeURIComponent(id)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const s = await r.json();

        // تعبئة الحقول (بدون مالك الملعب)
        f_id.value      = s.id;
        f_name.value    = s.name ?? '';
        f_price.value   = s.price ?? '';
        f_addr.value    = s.address ?? '';
        f_loc.value     = s.location_url ?? '';
        f_desc.value    = s.description ?? '';
        f_status.value  = String(s.status ?? 1);
        f_pic.value     = '';
        f_picInfo.innerHTML = s.stad_pic_url
            ? `الصورة الحالية: <a href="${s.stad_pic_url}" target="_blank">عرض</a>`
            : '';

        modal.show();
    });

    // حفظ التعديل (AJAX)
    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        const fd = new FormData(form);
        const r  = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        if (r.status === 204) {
            modal.hide();
            showSuccess('تم تحديث بيانات الملعب');
            input.dispatchEvent(new Event('keyup'));
        } else {
            window.location.reload();
        }
    });

    // ربط مستمعي التبديل الأوّليين (للصفحة قبل أي بحث)
    document.querySelectorAll('form.toggle-status').forEach(f => {
        f.addEventListener('submit', async ev => {
            ev.preventDefault();
            const fd = new FormData(f);
            await fetch(f.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            showSuccess('تم تحديث حالة الملعب');
            input.dispatchEvent(new Event('keyup'));
        });
    });
});
</script>
@endpush
