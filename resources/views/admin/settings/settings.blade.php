@extends('admin.admin_layouts.dashboard')

@php($title = 'إعدادات النظام')

@section('content')
<div class="settings-wrapper py-3" dir="rtl">

    {{-- ==== Success Alert ==== --}}
    @if(session('success'))
        <div style="background:#e6f4ea;color:#23774c;border:1px solid #c9efd8;padding:12px 20px;border-radius:7px;font-weight:bold;text-align:center;margin-bottom:22px;font-size:1.07rem;">
            {{ session('success') }}
        </div>
    @endif

    <div class="content-card">
        <div class="content-card-header">
            <i class="fas fa-cogs text-primary"></i>
            <span class="ms-2 fw-bold">إعدادات النظام العامة</span>
        </div>

        <form method="POST" action="{{ route('admin.settings') }}">
            @csrf
            <div class="row g-3">

                <div class="col-md-6 form-floating">
                    <input type="text" id="site_name" name="site_name"
                           class="form-control" placeholder="اسم الموقع"
                           value="{{ old('site_name', $settings['site_name'] ?? '') }}" required>
                    <label for="site_name">اسم الموقع</label>
                </div>

                <div class="col-md-6 form-floating">
                    <input type="email" id="email" name="email"
                           class="form-control" placeholder="البريد الإلكتروني"
                           value="{{ old('email', $settings['email'] ?? '') }}" required>
                    <label for="email">البريد الإلكتروني</label>
                </div>

                <div class="col-md-6 form-floating">
                    <input type="text" id="phone" name="phone"
                           class="form-control" placeholder="رقم الهاتف"
                           value="{{ old('phone', $settings['phone'] ?? '') }}" required>
                    <label for="phone">رقم الهاتف</label>
                </div>

                <div class="col-md-6 form-floating">
                    <input type="text" id="address" name="address"
                           class="form-control" placeholder="العنوان"
                           value="{{ old('address', $settings['address'] ?? '') }}" required>
                    <label for="address">العنوان</label>
                </div>

                <div class="col-12 inputContainer">
                    <textarea id="about_text"
                              name="about_text"
                              class="customInput"
                              rows="6"
                              maxlength="600"
                              placeholder=" "
                              required>{{ old('about_text', $settings['about_text'] ?? '') }}</textarea>
                    <label class="inputLabel" for="about_text">المحتوى التعريفي (حد أقصى 600 حرف)</label>
                    <div class="inputUnderline"></div>
                    <small class="d-block text-end mt-1"><span id="charCount">{{ mb_strlen(old('about_text', $settings['about_text'] ?? '')) }}</span>/600</small>
                </div>
            </div>

            <div class="text-center mt-4">
                <button class="btn btn-primary fw-bold px-5 py-2">
                    <i class="fas fa-save ms-2"></i> حفظ التغييرات
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==================== JavaScript ==================== --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('about_text');
        const charCount = document.getElementById('charCount');
        if(textarea && charCount) {
            textarea.addEventListener('input', function () {
                charCount.textContent = textarea.value.length;
            });
        }
    });
</script>

{{-- ==================== CSS المخصّص ==================== --}}
<style>
    .content-card{
        background:#fff;border-radius:10px;padding:24px;margin-bottom:24px;
        box-shadow:0 0.5rem 1rem rgba(0,0,0,.05)
    }
    .content-card-header{
        display:flex;align-items:center;gap:.5rem;
        font-size:1rem;color:#374151;margin-bottom:1.25rem
    }
    .nav-pills .nav-link{font-weight:600;color:#374151}
    .nav-pills .nav-link.active{background:#e6bd45;color:#fff}
    .inputContainer{position:relative;margin-bottom:24px}
    .customInput{
        width:100%;padding:14px 12px;font-size:15px;line-height:1.6;
        color:#0d6efd;background:transparent;border:none;border-bottom:2px solid #0d6efd;
        resize:vertical;transition:.3s
    }
    .customInput:focus{border-color:#e6bd45;box-shadow:none}
    .customInput:not(:placeholder-shown){border-color:#e6bd45}
    .inputLabel{
        position:absolute;top:10px;left:12px;font-size:15px;color:#e6bd45;
        pointer-events:none;transition:all .25s ease
    }
    .customInput:focus + .inputLabel,
    .customInput:not(:placeholder-shown) + .inputLabel{
        transform:translateY(-140%) scale(.85);color:#e6bd45
    }
    .inputUnderline{position:absolute;bottom:0;left:0;width:0;height:2px;background:#e6bd45;transition:.25s}
    .customInput:focus ~ .inputUnderline{width:100%}
    #floatingSave{background:#e6bd45;border:none}
    #floatingSave:hover{background:#e6bd45}
    .settings-wrapper{background:#f8f9fb;min-height:calc(100vh - 90px)}
</style>
@endsection
