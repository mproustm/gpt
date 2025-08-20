{{-- resources/views/admin/owners/owners.blade.php --}}
@extends('admin.admin_layouts.dashboard')

@php($title = 'أصحاب الملاعب')
@php($currentTab = request('tab', 'addOwner'))

@section('content')

    {{-- التنبيهات --}}
    @foreach(['success'=>'check-circle','error'=>'times-circle'] as $key => $icon)
        @if(session($key))
            <div class="alert alert-{{ $key=='success'?'success':'danger' }} alert-dismissible fade show mb-4">
                <i class="fas fa-{{ $icon }} me-1"></i> {{ session($key) }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- التبويبات --}}
    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <button class="nav-link {{ $currentTab=='addOwner'?'active':'' }}"
                    data-bs-toggle="tab" data-bs-target="#addOwner">
                إضافة صاحب ملعب
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $currentTab=='viewOwners'?'active':'' }}"
                    data-bs-toggle="tab" data-bs-target="#viewOwners">
                عرض أصحاب الملاعب
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $currentTab=='archivedOwners'?'active':'' }}"
                    data-bs-toggle="tab" data-bs-target="#archivedOwners">
                المؤرشفون
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ===== 1) إضافة صاحب ملعب ===== --}}
        <div class="tab-pane fade {{ $currentTab=='addOwner'?'show active':'' }}" id="addOwner">
            <div class="content-card">
                <div class="content-card-header"><i class="fas fa-user-plus me-1"></i>إضافة صاحب ملعب جديد</div>
                <form method="POST" action="{{ route('admin.owners', ['tab'=>'addOwner']) }}">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">الاسم</label>
                            <input name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف</label>
                            <input name="phone_number" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">العنوان</label>
                            <input name="address" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <div class="col-12 text-center">
                            <button class="btn btn-success px-4">
                                <i class="fas fa-plus me-1"></i>إضافة
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ===== 2) عرض أصحاب الملاعب ===== --}}
        <div class="tab-pane fade {{ $currentTab=='viewOwners'?'show active':'' }}" id="viewOwners">
            <div class="content-card mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">بحث بالاسم</label>
                        <input id="owner-search-view" type="text"
                               value="{{ request('name') }}"
                               class="form-control form-control-sm"
                               placeholder="أدخل الاسم…">
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الهاتف</th>
                                <th>العنوان</th>
                                <th class="text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="viewOwnersBody">
                            @foreach($owners as $owner)
                                <tr>
                                    <td>{{ $owner->name }}</td>
                                    <td>{{ $owner->email }}</td>
                                    <td>{{ $owner->phone_number }}</td>
                                    <td>{{ $owner->address }}</td>
                                    <td class="text-center">
                                        {{-- إضافة ملعب --}}
                                        <button class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#addStadiumModal"
                                                data-owner="{{ $owner->id }}">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        {{-- أرشفة --}}
                                        <form method="POST"
                                              action="{{ route('admin.owners',['tab'=>'viewOwners']) }}"
                                              class="d-inline">
                                            @csrf
                                            <input type="hidden" name="archive_id" value="{{ $owner->id }}">
                                            <button class="btn btn-sm btn-secondary">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                        {{-- حذف نهائي --}}
                                        <form method="POST"
                                              action="{{ route('admin.owners',['tab'=>'viewOwners']) }}"
                                              class="d-inline" onsubmit="return confirm('حذف نهائي؟');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="delete_id" value="{{ $owner->id }}">
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($owners->isEmpty())
                                <tr><td colspan="5" class="text-center py-4">لا توجد بيانات</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                {{ $owners->withQueryString()->links() }}
            </div>
        </div>

        {{-- ===== 3) المؤرشفون ===== --}}
        <div class="tab-pane fade {{ $currentTab=='archivedOwners'?'show active':'' }}"
             id="archivedOwners">
            <div class="content-card mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">بحث بالاسم</label>
                        <input id="owner-search-archived" type="text"
                               value="{{ request('name') }}"
                               class="form-control form-control-sm"
                               placeholder="أدخل الاسم…">
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الهاتف</th>
                                <th>العنوان</th>
                                <th class="text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="archivedOwnersBody">
                            @foreach($archived as $arch)
                                <tr>
                                    <td>{{ $arch->name }}</td>
                                    <td>{{ $arch->email }}</td>
                                    <td>{{ $arch->phone_number }}</td>
                                    <td>{{ $arch->address }}</td>
                                    <td class="text-center">
                                        <form method="POST"
                                              action="{{ route('admin.owners',['tab'=>'archivedOwners']) }}"
                                              class="d-inline">
                                            @csrf
                                            <input type="hidden" name="restore_id" value="{{ $arch->id }}">
                                            <button class="btn btn-sm btn-success">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($archived->isEmpty())
                                <tr><td colspan="5" class="text-center py-4">لا توجد بيانات</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- مودال إضافة ملعب (كما قبل) --}}
    <div class="modal fade" id="addStadiumModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('admin.stadiums') }}" class="modal-content" enctype="multipart/form-data">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">إضافة ملعب جديد</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="owner_id" id="modalOwnerId" required>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">اسم الملعب</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">سعر الملعب (د.ل)</label>
                <input type="number" name="price" step="0.01" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label">العنوان</label>
                <input type="text" name="address" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label">وصف الملعب</label>
                <textarea name="description" rows="3" class="form-control"></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">صورة الملعب</label>
                <input type="file" name="stad_pic" class="form-control" accept="image/*">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-1"></i> حفظ الملعب
            </button>
          </div>
        </form>
      </div>
    </div>

