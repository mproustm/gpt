/* ==========================================================
 *  Main scripts – sidebar, dark-mode, notifications …etc.
 *  نسخة مُوحَّدة بلا تكرار أو تعارض
 * ========================================================== */
document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  /* عناصر مشتركة */
  const body          = document.body;
  const sidebar       = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebar-toggle');   // يظهر في الموبايل
  const mobileOverlay = document.getElementById('mobile-overlay');   // طبقة التعتيم للموبايل
  const hamburgers    = document.querySelectorAll('.hamburger');     // أزرار طيّ الشريط
  const themeBtn      = document.getElementById('theme-toggle');
  const themeIcon     = themeBtn?.querySelector('i');
  const notifyBadge   = document.getElementById('notify-badge');
  const clearNotify   = document.getElementById('clear-notify');
  const notifications = document.querySelectorAll('.notification-item');
  const deleteBtns    = document.querySelectorAll('.delete-btn');

  /* ------------------------------------------------------------------
   * 1) الشريط الجانبي – فتح في الموبايل / طيّ في الديسكتوب
   * ------------------------------------------------------------------ */
  /* 1-A  فتح ⇄ إغلاق في الموبايل */
  sidebarToggle?.addEventListener('click', () =>
    body.classList.toggle('sidebar-mobile-open')
  );
  mobileOverlay?.addEventListener('click', () =>
    body.classList.remove('sidebar-mobile-open')
  );
  /* إغلاق عند النقر خارج الشريط (موبايل) */
  document.addEventListener('click', e => {
    if (
      body.classList.contains('sidebar-mobile-open') &&
      !e.target.closest('.sidebar') &&
      !e.target.closest('#sidebar-toggle')
    ) {
      body.classList.remove('sidebar-mobile-open');
    }
  });

  /* 1-B  طيّ / فرد في الديسكتوب */
  function toggleSidebarCollapsed () {
    sidebar?.classList.toggle('sidebar-collapsed');
    body.classList.toggle('sidebar-collapsed');
    hamburgers.forEach(btn => btn.classList.toggle('active'));
  }
  hamburgers.forEach(btn => btn.addEventListener('click', toggleSidebarCollapsed));

  /* ------------------------------------------------------------------
   * 2) الوضع الداكن – يدوي فقط + ‎localStorage‎
   * ------------------------------------------------------------------ */
  const THEME_KEY = 'theme';                       // 'dark' | 'light'
  const startDark = localStorage.getItem(THEME_KEY) === 'dark';
  body.classList.toggle('dark-mode', startDark);
  if (themeIcon) {
    themeIcon.classList.toggle('fa-sun',  startDark);
    themeIcon.classList.toggle('fa-moon', !startDark);
  }

  themeBtn?.addEventListener('click', () => {
    const dark = body.classList.toggle('dark-mode');
    localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light');
    themeIcon?.classList.toggle('fa-sun',  dark);
    themeIcon?.classList.toggle('fa-moon', !dark);
  });

  /* ------------------------------------------------------------------
   * 3) إشعارات – عدّاد وشارة
   * ------------------------------------------------------------------ */
  let unread = notifications.length;
  function updateBadge () {
    if (!notifyBadge) return;
    if (unread > 0) {
      notifyBadge.textContent = String(unread);
      notifyBadge.classList.remove('d-none');
    } else {
      notifyBadge.classList.add('d-none');
    }
  }
  updateBadge();

  clearNotify?.addEventListener('click', () => {
    unread = 0;
    updateBadge();
    notifications.forEach(n => n.classList.add('text-muted'));
  });

  /* ------------------------------------------------------------------
   * 4) تأكيد الحذف – لكل زرّ يحمل ‎.delete-btn‎
   * ------------------------------------------------------------------ */
  deleteBtns.forEach(btn =>
    btn.addEventListener('click', e => {
      if (!confirm('حذف هذا العنصر؟')) e.preventDefault();
    })
  );
});

/*  dashboard-demo-data.js
 *  يحقن بيانات وهميّة منطقية ويُنشئ الرسوم البيانية
 *  ضَع هذا الملف بعد تحميل Chart.js و قبل إغلاق </body>
 */

