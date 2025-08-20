{{-- resources/views/admin/profile/profile.blade.php --}}
@extends('admin.admin_layouts.dashboard')

@php($title = 'الملف الشخصي')

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="content-card">
        <div class="content-card-header"><i class="fas fa-user"></i> بيانات الملف الشخصي</div>

        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
            @csrf

            {{-- صورة الملف الشخصي مع زر تغيير --}}
            <div class="text-center my-4">
                <label for="picInput" class="d-inline-block position-relative" style="cursor:pointer;">
                    @if($user->pic)
                        <img src="{{ asset('storage/' . $user->pic) }}"
                             class="rounded-circle"
                             style="width:140px; height:140px; object-fit:cover;">
                    @else
                        <div class="bg-secondary rounded-circle"
                             style="width:140px; height:140px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:2.5rem;">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-white rounded-circle p-2"
                          title="تغيير الصورة">
                        <i class="fas fa-camera"></i>
                    </span>
                </label>
                <input type="file" id="picInput" name="pic" accept="image/*" class="d-none">
                @error('pic') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="row g-4">
                {{-- الاسم --}}
                <div class="col-md-6">
                    <label class="form-label">الاسم</label>
                    <input name="name" value="{{ old('name', $user->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- البريد الإلكتروني --}}
                <div class="col-md-6">
                    <label class="form-label">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- كلمة المرور --}}
                <div class="col-md-6">
                    <label class="form-label">كلمة المرور</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" minlength="6">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- حفظ --}}
                <div class="col-12 text-center mt-3">
                    <button class="btn btn-success px-4">
                        <i class="fas fa-save me-1"></i> حفظ التغييرات
                    </button>
                </div>
            </div>
        </form>
    </div>

@endsection
