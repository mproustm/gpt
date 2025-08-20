/*  ╔══════════════════════════════════════════════════════╗
    ║  PROVIDER DASHBOARD JS –  RTL                        ║
    ╚══════════════════════════════════════════════════════╝ */

document.addEventListener('DOMContentLoaded', () => {

  /* ------------------------------------------------------------------
   * عناصر DOM
   * ------------------------------------------------------------------ */
  const body           = document.body;
  const sidebar        = document.getElementById('sidebar');
  const sidebarToggle  = document.getElementById('sidebar-toggle'); // موبايل
  const collapseBtn    = document.getElementById('collapse-btn');   // ديسكتوب
  const mobileOverlay  = document.getElementById('mobile-overlay');
 const themeBtn   = document.getElementById('provider-theme-toggle');
const themeIcon  = themeBtn?.querySelector('i');
  const notifyBadge    = document.getElementById('notify-badge');
  const clearNotify    = document.getElementById('clear-notify');
  const notifications  = document.querySelectorAll('.notification-item');
  const deleteBtns     = document.querySelectorAll('.delete-btn');
  const hamburgers     = document.querySelectorAll('.hamburger');

  /* ------------------------------------------------------------------
   * 1) الشريط الجانبي (موبايل + ديسكتوب)
   * ------------------------------------------------------------------ */

  // موبايل: فتح / إغلاق
  sidebarToggle?.addEventListener('click', () =>
    body.classList.toggle('sidebar-mobile-open')
  );

  // إغلاق عند الضغط على التعتيم أو خارج الشريط
  function closeMobileSidebar(e){
    if (
      body.classList.contains('sidebar-mobile-open') &&
      !e.target.closest('#sidebar') &&
      !e.target.closest('#sidebar-toggle')
    ){
      body.classList.remove('sidebar-mobile-open');
    }
  }
  mobileOverlay?.addEventListener('click', closeMobileSidebar);
  document.addEventListener('click', closeMobileSidebar);

  // ديسكتوب: طيّ / فرد
  function toggleCollapsed(){
    sidebar?.classList.toggle('sidebar-collapsed');
    body.classList.toggle('sidebar-collapsed');
    hamburgers.forEach(btn => btn.classList.toggle('active'));
  }
  hamburgers.forEach(btn => btn.addEventListener('click', toggleCollapsed));
  collapseBtn?.addEventListener('click', toggleCollapsed);

  /* ------------------------------------------------------------------
 * 2) الوضع الليلي (Light / Dark)
 * ------------------------------------------------------------------ */
/* ------------------------------------------------------------------
 * 2) الوضع الليلي (Light / Dark) – لوحة المزوّد
 * ------------------------------------------------------------------ */
const THEME_KEY  = 'provider_theme';                 // مفتاح تخزين مستقل

/* فعِّل الداكن عند التحميل إن كان مخزّناً */
if (localStorage.getItem(THEME_KEY) === 'dark') {
  document.body.classList.add('dark-mode');
  themeIcon?.classList.replace('fa-moon', 'fa-sun');
}

/* عند الضغط: بدِّل وخزّن */
themeBtn?.addEventListener('click', () => {
  const dark = document.body.classList.toggle('dark-mode');
  localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light');
  themeIcon?.classList.toggle('fa-sun',  dark);
  themeIcon?.classList.toggle('fa-moon', !dark);
});


  /* ------------------------------------------------------------------
   * 3) الإشعارات
   * ------------------------------------------------------------------ */
  let unread = notifications.length;
  function updateBadge(){
    if (!notifyBadge) return;
    if (unread){
      notifyBadge.textContent = String(unread);
      notifyBadge.classList.remove('d-none');
    }else{
      notifyBadge.classList.add('d-none');
    }
  }
  updateBadge();

  clearNotify?.addEventListener('click', () => {
    unread = 0;
    notifications.forEach(n => n.classList.add('text-muted'));
    updateBadge();
  });

  /* ------------------------------------------------------------------
   * 4) تأكيدات الحذف
   * ------------------------------------------------------------------ */
  deleteBtns.forEach(btn => {
    btn.addEventListener('click', e => {
      if (!confirm('هل تريد حذف هذا العنصر؟')){
        e.preventDefault();
      }
    });
  });

});