document.addEventListener('DOMContentLoaded', () => {

    /* ===== 1- بيانات وهمية مرتّبة حسب السنة ===== */
    const DATA = {
        2023: {
            players  : 690,
            bookings : 2880,
            monthlyRevenue: [100,105,110,115,115,110,100,110,105,115,110,125], // مجموعها 1320
            monthlyExpense: [65,70,75,70,75,70,65,70,70,75,75,80]              // مجموعها 860
        },
        2024: {
            players  : 780,
            bookings : 3120,
            monthlyRevenue: [110,115,120,125,120,115,130,125,110,120,130,130], // 1450
            monthlyExpense: [70,75,80,80,80,75,85,80,70,75,80,70]              // 920
        },
        2025: {
            players  : 820,
            bookings : 3450,
            monthlyRevenue: [115,120,125,130,135,120,125,130,115,120,135,150], // 1520
            monthlyExpense: [75,80,85,90,85,80,90,85,75,80,67,78]              // 970
        }
    };

    /* نقوم باستخراج السنوات ديناميكيًا */
    const YEARS = Object.keys(DATA).map(Number).sort();

    /* ===== 2- عناصر DOM ===== */
    const yearSelect     = document.getElementById('yearSelect');
    const playersCountEl = document.getElementById('players-count');
    const bookingsCountEl= document.getElementById('bookings-count');
    const revenueEl      = document.getElementById('revenue-v');
    const expenseEl      = document.getElementById('expense-v');
    const profitEl       = document.getElementById('profit-v');

    /* ===== 3- إنشاء خيارات السنة ===== */
    YEARS.forEach(yr => {
        const opt = document.createElement('option');
        opt.value = yr;
        opt.textContent = yr;
        yearSelect.appendChild(opt);
    });
    yearSelect.value = Math.max(...YEARS);     // افتراضيًا أحدث سنة

    /* ===== 4- تهيئة الرسوم البيانية (فارغة) ===== */
    const monthLabels = ['يناير','فبراير','مارس','أبريل','مايو','يونيو',
                         'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

    /* سنُخزّن الكائنات لإعادة استخدامها عند تغيير السنة */
    let revExpChart  = null;
    let cashFlowChart= null;

    /* ===== 5- دالة تحديث شاملة ===== */
    function updateDashboard(year){
        const d = DATA[year];

        /* --- البطاقات --- */
        playersCountEl.textContent   = d.players.toLocaleString();
        bookingsCountEl.textContent  = d.bookings.toLocaleString();

        const totalRevenue = d.monthlyRevenue.reduce((a,b)=>a+b,0); // = آلاف الدينار
        const totalExpense = d.monthlyExpense.reduce((a,b)=>a+b,0);
        const totalProfit  = totalRevenue - totalExpense;

        revenueEl.textContent = totalRevenue.toLocaleString();
        expenseEl.textContent = totalExpense.toLocaleString();
        profitEl .textContent = totalProfit .toLocaleString();

        /* --- بيانات المخططات --- */
        const cashFlow = d.monthlyRevenue.map((v,i)=> v - d.monthlyExpense[i]);

        /* إيرادات/مصروفات */
        if (revExpChart) revExpChart.destroy();
        revExpChart = new Chart(document.getElementById('revExpLine'), {
            type:'line',
            data:{
                labels: monthLabels,
                datasets:[
                    {label:'الإيرادات', data:d.monthlyRevenue, tension:0.35},
                    {label:'المصروفات', data:d.monthlyExpense, tension:0.35}
                ]
            },
            options:{responsive:true, maintainAspectRatio:false}
        });

        /* التدفّق النقدي */
        if (cashFlowChart) cashFlowChart.destroy();
        cashFlowChart = new Chart(document.getElementById('cashFlowLine'), {
            type:'bar',
            data:{labels:monthLabels, datasets:[{label:'صافي التدفق', data:cashFlow}]},
            options:{responsive:true, maintainAspectRatio:false}
        });
    }

    /* ===== 6- تشغيل لأول مرة، ثم عند اختيار سنة ===== */
    updateDashboard(yearSelect.value);

    yearSelect.addEventListener('change', e => updateDashboard(e.target.value));

});

/*  dashboard-charts.js
 *  ▶ يحوّل الرسوم مع الـ Dark-mode
 *  ▶ يحقن بيانات وهميّة ويُنشئ المخططات
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ───── 1. بيانات وهميّة ───── */
  const DATA = {
    2023:{players:690,bookings:2880,
          rev:[100,105,110,115,115,110,100,110,105,115,110,125],
          exp:[65,70,75,70,75,70,65,70,70,75,75,80]},
    2024:{players:780,bookings:3120,
          rev:[110,115,120,125,120,115,130,125,110,120,130,130],
          exp:[70,75,80,80,80,75,85,80,70,75,80,70]},
    2025:{players:820,bookings:3450,
          rev:[115,120,125,130,135,120,125,130,115,120,135,150],
          exp:[75,80,85,90,85,80,90,85,75,80,67,78]}
  };

  const MONTHS = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  const yearSelect = document.getElementById('yearSelect');
  const years = Object.keys(DATA).map(Number).sort();
  years.forEach(y => yearSelect.insertAdjacentHTML('beforeend', `<option>${y}</option>`));
  yearSelect.value = Math.max(...years);

  /* ───── 2. متغيّرات DOM للبطاقات ───── */
  const el = id => document.getElementById(id);

  /* ───── 3. تيم (ألوان) يتبع الوضع ───── */
  function chartTheme(){
    const dark = document.body.classList.contains('dark-mode');
    return {
      text : dark ? '#e6e6e6' : '#333',
      grid : dark ? 'rgba(255,255,255,.15)' : 'rgba(0,0,0,.08)',
      tooltipBg : dark ? '#1b2227' : '#fff',
      series : dark
               ? ['#64b5f6','#f48fb1']        // ألوان أوضح فوق الخلفيّة الداكنة
               : ['#2196f3','#e91e63']
    };
  }

  /* طبّق التيم على إعدادات Chart.js العامة */
  function applyChartTheme(){
    const t = chartTheme();
    Chart.defaults.color = t.text;
    Chart.defaults.borderColor = t.grid;
    Chart.defaults.plugins.tooltip.backgroundColor = t.tooltipBg;
  }
  applyChartTheme();

  /* ───── 4. إنشاء الرسوم وإعادة استخدامها ───── */
  const charts = [];     // نخزّن كل رسومنا هنا لتسهيل update()
  function render(year){
    const d = DATA[year];
    const revSum = d.rev.reduce((a,b)=>a+b,0);
    const expSum = d.exp.reduce((a,b)=>a+b,0);

    /* بطاقات */
    el('players-count').textContent   = d.players;
    el('bookings-count').textContent  = d.bookings;
    el('revenue-v').textContent       = revSum;
    el('expense-v').textContent       = expSum;
    el('profit-v').textContent        = revSum - expSum;

    /* ألوان السلاسل */
    const pal = chartTheme().series;

    /* حذف المخططات القديمة */
    charts.forEach(c => c.destroy());
    charts.length = 0;

    /* 1) خط الإيرادات/المصروفات */
    charts.push(new Chart(el('revExpLine'), {
      type:'line',
      data:{labels:MONTHS,
            datasets:[
              {label:'الإيرادات', data:d.rev, borderColor:pal[0], backgroundColor:hexA(pal[0]), tension:.35, fill:true, pointRadius:2},
              {label:'المصروفات',data:d.exp, borderColor:pal[1], backgroundColor:hexA(pal[1]), tension:.35, fill:true, pointRadius:2}
            ]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}
    }));

    /* 2) عمود التدفق النقدى */
    const cash = d.rev.map((v,i)=>v-d.exp[i]);
    charts.push(new Chart(el('cashFlowLine'), {
      type:'bar',
      data:{labels:MONTHS,datasets:[{label:'صافي التدفق',data:cash,backgroundColor:pal[0]}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}
    }));
  }

  /* hex إلى rgba شفاف */
  const hexA = (hex,op=.25)=>hex+Math.round(op*255).toString(16).padStart(2,'0');

  /* تشغيل أولي + تغيّر السنة */
  render(yearSelect.value);
  yearSelect.addEventListener('change',e=>render(e.target.value));

  /* ───── 5. ربط مفتاح السِمة (موجود فى main.js) ───── */
  const themeBtn = document.getElementById('theme-toggle');
  themeBtn?.addEventListener('click',()=>{
    /* main.js يضيف/يحذف ‎.dark-mode‎ .. نحافظ هنا فقط */
    applyChartTheme();           // أعد ألوان الافتراض
    charts.forEach(c=>c.update());// حدث كل الرسوم
  });

});

