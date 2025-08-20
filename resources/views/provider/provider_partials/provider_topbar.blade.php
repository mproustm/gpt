<header class="topbar">
    <span class="page-title">
        لوحة التحكم – @yield('page_title', 'الرئيسية')
    </span>

    <button id="provider-theme-toggle" class="theme-toggle-btn" aria-label="Toggle dark mode">
        <i class="fas fa-moon"></i>
    </button>

    <div class="dropdown user-dropdown position-relative">
        <button class="btn dropdown-toggle p-0"
                id="userMenu"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                type="button">
            <span class="avatar-wrapper"
                  style="position:relative; display: flex; align-items: center; gap: 0.5rem;">
                {{-- User avatar --}}
                @if(!empty($owner->pic))
                    <img src="{{ asset('storage/' . $owner->pic) }}"
                         class="user-avatar rounded-circle"
                         style="width:40px; height:40px; object-fit:cover;"
                         alt="User">
                @else
                    <img src="{{ asset('img/2.jpg') }}"
                         class="user-avatar rounded-circle"
                         style="width:40px; height:40px; object-fit:cover;"
                         alt="User">
                @endif

                {{-- Notification badge --}}
                <span id="notify-badge" class="notify-badge" style="display:none;"></span>

                {{-- Custom dropdown arrow (left of name) --}}
                <span class="dropdown-arrow"><i class="fas fa-chevron-down"></i></span>

                {{-- Owner name --}}
                <span class="owner-name">
                    {{ $owner->name }}
                </span>
            </span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end" style="min-width:320px;max-width:98vw;">
            <li>
                <h6 class="dropdown-header d-flex justify-content-between align-items-center">
                    إشعارات
                </h6>
            </li>

            @forelse($allNotifications as $notif)
                <li class="notification-item px-3 py-2 small {{ $notif->read ? 'text-muted' : 'fw-bold text-secondary' }}">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-bell text-warning ms-2"></i>
                        <div>
                            <div class="fw-bold mb-1" style="font-size: 14px;">{{ $notif->title }}</div>
                            <div style="font-size: 13px;line-height:1.5;">
                                {{ $notif->body }}
                            </div>
                            <div class="text-muted mt-1" style="font-size: 11px;">
                                {{ $notif->created_at->diffForHumans() }}
                                @if($notif->read)
                                    <span class="badge bg-light border border-1 text-secondary ms-1">مقروء</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="notification-item px-3 py-2 text-center text-muted small">
                    لا توجد إشعارات بعد
                </li>
            @endforelse

            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('provider.profile') }}">
                    الملف الشخصي
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    تسجيل الخروج
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</header>

<style>
/* Remove Bootstrap’s default dropdown caret */
.btn.dropdown-toggle::after {
    display: none;
}

.notify-badge {
    position: absolute;
    top: -3px;
    left: -3px;
    background: #e3342f;
    color: #fff;
    border-radius: 9999px;
    font-size: 13px;
    min-width: 22px;
    min-height: 22px;
    padding: 0 7px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    z-index: 2;
    box-shadow: 0 0 6px #fff;
    transition: 0.2s;
}

.owner-name {
    font-size: 15px;
    font-weight: 500;
    color: #222;
    /* Optional ellipsis */
    max-width: 120px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    /* add some left margin to separate from arrow */
    margin-left: 5px;
}

.dropdown-arrow {
    font-size: 12px;
    color: #222;
    /* small right margin so it sits just left of the name */
    margin-right: 5px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Fetch unread count on page load
    function updateNotifyBadge() {
        fetch("{{ route('provider.notifications.unread_count') }}")
            .then(res => res.json())
            .then(data => {
                const badge = document.getElementById('notify-badge');
                if (badge) {
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error fetching unread notification count:', error));
    }

    updateNotifyBadge();

    // Optional: Poll for unread count every 30 seconds
    setInterval(updateNotifyBadge, 30000);

    // Existing logic for marking as read when opening dropdown
    const userMenuBtn = document.getElementById('userMenu');
    let notificationsMarkedAsRead = false;

    userMenuBtn.addEventListener('show.bs.dropdown', function() {
        const notifyBadge = document.getElementById('notify-badge');
        if (!notifyBadge || notificationsMarkedAsRead) {
            return;
        }

        fetch("{{ route('provider.notifications.read') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                notificationsMarkedAsRead = true;
                const unreadItems = document.querySelectorAll('.notification-item.fw-bold');
                unreadItems.forEach(item => {
                    item.classList.remove('fw-bold', 'text-secondary');
                    item.classList.add('text-muted');
                });

                if (notifyBadge) {
                    notifyBadge.style.transition = 'opacity 0.5s ease';
                    notifyBadge.style.opacity = '0';
                    setTimeout(() => {
                        notifyBadge.remove();
                    }, 500);
                }
            }
        }).catch(error => console.error('Error marking notifications as read:', error));
    });
});
</script>
