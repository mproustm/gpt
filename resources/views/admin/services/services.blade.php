{{-- resources/views/admin/services/services.blade.php --}}
@extends('admin.admin_layouts.dashboard')

@php
    $title = 'خدمات الملاعب';
    $currentTab = request('tab', 'addService');
@endphp

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="content-card">

        <ul class="nav nav-pills mb-4" id="serviceTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $currentTab=='addService'?'active':'' }}" id="add-tab"
                        data-bs-toggle="tab" data-bs-target="#addService" type="button" role="tab">
                    إضافة خدمة للملعب
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $currentTab=='viewServices'?'active':'' }}" id="view-tab"
                        data-bs-toggle="tab" data-bs-target="#viewServices" type="button" role="tab">
                    عرض خدمات الملاعب
                </button>
            </li>
        </ul>

        <div class="tab-content" id="serviceTabsContent">

            {{-- إضافة --}}
            <div class="tab-pane fade {{ $currentTab=='addService'?'show active':'' }}" id="addService" role="tabpanel">
                <div class="content-card">
                    <div class="content-card-header">إضافة خدمة جديدة</div>

                    <form method="POST" action="{{ route('admin.services', ['tab'=>'addService']) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">اختر الملعب</label>
                                <select name="s_id" class="form-select" required>
                                    @foreach($stadiums as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">نوع الخدمة</label>
                                <input type="text" name="service_type" class="form-control" maxlength="50" required>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> إضافة الخدمة
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- عرض --}}
            <div class="tab-pane fade {{ $currentTab=='viewServices'?'show active':'' }}" id="viewServices" role="tabpanel">
                <div class="content-card">
                    {{-- فلتر الحالة --}}
                    <form method="GET" class="row g-2 mb-3">
                        <input type="hidden" name="tab" value="viewServices">
                        <div class="col-auto">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="" {{ request('status')==='' ? 'selected' : '' }}>كل الحالات</option>
                                <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>مفعل</option>
                                <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>غير مفعل</option>
                            </select>
                        </div>
                        @if(request()->has('status') && request('status')!=='')
                        <div class="col-auto">
                            <a class="btn btn-outline-secondary" href="{{ route('admin.services',['tab'=>'viewServices']) }}">إزالة الفلتر</a>
                        </div>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>اسم الملعب</th>
                                    <th>نوع الخدمة</th>
                                    <th>الحالة</th>
                                    <th class="text-center">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($services as $service)
                                @php
                                    $isActive = $service->is_active; // من الإكسسِسور في الموديل
                                @endphp
                                <tr>
                                    <td>{{ $service->stadium->name }}</td>
                                    <td>{{ $service->service_type }}</td>
                                    <td>
                                        <span class="status-badge {{ $isActive ? 'status-active' : 'status-inactive' }}">
                                            {{ $isActive ? 'مفعل' : 'غير مفعل' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{-- تفعيل/تعطيل --}}
                                        <form method="POST" action="{{ route('admin.services', ['tab'=>'viewServices'] + request()->only('status')) }}" class="d-inline">
                                            @csrf
                                            @if($isActive)
                                                <input type="hidden" name="deactivate_id" value="{{ $service->id }}">
                                                <button type="submit" class="btn btn-sm btn-warning" title="تعطيل">
                                                    <i class="fas fa-toggle-off"></i>
                                                </button>
                                            @else
                                                <input type="hidden" name="activate_id" value="{{ $service->id }}">
                                                <button type="submit" class="btn btn-sm btn-success" title="تفعيل">
                                                    <i class="fas fa-toggle-on"></i>
                                                </button>
                                            @endif
                                        </form>

                                        {{-- حذف --}}
                                        <form method="POST" action="{{ route('admin.services', ['tab'=>'viewServices'] + request()->only('status')) }}"
                                              class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الخدمة؟');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="delete_id" value="{{ $service->id }}">
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">لا توجد خدمات</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        {{ $services->withQueryString()->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('styles')
<style>
.status-badge { padding:4px 10px; border-radius:8px; font-size:.75rem; }
.status-active   { background:#d1e7dd; color:#0f5132; }
.status-inactive { background:#f8d7da; color:#842029; }
</style>
@endpush
