@extends('provider.provider_layouts.dashboard2')

@section('page_title', 'التقارير والإحصائيات')

@section('content')

    <!-- أدوات السنة + الشهر + تصدير -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <label class="fw-bold mb-0">السنة:</label>
        <select id="yearSelect" class="form-select form-select-sm w-auto"></select>

        <label class="fw-bold mb-0 ms-2">الشهر:</label>
        <select id="monthSelect" class="form-select form-select-sm w-auto"></select>

        <div class="dropdown export-print ms-auto">
            <button class="btn btn-success btn-sm d-flex align-items-center gap-1"
                    id="exportMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-export"></i>
                <span>تصدير / طباعة</span>
                <i class="fas fa-chevron-down small flex-shrink-0"></i>
            </button>

            <ul class="dropdown-menu shadow-sm rounded-3" aria-labelledby="exportMenu">
                <!-- الطباعة -->
                <li class="dropdown-header">خيارات الطباعة</li>
                <li>
                    <a href="#" class="dropdown-item print-option" data-target="all">
                        <i class="fas fa-print fa-fw"></i> طباعة الكل
                    </a>
                </li>
                <li>
                    <a href="#" class="dropdown-item print-option" data-target="finance-section">
                        <i class="fas fa-hand-holding-dollar fa-fw"></i> تقرير مالي
                    </a>
                </li>
                <li>
                    <a href="#" class="dropdown-item print-option" data-target="clients-section">
                        <i class="fas fa-users fa-fw"></i> تقرير العملاء
                    </a>
                </li>

                <li><hr class="dropdown-divider my-1"></li>

                <!-- التصدير -->
                <li class="dropdown-header">تصدير ملف</li>
                <li>
                    <a href="#" id="exportExcel" class="dropdown-item">
                        <i class="fa-brands fa-file-excel text-success fa-fw"></i> Excel (الجدول السنوي)
                    </a>
                </li>
                <li>
                    <a href="#" id="exportPDF" class="dropdown-item">
                        <i class="fas fa-file-pdf text-danger fa-fw"></i> PDF (الجدول السنوي)
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- بطاقات الإحصاءات -->
    <div class="row" data-printable>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-icon bg-players"><i class="fas fa-users"></i></div>
                <div>
                    <span class="stats-label">عدد اللاعبين (مميّزين)</span>
                    <p class="stats-value" id="players-count">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-icon bg-stadiums"><i class="fas fa-futbol"></i></div>
                <div>
                    <span class="stats-label">ملاعبك</span>
                    <p class="stats-value" id="stadiums-count">0</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-icon bg-bookings"><i class="fas fa-calendar-check"></i></div>
                <div>
                    <span class="stats-label">الحجوزات</span>
                    <p class="stats-value" id="bookings-count">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- القسم المالي (إيرادات فقط) -->
    <section id="finance-section" class="mt-4" data-printable>
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon bg-revenue"><i class="fas fa-hand-holding-usd"></i></div>
                    <div>
                        <span class="stats-label">الإيرادات</span>
                        <p class="stats-value" id="revenue-v">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- خط الإيرادات -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="content-box">
                    <h2><i class="fas fa-chart-line"></i> الإيرادات</h2>
                    <div class="chart-wrapper">
                        <canvas id="revenueLine"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- قسم العملاء -->
    <section id="clients-section" class="mt-4" data-printable>
        <div class="content-box">
            <h2><i class="fas fa-user-tag"></i> العملاء الأكثر دخلاً</h2>
            <div class="table-responsive">
                <table id="clients-table" class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العميل</th>
                            <th>إيراد</th>
                            <th>عدد الحجوزات</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- تعبئة عبر الجلب --}}
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- جدول سنوي -->
    <div class="content-box" data-printable>
        <h2><i class="fas fa-table"></i> تفاصيل سنويّة</h2>
        <div class="table-responsive">
            <table id="yearly-table" class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>السنة</th>
                        <th>الشهر</th>
                        <th>اللاعبون</th>
                        <th>الحجوزات</th>
                        <th>الإيراد</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- صفوف تُملأ حسب اختيار السنة/الشهر --}}
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('styles')
<style>
/* صندوق المحتوى */
.content-box{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:1rem .75rem;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:1rem;display:flex;flex-direction:column}
.content-box h2{font-size:1rem;margin-bottom:.4rem;display:flex;align-items:center;gap:.35rem}