@endsection

@push('scripts')
<script>
  // إدخال الـ owner_id داخل المودال
  document.getElementById('addStadiumModal')
    .addEventListener('show.bs.modal', e => {
      document.getElementById('modalOwnerId')
        .value = e.relatedTarget.getAttribute('data-owner');
    });

  // وظيفة بحث حي لأصحاب الملاعب
  function liveSearchOwners(tab, inputId, bodyId) {
    const input = document.getElementById(inputId),
          tbody = document.getElementById(bodyId);

    input.addEventListener('keyup', ev => {
      const q = input.value.trim();
      fetch(`{{ route('admin.owners') }}?tab=${tab}&name=` + encodeURIComponent(q), {
        headers: {'X-Requested-With':'XMLHttpRequest'}
      })
      .then(r=>r.json())
      .then(data=>{
        let list = (tab==='viewOwners') ? data.viewOwners : data.archivedOwners;
        tbody.innerHTML = '';
        if (!list.length) {
          tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4">لا توجد بيانات</td></tr>`;
          return;
        }
        list.forEach(o=>{
          tbody.innerHTML += `
            <tr>
              <td>${o.name}</td>
              <td>${o.email}</td>
              <td>${o.phone_number}</td>
              <td>${o.address||''}</td>
              <td class="text-center">
                ${ tab==='viewOwners' 
                  ? `<button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStadiumModal" data-owner="${o.id}">
                       <i class="fas fa-plus-circle"></i>
                     </button>
                     <form method="POST" action="{{ route('admin.owners',['tab'=>'viewOwners']) }}" class="d-inline">
                       @csrf
                       <input type="hidden" name="archive_id" value="${o.id}">
                       <button class="btn btn-sm btn-secondary"><i class="fas fa-archive"></i></button>
                     </form>
                     <form method="POST" action="{{ route('admin.owners',['tab'=>'viewOwners']) }}" class="d-inline" onsubmit="return confirm('حذف نهائي؟');">
                       @csrf
                       @method('DELETE')
                       <input type="hidden" name="delete_id" value="${o.id}">
                       <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                     </form>`
                  : `<form method="POST" action="{{ route('admin.owners',['tab'=>'archivedOwners']) }}" class="d-inline">
                       @csrf
                       <input type="hidden" name="restore_id" value="${o.id}">
                       <button class="btn btn-sm btn-success"><i class="fas fa-undo"></i></button>
                     </form>`
                }
              </td>
            </tr>`;
        });
      });
    });
  }

  liveSearchOwners('viewOwners',     'owner-search-view',     'viewOwnersBody');
  liveSearchOwners('archivedOwners', 'owner-search-archived', 'archivedOwnersBody');
</script>
@endpush
