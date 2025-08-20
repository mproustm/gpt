{{-- resources/views/admin/players/players.blade.php --}}
@extends('admin.admin_layouts.dashboard')

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
            <button class="nav-link {{ request('tab')!='view' && request('tab')!='archived' && !request()->has('name') ? 'active' : '' }}"
                    data-bs-toggle="tab" data-bs-target="#addPlayer">
                إضافة لاعب
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request('tab')==='view' || request()->has('name') ? 'active' : '' }}"
                    data-bs-toggle="tab" data-bs-target="#viewPlayers">
                عرض اللاعبين
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request('tab')==='archived' ? 'active' : '' }}"
                    data-bs-toggle="tab" data-bs-target="#archivedPlayers">
                المؤرشفون
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- 1) إضافة لاعب --}}
        <div class="tab-pane fade {{ request('tab')!='view' && request('tab')!='archived' && !request()->has('name') ? 'show active' : '' }}" id="addPlayer">
            <div class="content-card">
                <form method="POST" action="{{ route('admin.players') }}">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">الاسم الكامل</label>
                            <input name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">اسم المستخدم</label>
                            <input name="user_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف</label>
                            <input name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">كلمة المرور</label>
                            <input name="password" type="password" class="form-control" minlength="6" required>
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

        {{-- 2) عرض اللاعبين --}}
        <div class="tab-pane fade {{ request('tab')==='view' || request()->has('name') ? 'show active' : '' }}" id="viewPlayers">
            <div class="content-card mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">بحث بالاسم</label>
                        <input id="player-search-view"
                               type="text"
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
                                <th>اسم المستخدم</th>
                                <th>الهاتف</th>
                                <th class="text-center">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="viewPlayersBody">
                            @foreach($players as $p)
                                <tr>
                                    <td>{{ $p->name }}</td>
                                    <td>{{ $p->user_name }}</td>
                                    <td>{{ $p->phone }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPlayerModal"
                                                data-player-id="{{ $p->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.players',['tab'=>'view']) }}" class="d-inline" onsubmit="return confirm('أرشفة؟')">
                                            @csrf
                                            <input type="hidden" name="archive_id" value="{{ $p->id }}">
                                            <button class="btn btn-sm btn-warning"><i class="fas fa-archive"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.players',['tab'=>'view']) }}" class="d-inline" onsubmit="return confirm('حذف نهائي؟')">
                                            @csrf @method('DELETE')
                                            <input type="hidden" name="delete_id" value="{{ $p->id }}">
                                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($players->isEmpty())
                                <tr><td colspan="4" class="text-center py-4">لا توجد بيانات</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                {{ $players->withQueryString()->links() }}
            </div>
        </div>

        {{-- 3) المؤرشفون --}}
        <div class="tab-pane fade {{ request('tab')==='archived'?'show active':'' }}" id="archivedPlayers">
            <div class="content-card mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">بحث بالاسم</label>
                        <input id="player-search-archived"
                               type="text"
                               value="{{ request('name') }}"
                               class="form-control form-control-sm"
                               placeholder="أدخل الاسم…">
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>اسم المستخدم</th>
                                <th>الهاتف</th>
                                <th>تاريخ الأرشفة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody id="archivedPlayersBody">
                            @foreach($archived as $p)
                                <tr>
                                    <td>{{ $p->name }}</td>
                                    <td>{{ $p->user_name }}</td>
                                    <td>{{ $p->phone }}</td>
                                    <td>{{ $p->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.players',['tab'=>'archived']) }}" onsubmit="return confirm('استعادة؟')" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="restore_id" value="{{ $p->id }}">
                                            <button class="btn btn-sm btn-success"><i class="fas fa-undo"></i></button>
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

    {{-- تعديل لاعب – المودال --}}
    <div class="modal fade" id="editPlayerModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">تعديل بيانات اللاعب</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div id="edit-form-container" class="py-3 text-center text-secondary">
              <i class="fas fa-spinner fa-spin"></i> جاري التحميل…
            </div>
          </div>
        </div>
      </div>
    </div>

@endsection

@push('scripts')
<script>
  // Live-search للاعبين
  function liveSearchPlayers(tab, inputId, bodyId) {
    const input = document.getElementById(inputId),
          tbody = document.getElementById(bodyId);
    let timer;
    input.addEventListener('keyup', () => {
      clearTimeout(timer);
      timer = setTimeout(() => {
        const q = encodeURIComponent(input.value.trim());
        fetch(`{{ route('admin.players') }}?tab=${tab}&name=${q}`, {
          headers: {'X-Requested-With':'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(data => {
          tbody.innerHTML = '';
          if (!data.length) {
            tbody.innerHTML = `<tr><td colspan="${tab==='view'?4:5}" class="text-center py-4">لا توجد بيانات</td></tr>`;
            return;
          }
          data.forEach(p => {
            if (tab==='view') {
              tbody.innerHTML += `
                <tr>
                  <td>${p.name}</td>
                  <td>${p.user_name}</td>
                  <td>${p.phone}</td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#editPlayerModal"
                            data-player-id="${p.id}">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="{{ route('admin.players',['tab'=>'view']) }}" class="d-inline" onsubmit="return confirm('أرشفة؟')">
                      <input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <input type="hidden" name="archive_id" value="${p.id}">
                      <button class="btn btn-sm btn-warning"><i class="fas fa-archive"></i></button>
                    </form>
                    <form method="POST" action="{{ route('admin.players',['tab'=>'view']) }}" class="d-inline" onsubmit="return confirm('حذف نهائي؟')">
                      <input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <input type="hidden" name="_method" value="DELETE">
                      <input type="hidden" name="delete_id" value="${p.id}">
                      <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>`;
            } else {
              tbody.innerHTML += `
                <tr>
                  <td>${p.name}</td>
                  <td>${p.user_name}</td>
                  <td>${p.phone}</td>
                  <td>${p.created_at}</td>
                  <td class="text-center">
                    <form method="POST" action="{{ route('admin.players',['tab'=>'archived']) }}" onsubmit="return confirm('استعادة؟')" class="d-inline">
                      <input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <input type="hidden" name="restore_id" value="${p.id}">
                      <button class="btn btn-sm btn-success"><i class="fas fa-undo"></i></button>
                    </form>
                  </td>
                </tr>`;
            }
          });
        })
        .catch(console.error);
      }, 300);
    });
  }

  liveSearchPlayers('view',     'player-search-view',     'viewPlayersBody');
  liveSearchPlayers('archived', 'player-search-archived', 'archivedPlayersBody');

  // حقل المودال: جلب نموذج التحرير
  document.getElementById('editPlayerModal')
    .addEventListener('show.bs.modal', e => {
      const id = e.relatedTarget.getAttribute('data-player-id'),
            container = document.getElementById('edit-form-container');
      container.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التحميل…';
      fetch(`{{ route('admin.players') }}?fetch_id=${id}`, {
        headers: {'X-Requested-With':'XMLHttpRequest'}
      })
      .then(r => r.json())
      .then(p => {
        container.innerHTML = `
          <form method="POST" action="{{ route('admin.players') }}">
            @csrf
            <input type="hidden" name="edit_id" value="${p.id}">
            <div class="mb-3 text-start">
              <label class="form-label">الاسم الكامل</label>
              <input name="name" type="text" class="form-control" value="${p.name}" required>
            </div>
            <div class="mb-3 text-start">
              <label class="form-label">اسم المستخدم</label>
              <input name="user_name" type="text" class="form-control" value="${p.user_name}" required>
            </div>
            <div class="mb-3 text-start">
              <label class="form-label">رقم الهاتف</label>
              <input name="phone" type="tel" class="form-control" value="${p.phone}" required>
            </div>
            <div class="mb-3 text-start">
              <label class="form-label">كلمة المرور (جديدة إذا رغبت)</label>
              <input name="password" type="password" class="form-control">
            </div>
            <div class="text-end">
              <button class="btn btn-success"><i class="fas fa-save"></i> حفظ التغييرات</button>
            </div>
          </form>`;
      })
      .catch(() => {
        container.innerHTML = '<div class="text-danger">فشل في جلب البيانات</div>';
      });
    });
</script>
@endpush