/*  dashboard-charts.js  –  يملأ الجداول ويرسم المخططات المتوافقة مع الوضع الداكن */

document.addEventListener('DOMContentLoaded', () => {

  /* ── 1. بيانات خام ──────────────────────────────────────── */
  const DATA = {
    2023:{players:690,bookings:2880,
          rev:[100,105,110,115,115,110,100,110,105,115,110,125],
          exp:[65,70,75,70,75,70,65,70,70,75,75,80]},
    2024:{players:780,bookings:3120,
          rev:[110,115,120,125,120,115,130,125,110,120,130,130],
          exp:[70,75,80,80,80,75,85,80,70,75,80,70]},
    2025:{players:820,bookings:3450,
          rev:[115,120,125,130,135,120,125,130,115,120,135,150],
          exp:[75,80,85,90,85,80,90,85,75,80,67,78]}
  };

  /* بيانات العملاء (ثابتة) */
  const CLIENTS = [
    {name:'Libya Telecom',      value:310},
    {name:'Al-Nasr FC',         value:210},
    {name:'Al-Tahaddi Academy', value:180},
    {name:'Tripoli Stars',      value:150},
    {name:'Sportify Ltd.',      value:120},
    {name:'GoalGetters',        value:100},
    {name:'GreenField Group',   value: 90},
    {name:'Benghazi United',    value: 80},
    {name:'Future Play',        value: 70},
    {name:'Elite Sports',       value:140}
  ];

  const MONTHS = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

  /* ── 2. تحضير القوائم المنسدلة / العناصر ───────────────── */
  const yearSelect = document.getElementById('yearSelect');
  const years = Object.keys(DATA).map(Number).sort();
  years.forEach(y => yearSelect.insertAdjacentHTML('beforeend', `<option>${y}</option>`));
  yearSelect.value = Math.max(...years);

  const el = id => document.getElementById(id);

  /* ── 3. Theme helpers (Light / Dark) ───────────────────── */
  const charts = [];   // مصفوفة عالمية لكل المخططات

  function theme(){
    const dark = document.body.classList.contains('dark-mode');
    return {
      text : dark ? '#ffffff' : '#333333',
      grid : dark ? 'rgba(255,255,255,.15)' : 'rgba(0,0,0,.08)',
      tooltip : dark ? '#1a1e23' : '#ffffff',
      series : dark
               ? ['#64b5f6','#ff8a80']   // أفتح على الخلفيّة الداكنة
               : ['#2196f3','#e91e63']
    };
  }

  function applyChartTheme(){
    const t = theme();
    Chart.defaults.color       = t.text;
    Chart.defaults.borderColor = t.grid;
    Chart.defaults.plugins.tooltip.backgroundColor = t.tooltip;
  }
  applyChartTheme();

  /* ── 4. hex+opacity helper ─────────────────────────────── */
  const hexA = (hex,op=.25)=>hex+Math.round(op*255).toString(16).padStart(2,'0');

  /* ── 5. تعبئة جداول ثابتة مرة واحدة ─────────────────────── */
  (function fillStaticTables(){
    /* جدول العملاء */
    const tbody = document.querySelector('#clients-table tbody');
    const total = CLIENTS.reduce((a,b)=>a+b.value,0);
    CLIENTS.sort((a,b)=>b.value-a.value).forEach((c,i)=>{
      tbody.insertAdjacentHTML('beforeend',`
        <tr>
          <td>${i+1}</td>
          <td>${c.name}</td>
          <td>${c.value}</td>
          <td>${((c.value/total)*100).toFixed(1)}%</td>
        </tr>`);
    });

    /* جدول تفاصيل سنوية */
    const ybody = document.querySelector('#yearly-table tbody');
    years.forEach(y=>{
      const d   = DATA[y];
      const rev = d.rev.reduce((a,b)=>a+b,0);
      const exp = d.exp.reduce((a,b)=>a+b,0);
      ybody.insertAdjacentHTML('beforeend',`
        <tr>
          <td>${y}</td>
          <td>${d.players}</td>
          <td>${d.bookings}</td>
          <td>${rev}</td>
          <td>${exp}</td>
          <td>${rev-exp}</td>
        </tr>`);
    });
  })();

  /* ── 6. دالة رسم وتحديث البطاقات ──────────────────────── */
  function render(year){
    const d = DATA[year];
    const revSum = d.rev.reduce((a,b)=>a+b,0);
    const expSum = d.exp.reduce((a,b)=>a+b,0);

    /* بطاقات علوية */
    el('players-count').textContent  = d.players;
    el('bookings-count').textContent = d.bookings;
    el('revenue-v').textContent      = revSum;
    el('expense-v').textContent      = expSum;
    el('profit-v').textContent       = revSum - expSum;

    /* ألوان السلاسل */
    const pal = theme().series;

    /* مسح رسوم قديمة */
    charts.forEach(c=>c.destroy());
    charts.length = 0;

    /* 1) خط الإيرادات/المصروفات */
    charts.push(new Chart(el('revExpLine'),{
      type:'line',
      data:{labels:MONTHS,
            datasets:[
              {label:'الإيرادات', data:d.rev, borderColor:pal[0], backgroundColor:hexA(pal[0]), pointRadius:2, tension:.35, fill:true},
              {label:'المصروفات',data:d.exp, borderColor:pal[1], backgroundColor:hexA(pal[1]), pointRadius:2, tension:.35, fill:true}
            ]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}
    }));

    /* 2) عمود التدفق النقدي */
    const cash = d.rev.map((v,i)=>v-d.exp[i]);
    charts.push(new Chart(el('cashFlowLine'),{
      type:'bar',
      data:{labels:MONTHS,datasets:[{label:'صافي التدفق',data:cash,backgroundColor:pal[0]}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}}}
    }));
  }

  /* ── 7. تشغيل أولي + تغيير سنة ────────────────────────── */
  render(yearSelect.value);
  yearSelect.addEventListener('change',e=>render(e.target.value));

  /* ── 8. عند تبديل الثيم (الزر موجود فى main.js) ───────── */
  document.getElementById('theme-toggle')?.addEventListener('click',()=>{
    applyChartTheme(); charts.forEach(c=>c.update());
  });

});
/* بيانات العملاء (عدّل الأرقام أو الأسماء) */
const CLIENTS = [
  {name:'Libya Telecom',      value:310},
  {name:'Al-Nasr FC',         value:210},
  {name:'Al-Tahaddi Academy', value:180},
  {name:'Tripoli Stars',      value:150},
  {name:'Sportify Ltd.',      value:120}
];