/* بطاقات الإحصاءات */
.stats-card{display:flex;gap:.75rem;align-items:center;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:.9rem .9rem;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.stats-icon{width:42px;height:42px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff}
.bg-players{background:#2563eb}.bg-stadiums{background:#16a34a}.bg-bookings{background:#f59e0b}.bg-revenue{background:#8b5cf6}
.stats-label{display:block;font-size:.85rem;color:#6b7280}
.stats-value{margin:0;font-size:1.4rem;font-weight:700}

/* الرسم البياني */
.chart-wrapper{position:relative;width:100%;height:220px;flex:0 0 auto;overflow:hidden;margin:auto 0 .75rem;background:#fafafa;border-radius:6px}
@media(max-width:576px){.chart-wrapper{height:180px}}
.chart-wrapper canvas{position:absolute;inset:0;width:100%!important;height:100%!important;display:block}

/* أسطورة (إن لزم) */
.chartjs-legend{display:flex!important;justify-content:center;flex-wrap:wrap;gap:.6rem;margin:.4rem 0 0;padding:0;list-style:none;font-size:.75rem}
.chartjs-legend li{display:flex;align-items:center;gap:.25rem}
.chartjs-legend li span{width:12px;height:12px;border-radius:2px}
</style>
@endpush

@push('scripts')
    <!-- مكتبات -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js" defer></script>

    <script>
    document.addEventListener('DOMContentLoaded', async function () {
        const yearSelect    = document.getElementById('yearSelect');
        const monthSelect   = document.getElementById('monthSelect');
        const playersEl     = document.getElementById('players-count');
        const stadiumsEl    = document.getElementById('stadiums-count');
        const bookingsEl    = document.getElementById('bookings-count');
        const revenueEl     = document.getElementById('revenue-v');
        const clientsTable  = document.querySelector('#clients-table tbody');
        const yearlyTable   = document.querySelector('#yearly-table tbody');
        const dataUrl       = "{{ route('provider.reports.data') }}";

        const fmtInt  = new Intl.NumberFormat('ar-LY');
        const fmtCurr = new Intl.NumberFormat('ar-LY', { style: 'currency', currency: 'LYD', minimumFractionDigits: 2 });

        // تعبئة السنوات والشهور
        (function initSelectors(){
            const now = new Date();
            const thisYear  = now.getFullYear();
            const thisMonth = now.getMonth() + 1;

            if (!yearSelect.options.length) {
                for (let y = thisYear + 2; y >= thisYear - 4; y--) {
                    const opt = document.createElement('option');
                    opt.value = y; opt.textContent = y;
                    if (y === thisYear) opt.selected = true;
                    yearSelect.appendChild(opt);
                }
            }
            if (!monthSelect.options.length) {
                const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
                const allOpt = document.createElement('option');
                allOpt.value = 0; allOpt.textContent = 'كل الشهور';
                monthSelect.appendChild(allOpt);
                for (let m = 1; m <= 12; m++) {
                    const opt = document.createElement('option');
                    opt.value = m; opt.textContent = `${months[m-1]} (${m})`;
                    if (m === thisMonth) opt.selected = true;
                    monthSelect.appendChild(opt);
                }
            }
        })();

        // رسم خط الإيرادات
        let chart;
        function renderChart(labels, series) {
            const canvas = document.getElementById('revenueLine');
            if (!canvas || !window.Chart) return;
            const ctx = canvas.getContext('2d');
            if (chart) chart.destroy();
            chart = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets: [{ label: 'الإيرادات', data: series, tension: .25 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: true }, tooltip: { enabled: true } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        async function loadData() {
            const params = new URLSearchParams({
                year: yearSelect.value,
                month: monthSelect.value
            });
            const res  = await fetch(`${dataUrl}?${params.toString()}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!res.ok) return;
            const json = await res.json();

            // Counters
            playersEl.textContent  = fmtInt.format(json.totals.players);
            stadiumsEl.textContent = fmtInt.format(json.totals.stadiums);
            bookingsEl.textContent = fmtInt.format(json.totals.bookings);
            revenueEl.textContent  = fmtCurr.format(json.totals.revenue);

            // Chart
            renderChart(json.chart.labels, json.chart.series);

            // Top clients
            clientsTable.innerHTML = '';
            json.top_clients.forEach((c, i) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${i+1}</td>
                                <td>${c.name}</td>
                                <td>${fmtCurr.format(c.revenue)}</td>
                                <td>${fmtInt.format(c.bookings)}</td>`;
                clientsTable.appendChild(tr);
            });

            // Yearly table
            yearlyTable.innerHTML = '';
            const monthNames = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
            json.yearly.forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${r.year}</td>
                                <td>${monthNames[r.month-1]} (${r.month})</td>
                                <td>${fmtInt.format(r.players)}</td>
                                <td>${fmtInt.format(r.bookings)}</td>
                                <td>${fmtCurr.format(r.revenue)}</td>`;
                yearlyTable.appendChild(tr);
            });
        }

        // تحميل أولي + مستمعي التغيير
        await loadData();
        yearSelect.addEventListener('change', loadData);
        monthSelect.addEventListener('change', loadData);

        // الطباعة
        document.querySelectorAll('.print-option').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const target = this.dataset.target;
                if (target === 'all') { window.print(); return; }
                const section = document.getElementById(target);
                if (!section) return;
                const w = window.open('', '_blank', 'width=1024,height=768');
                w.document.write('<html><head><title>طباعة</title>');
                document.querySelectorAll('link[rel=stylesheet], style').forEach(tag => w.document.write(tag.outerHTML));
                w.document.write('</head><body dir="rtl">');
                w.document.write(section.outerHTML);
                w.document.write('</body></html>');
                w.document.close(); w.focus(); w.print(); w.close();
            });
        });

        // التصدير: Excel و PDF لجدول السنة
        const yearlyTableEl = document.getElementById('yearly-table');
        const exportExcel = document.getElementById('exportExcel');
        const exportPDF   = document.getElementById('exportPDF');

        if (exportExcel && yearlyTableEl) {
            exportExcel.addEventListener('click', function(e){
                e.preventDefault();
                if (!window.XLSX) return;
                const wb = XLSX.utils.table_to_book(yearlyTableEl, {sheet: 'Yearly'});
                XLSX.writeFile(wb, 'yearly_report.xlsx');
            });
        }

        if (exportPDF && yearlyTableEl) {
            exportPDF.addEventListener('click', async function(e){
                e.preventDefault();
                if (!window.jspdf || !window.jspdf.jsPDF) return;
                const doc = new window.jspdf.jsPDF({orientation:'landscape'});
                doc.text('التقرير السنوي', 14, 12);
                if (doc.autoTable) doc.autoTable({ html: '#yearly-table', startY: 16 });
                doc.save('yearly_report.pdf');
            });
        }
    });
    </script>
@endpush
