@extends('admin.admin_layouts.dashboard')

@php($title = 'إدارة الشكاوى')

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Tabs --}}
    <ul class="nav nav-pills mb-4" id="complaintsTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="listTab" data-bs-toggle="tab" data-bs-target="#listPane" type="button">
                عرض الشكاوى
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="listPane">

            {{-- Search Card --}}
            <div class="content-card mb-4">
                <div class="content-card-header">
                    <i class="fas fa-search"></i><span>بحث</span>
                </div>
                <form class="row g-3" method="GET" action="{{ route('admin.support') }}">
                    <div class="col-md-3">
                        <label class="form-label">رقم الشكوى</label>
                        <input type="text" name="id" value="{{ request('id') }}" class="form-control form-control-sm" placeholder="1024 …">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">مقدّم الشكوى</label>
                        <input type="text" name="reporter" value="{{ request('reporter') }}" class="form-control form-control-sm" placeholder="اسم أو بريد">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">الحالة</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">الكل</option>
                            <option value="pending"  @selected(request('status') == 'pending')>جديدة</option>
                            <option value="reviewed" @selected(request('status') == 'reviewed')>قيد المراجعة</option>
                            <option value="resolved" @selected(request('status') == 'resolved')>تم الحل</option>
                            <option value="rejected" @selected(request('status') == 'rejected')>مرفوضة</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-sm btn-success w-100">
                            <i class="fas fa-filter"></i> بحث
                        </button>
                        <a href="{{ route('admin.support') }}" class="btn btn-sm btn-secondary w-100">
                            <i class="fas fa-undo"></i> إعادة
                        </a>
                    </div>
                </form>
            </div>

            {{-- Complaints List --}}
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-list-alt"></i><span>قائمة الشكاوى</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-center">
                                <th>#</th>
                                <th>المُشتكي</th>
                                <th>التاريخ</th>
                                <th>نوع المُشتكي</th>
                                <th>الجهة المشتكى عليها</th>
                                <th>مقتطف</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supports as $support)
                                <tr class="text-center">
                                    <td>{{ $support->id }}</td>
                                    <td>{{ $support->reporter->name ?? 'مستخدم محذوف' }}</td>
                                    <td>{{ $support->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $support->reporter instanceof App\Models\Player ? 'لاعب' : 'صاحب ملعب' }}</td>
                                    <td>
                                        @if($support->target)
                                            {{ ($support->target instanceof App\Models\Player ? 'لاعب: ' : 'صاحب ملعب: ') . $support->target->name }}
                                        @else
                                            <span class="text-muted">غير معروف</span>
                                        @endif
                                    </td>
                                    <td class="text-start">{{ \Illuminate\Support\Str::limit($support->message, 30, '…') }}</td>
                                    <td>
                                        @if($support->status=='pending')
                                            <span class="badge bg-info text-dark">جديدة</span>
                                        @elseif($support->status=='reviewed')
                                            <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                        @elseif($support->status=='resolved')
                                            <span class="badge bg-success">تم الحل</span>
                                        @else
                                            <span class="badge bg-danger">مرفوضة</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewSupportModal{{ $support->id }}" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editSupportModal{{ $support->id }}" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.support.destroy', $support->id) }}" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الشكوى؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        لا توجد شكاوى تطابق معايير البحث.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($supports->hasPages())
                <nav class="d-flex justify-content-center mt-4">
                    {{ $supports->links() }}
                </nav>
                @endif
            </div>

            {{-- Modals --}}
            @foreach($supports as $support)
                {{-- View Modal --}}
                <div class="modal fade" id="viewSupportModal{{ $support->id }}" tabindex="-1" aria-labelledby="viewSupportLabel{{ $support->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewSupportLabel{{ $support->id }}">تفاصيل الشكوى #{{ $support->id }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                            </div>
                            <div class="modal-body p-0">
                                <table class="table table-striped mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="w-25">المُشتكي</th>
                                            <td>{{ $support->reporter->name ?? 'مستخدم محذوف' }}</td>
                                        </tr>
                                        <tr>
                                            <th>نوع المُشتكي</th>
                                            <td>{{ $support->reporter instanceof App\Models\Player ? 'لاعب' : 'صاحب ملعب' }}</td>
                                        </tr>
                                        <tr>
                                            <th>التاريخ</th>
                                            <td>{{ $support->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>الجهة المشتكى عليها</th>
                                            <td>
                                                @if($support->target)
                                                    {{ ($support->target instanceof App\Models\Player ? 'لاعب: ' : 'صاحب ملعب: ') . $support->target->name }}
                                                @else
                                                    <span class="text-muted">غير معروف</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الحالة</th>
                                            <td>
                                                @if($support->status=='pending')
                                                    <span class="badge bg-info text-dark">جديدة</span>
                                                @elseif($support->status=='reviewed')
                                                    <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                                @elseif($support->status=='resolved')
                                                    <span class="badge bg-success">تم الحل</span>
                                                @else
                                                    <span class="badge bg-danger">مرفوضة</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>نص الشكوى</th>
                                            <td style="white-space: pre-wrap; word-break: break-word;">{{ $support->message }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Edit Modal --}}
                <div class="modal fade" id="editSupportModal{{ $support->id }}" tabindex="-1" aria-labelledby="editSupportLabel{{ $support->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('admin.support.update', $support->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editSupportLabel{{ $support->id }}">تعديل الشكوى #{{ $support->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">الحالة</label>
                                        <select name="status" class="form-select">
                                            <option value="pending"  @selected($support->status == 'pending')>جديدة</option>
                                            <option value="reviewed" @selected($support->status == 'reviewed')>قيد المراجعة</option>
                                            <option value="resolved" @selected($support->status == 'resolved')>تم الحل</option>
                                            <option value="rejected" @selected($support->status == 'rejected')>مرفوضة</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">نص الشكوى</label>
                                        <textarea name="message" class="form-control" rows="4">{{ $support->message }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection