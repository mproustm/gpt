@extends('admin.admin_layouts.dashboard')

@php($title = 'إرسال الإشعارات')

@section('content')
<div class="settings-wrapper py-3" dir="rtl">

    <div class="content-card">
        {{-- رأس البطاقة --}}
        <div class="content-card-header">
            <i class="fas fa-bell text-primary"></i>
            <span class="ms-2 fw-bold">إرسال إشعار جديد</span>
        </div>

        {{-- إشعار نجاح --}}
        @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif

        {{-- نموذج إرسال الإشعار --}}
        <form id="notifyForm" method="POST" action="{{ route('admin.notifications') }}">
            @csrf
            <div class="row g-3">

                {{-- العنوان --}}
                <div class="col-12 form-floating">
                    <input type="text" id="title" name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           placeholder="عنوان الإشعار"
                           maxlength="80" required value="{{ old('title') }}">
                    <label for="title">عنوان الإشعار (حد أقصى 80 حرف)</label>
                    @error('title')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- النص --}}
                <div class="col-12 inputContainer">
                    <textarea id="body" name="body"
                              class="customInput @error('body') is-invalid @enderror"
                              rows="4" maxlength="300" placeholder=" " required>{{ old('body') }}</textarea>
                    <label class="inputLabel" for="body">نص الإشعار (حد أقصى 300 حرف)</label>
                    <div class="inputUnderline"></div>
                    <small class="d-block text-end mt-1"><span id="bodyCharCount">0</span>/300</small>
                    @error('body')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- الفئة المستلمة --}}
                <div class="col-12 form-floating">
                    <select id="target" name="target"
                            class="form-select @error('target') is-invalid @enderror" required>
                        <option value="" selected disabled>اختر الفئة المستلمة</option>
                        <option value="admins"     {{ old('target')=='admins'     ? 'selected':'' }}>اصحاب الملاعب</option>
                        <option value="one_owner"  {{ old('target')=='one_owner'  ? 'selected':'' }}>مالك محدد</option>
                        <option value="players" {{ old('target')=='players' ? 'selected' : '' }}>اللاعبين</option>
                    </select>
                    <label for="target">الفئة المستلمة</label>
                    @error('target')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                {{-- يظهر فقط عند اختيار "مالك محدد" --}}
                <div id="ownerStadiumDiv" class="col-12 form-floating" style="display:none;">
                    <select id="owner_stadium" name="owner_stadium"
                            class="form-select @error('owner_stadium') is-invalid @enderror">
                        <option value="" selected disabled>اختر مالكًا – اسم الملعب</option>
                        @foreach($stadiums as $stadium)
                            <option value="{{ $stadium->id }}"
                                {{ old('owner_stadium')==$stadium->id ? 'selected':'' }}>
                                {{ $stadium->owner?->name ?? '-' }} – {{ $stadium->name }}
                            </option>
                        @endforeach
                    </select>
                    <label for="owner_stadium">مالك الملعب – اسم الملعب</label>
                    @error('owner_stadium')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

            </div>

            <div class="text-center mt-4">
                <button class="btn btn-primary fw-bold px-5 py-2">
                    <i class="fas fa-paper-plane ms-2"></i> إرسال الإشعار
                </button>
            </div>
        </form>
    </div>

    {{-- جدول الإشعارات المرسلة --}}
    @if(isset($notifications) && count($notifications))
        <div class="content-card mt-4">
            <div class="content-card-header">
                <i class="fas fa-list text-primary"></i>
                <span class="ms-2 fw-bold">سجل الإشعارات الأخيرة</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>المالك – اسم الملعب</th>
                            <th>العنوان</th>
                            <th>النص</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notifications as $notif)
                            <tr>
                                <td>{{ $notif->owner?->name ?? '-' }} – {{ $notif->stadium?->name ?? '-' }}</td>
                                <td>{{ $notif->title }}</td>
                                <td>{{ $notif->body }}</td>
                                <td>{{ $notif->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- زرّ إرسال عائم --}}
<button id="floatingSend"
        class="btn btn-primary fw-bold shadow-lg px-4 py-2"
        style="display:none;position:fixed;bottom:25px;left:25px;z-index:1050;">
    <i class="fas fa-paper-plane ms-2"></i> إرسال
</button>

{{-- ============ JavaScript ============ --}}
<script>
(function () {
    const body       = document.getElementById('body');
    const bodyCount  = document.getElementById('bodyCharCount');
    const floatBtn   = document.getElementById('floatingSend');
    const form       = document.getElementById('notifyForm');
    const ownerDiv   = document.getElementById('ownerStadiumDiv');
    const targetSel  = document.getElementById('target');
    const ownerSel   = document.getElementById('owner_stadium');

    const defaultForm = { title:"", body:"", target:"", owner_stadium:"" };

    /** تحديث عدّاد الأحرف وظهور زر الإرسال */
    const refresh = () => {
        bodyCount.textContent = body.value.length;
        floatBtn.style.display =
            form.title.value.trim()  !== defaultForm.title  ||
            form.body.value.trim()   !== defaultForm.body   ||
            form.target.value.trim() !== defaultForm.target ||
            (ownerSel && ownerSel.value.trim() !== defaultForm.owner_stadium)
            ? 'block' : 'none';
    };

    /** إظهار قائمة المالك فقط عند "one_owner" */
    function toggleOwnerList() {
        if (targetSel.value === 'one_owner') {
            ownerDiv.style.display = 'block';
        } else {
            ownerDiv.style.display = 'none';
            if (ownerSel) ownerSel.selectedIndex = 0;
        }
    }

    // أحداث
    body.addEventListener('input',   refresh);
    form.title.addEventListener('input', refresh);
    targetSel.addEventListener('change', () => { toggleOwnerList(); refresh(); });
    if (ownerSel) ownerSel.addEventListener('change', refresh);
    floatBtn.addEventListener('click', () => form.submit());

    // تهيئة
    document.addEventListener('DOMContentLoaded', () => {
        toggleOwnerList();
        bodyCount.textContent = body.value.length;
    });
})();
</script>
@endsection