/* لوحة ألوان تتغير مع الوضع */
function clientPalette(){
  const dark = document.body.classList.contains('dark-mode');
  return dark
    ? ['#64b5f6','#81c784','#ffb74d','#ba68c8','#4db6ac']
    : ['#2196f3','#4caf50','#ff9800','#9c27b0','#009688'];
}

function drawTopClients(){
  /* دمّر الرسم القديم إن وجد */
  window.topClientsChart?.destroy();

  window.topClientsChart = new Chart(
    document.getElementById('topClientsCol'),
    {
      type:'bar',
      data:{
        labels:CLIENTS.map(c=>c.name),
        datasets:[{
          label:'إيراد (ألف)',
          data:CLIENTS.map(c=>c.value),
          backgroundColor:clientPalette()
        }]
      },
      options:{
        indexAxis:'y',
        responsive:true,
        maintainAspectRatio:false,
        plugins:{legend:{display:false}}
      }
    }
  );
}

drawTopClients();                      // تشغيل أولي
document.getElementById('theme-toggle')
  ?.addEventListener('click', ()=>{ applyChartTheme(); drawTopClients(); });

  /* بيانات المصادر */
const REVENUE_SOURCES = [
  {label:'حجوزات ملاعب', value:870},
  {label:'بطولات',       value:400},
  {label:'رعايات',       value:250}
];

/* ألوان دائرية */
function piePalette(){
  const dark = document.body.classList.contains('dark-mode');
  return dark
    ? ['#64b5f6','#ff8a80','#fdd835']
    : ['#2196f3','#e91e63','#fbc02d'];
}

function drawRevSources(){
  window.revSourceChart?.destroy();

  window.revSourceChart = new Chart(
    document.getElementById('revSourcePie'),
    {
      type:'pie',
      data:{
        labels : REVENUE_SOURCES.map(r=>r.label),
        datasets:[{data:REVENUE_SOURCES.map(r=>r.value),
                   backgroundColor:piePalette()}]
      },
      options:{responsive:true,maintainAspectRatio:false}
    }
  );
}

drawRevSources();
document.getElementById('theme-toggle')
  ?.addEventListener('click', ()=>{ applyChartTheme(); drawRevSources(); });

document.addEventListener('keydown', e=>{
  if(e.key==='Escape'){
    const open = document.querySelector('.dropdown-menu.show');
    open?.classList.remove('show');
    open?.closest('.dropdown')?.querySelector('[data-bs-toggle="dropdown"]')?.setAttribute('aria-expanded','false');
  }
});
