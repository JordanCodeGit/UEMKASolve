@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="summary-grid">

        <div class="summary-card summary-saldo">
            <div class="card-content">
                <div class="card-header">
                    <div class="icon-box icon-white">
                        <img src="{{ asset('icons/saldo.png') }}" alt="Saldo" class="icon-img">
                    </div>
                    <p>SALDO</p>
                </div>
                <h3 id="summary-saldo">Memuat...</h3>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-content">
                <div class="card-header">
                    <div class="icon-box icon-blue">
                        <img src="{{ asset('icons/wallet.png') }}" alt="Pemasukan" class="icon-img">
                    </div>
                    <p>Pemasukan</p>
                </div>
                <h3 id="summary-pemasukan">Memuat...</h3>
                <div class="percentage-badge badge-green" id="summary-pemasukan-pct">
                    <img class="pct-icon" id="summary-pemasukan-pct-icon" src="{{ asset('icons/upp_green.png') }}"
                        alt="Trend">
                    <span id="summary-pemasukan-pct-text"></span>
                </div>
            </div>
            <div class="card-trend-icon icon-green" id="summary-pemasukan-trend">
                <div class="trend-icon-bg trend-bg-blue"></div>
                <img src="{{ asset('icons/up.png') }}" alt="Trend" class="trend-icon" id="summary-pemasukan-arrow">
            </div>
        </div>

        <div class="summary-card">
            <div class="card-content">
                <div class="card-header">
                    <div class="icon-box icon-blue">
                        <img src="{{ asset('icons/wallet.png') }}" alt="Pengeluaran" class="icon-img">
                    </div>
                    <p>Pengeluaran</p>
                </div>
                <h3 id="summary-pengeluaran">Memuat...</h3>
                <div class="percentage-badge badge-red" id="summary-pengeluaran-pct">
                    <img class="pct-icon" id="summary-pengeluaran-pct-icon" src="{{ asset('icons/upp_orange.png') }}"
                        alt="Trend">
                    <span id="summary-pengeluaran-pct-text"></span>
                </div>
            </div>
            <div class="card-trend-icon icon-red" id="summary-pengeluaran-trend">
                <div class="trend-icon-bg trend-bg-orange"></div>
                <img src="{{ asset('icons/up_orange.png') }}" alt="Trend" class="trend-icon"
                    id="summary-pengeluaran-arrow">
            </div>
        </div>

        <div class="summary-card">
            <div class="card-content">
                <div class="card-header">
                    <div class="icon-box icon-blue">
                        <img src="{{ asset('icons/money.png') }}" alt="Laba" class="icon-img">
                    </div>
                    <p>Laba</p>
                </div>
                <h3 id="summary-laba">Memuat...</h3>
                <div class="percentage-badge badge-green" id="summary-laba-pct">
                    <img class="pct-icon" id="summary-laba-pct-icon" src="{{ asset('icons/upp_green.png') }}"
                        alt="Trend">
                    <span id="summary-laba-pct-text"></span>
                </div>
            </div>
            <div class="card-trend-icon icon-green" id="summary-laba-trend">
                <div class="trend-icon-bg trend-bg-blue"></div>
                <img src="{{ asset('icons/up.png') }}" alt="Trend" class="trend-icon" id="summary-laba-arrow">
            </div>
        </div>
    </div>

    <div class="chart-grid">
        <div class="content-card">
            <div class="card-header card-header-grafik-kas">
                <div class="card-title-with-icon">
                    <img src="{{ asset('icons/transaction_icon.png') }}" alt="Icon" class="custom-title-icon">
                    <h3 class="card-title">Grafik Kas</h3>
                </div>
                <div class="grafik-kas-header">
                    <div class="filter-container">

                        <div class="dropdown-with-icon" id="dashboard-filter-wrapper"
                            style="position: relative; display: inline-block;">

                            <div id="dashboard-filter-btn" class="dropdown-minimalis-grafik"
                                style="min-width: 150px; justify-content: space-between; padding: 10px 14px; border: none; border-radius: 8px; background: var(--bg-primary); display: flex; align-items: center; cursor: pointer;">
                                <img src="{{ asset('icons/kalendar.png') }}" alt="calendar"
                                    style="font-size: 14px; color: var(--text-secondary); margin-right: 6px; width: 16px; height: 16px;">
                                <span style="font-size: 13px; font-weight: 500;">Bulan Ini</span>
                                <i class="fa-solid fa-chevron-down"
                                    style="font-size: 12px; color: var(--text-secondary); margin-left: auto;"></i>
                            </div>

                            <div class="dropdown-menu-custom" id="dashboard-filter-menu" style="display: none;">
                                <div class="dropdown-item active" data-value="bulan_ini">Bulan Ini</div>
                                <div class="dropdown-item" data-value="bulan_lalu">Bulan Lalu</div>
                                <div class="dropdown-item" data-value="semua">Semua</div>

                                <div class="dropdown-divider"></div>

                                <div class="dropdown-item-custom">
                                    <label for="dashboard-month-picker">Pilih Bulan:</label>
                                    <input type="month" id="dashboard-month-picker" class="form-input-month">
                                </div>
                            </div>

                        </div>

                        <div class="grafik-kas-legend">
                            <div class="legend-item">
                                <span class="legend-dot" style="background-color: #5EDB65;"></span>
                                <span class="legend-text">Pemasukan</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-dot" style="background-color: #FFA142;"></span>
                                <span class="legend-text">Pengeluaran</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="card-body card-body-grafik-kas">
                <div class="chart-container" id="lineChartContainer">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="card-header card-header-persentase-kas">
                <div class="card-title-with-icon">
                    <img src="{{ asset('icons/transaction_icon.png') }}" alt="Icon" class="custom-title-icon">
                    <h3 class="card-title">Persentase Kas</h3>
                </div>
                <select id="doughnut-type-filter" class="dropdown-minimalis" onchange="loadDashboardData()">
                    <option value="pengeluaran">Pengeluaran</option>
                    <option value="pemasukan">Pemasukan</option>
                </select>
            </div>
            <div class="card-body card-body-persentase-kas">
                <div class="chart-container-horizontal">
                    <div class="chart-wrapper">
                        <canvas id="doughnutChart"></canvas>
                    </div>
                    <ul class="doughnut-legend" id="doughnut-legend-list">
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-transaksi-terakhir">

        <div class="card-header">
            <h3>
                <img src="{{ asset('icons/transaction_icon.png') }}" alt="Icon" class="custom-title-icon">
                Transaksi Terakhir
            </h3>

        </div>

        <div class="card-body">
            <ul id="recent-transactions-list" class="transaction-list">

                <li class="transaction-item" style="justify-content: center; color: var(--text-secondary);">
                    Memuat...
                </li>

            </ul>
        </div>
    </div>

    @if ($needsCompanySetup)
        <div class="company-setup-overlay" id="company-setup-modal">
            <div class="company-setup-modal-content">
                <h2>Selamat Datang!</h2>
                <p>Silakan lengkapi info usaha Anda untuk melanjutkan.</p>

                <form action="{{ route('company.setup.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="nama_perusahaan">Nama Usaha</label>
                        <input type="text" id="nama_perusahaan" name="nama_perusahaan" maxlength ="32"required>
                    </div>

                    <div class="form-group">
                        <label for="logo_usaha">Logo Usaha (Opsional)</label>
                        <input type="file" id="logo_usaha" name="logo" accept="image/*">
                    </div>

                    <button type="submit" class="btn-submit-setup">Simpan dan Mulai</button>
                </form>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- 1. CONFIG & VARIABLES ---
            let lineChartInstance;
            let doughnutChartInstance;
            let dashboardCurrentFilter = 'bulan_ini';

            const chartColors = {
                pemasukan: '#49CABE',
                pemasukanHover: '#3bb5a9',
                pengeluaran: '#FFA142',
                pengeluaranHover: '#e58e33',
                doughnut: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6']
            };

            // --- 2. HELPER FUNCTIONS ---
            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            }

            function escapeHtml(text) {
                if (!text) return text;
                return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g,
                    "&quot;").replace(/'/g, "&#039;");
            }

            function updateTrendUI(containerId, badgeId, isPositive, isReverse) {
                const container = document.getElementById(containerId);
                const badge = document.getElementById(badgeId);
                if (!container || !badge) return;

                const arrow = container.querySelector('.trend-icon');
                let color = 'green';
                if (isReverse) {
                    color = isPositive ? 'red' : 'green';
                } else {
                    color = isPositive ? 'green' : 'red';
                }

                container.className = `card-trend card-trend-icon icon-${color}`;
                badge.className = `percentage-badge badge-${color}`;

                if (arrow) {
                    let iconPath = '{{ asset('icons/up.png') }}';
                    if (isReverse) {
                        iconPath = isPositive ? '{{ asset('icons/up_orange.png') }}' :
                            '{{ asset('icons/down_orange.png') }}';
                    } else {
                        iconPath = isPositive ? '{{ asset('icons/up.png') }}' : '{{ asset('icons/down.png') }}';
                    }
                    arrow.src = iconPath;
                }
            }

            function updateSummaryCard(id, value) {
                const el = document.getElementById(id);
                if (el) el.textContent = formatRupiah(value);
            }

            // --- 3. CHART RENDERING ---

            // [FIX: GRAFIK GRADASI HALUS]
            function renderLineChart(chartData) {
                const canvas = document.getElementById('lineChart');
                if (!canvas) return;
                const ctx = canvas.getContext('2d'); // Ambil context 2D untuk bikin gradasi

                if (lineChartInstance) lineChartInstance.destroy();

                const isBulananMode = (dashboardCurrentFilter === 'semua');
                let chartLabels, pemasukanData, pengeluaranData;

                if (isBulananMode) {
                    chartLabels = chartData.labels || [];
                    pemasukanData = chartData.datasets[0]?.data || [];
                    pengeluaranData = chartData.datasets[1]?.data || [];
                } else {
                    // Mode Harian (1-31)
                    chartLabels = Array.from({
                        length: 31
                    }, (_, i) => String(i + 1));
                    const mapDataToFullMonth = (apiData, apiLabels) => {
                        const fullData = new Array(31).fill(null);
                        if (apiData && apiLabels) {
                            apiData.forEach((value, index) => {
                                const label = apiLabels[index];
                                const dateMatch = label ? label.split(' ')[0] : null;
                                if (dateMatch) {
                                    const dateNum = parseInt(dateMatch) - 1;
                                    if (dateNum >= 0 && dateNum < 31) fullData[dateNum] = value;
                                }
                            });
                        }
                        return fullData;
                    };
                    pemasukanData = mapDataToFullMonth(chartData.datasets[0]?.data, chartData.labels);
                    pengeluaranData = mapDataToFullMonth(chartData.datasets[1]?.data, chartData.labels);
                }

                // Hitung Max Value
                const allData = [...pemasukanData, ...pengeluaranData].filter(v => v != null);
                let maxValue = Math.max(...allData, 0);
                if (maxValue === 0) maxValue = 100000;
                const yAxisMax = Math.ceil(maxValue * 1.1 / 100000) * 100000;

                // --- BUAT GRADASI WARNA (EFEK PREMIUM) ---
                // Gradasi Pemasukan (Hijau Teal)
                let gradientPemasukan = ctx.createLinearGradient(0, 0, 0, 400);
                gradientPemasukan.addColorStop(0, 'rgba(73, 202, 190, 0.6)'); // Atas (Pekat)
                gradientPemasukan.addColorStop(1, 'rgba(73, 202, 190, 0.0)'); // Bawah (Transparan)

                // Gradasi Pengeluaran (Oranye)
                let gradientPengeluaran = ctx.createLinearGradient(0, 0, 0, 400);
                gradientPengeluaran.addColorStop(0, 'rgba(255, 161, 66, 0.6)'); // Atas (Pekat)
                gradientPengeluaran.addColorStop(1, 'rgba(255, 161, 66, 0.0)'); // Bawah (Transparan)

                lineChartInstance = new Chart(ctx, {
                    type: 'line', // Kembali ke LINE untuk efek lengkung
                    data: {
                        labels: chartLabels,
                        datasets: [{
                                label: 'Pemasukan',
                                data: pemasukanData,
                                borderColor: '#49CABE', // Warna Garis
                                backgroundColor: gradientPemasukan, // Warna Isi (Gradasi)
                                borderWidth: 3, // Garis agak tebal
                                tension: 0.4, // KELENGKUNGAN (Smooth Curve)
                                fill: true, // Isi area bawah garis
                                pointRadius: 0, // Sembunyikan titik
                                pointHoverRadius: 6, // Munculkan titik saat hover
                                pointBackgroundColor: '#49CABE',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            },
                            {
                                label: 'Pengeluaran',
                                data: pengeluaranData,
                                borderColor: '#FFA142',
                                backgroundColor: gradientPengeluaran,
                                borderWidth: 3,
                                tension: 0.4, // KELENGKUNGAN
                                fill: true,
                                pointRadius: 0,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#FFA142',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: true,
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                titleColor: '#1e293b',
                                bodyColor: '#475569',
                                borderColor: '#e2e8f0',
                                borderWidth: 1,
                                padding: 10,
                                titleFont: {
                                    size: 13,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        let value = context.parsed.y;
                                        if (value === null || value === undefined) return context
                                            .dataset.label + ': -';
                                        return context.dataset.label + ': ' + formatRupiah(value);
                                    },
                                    title: function(context) {
                                        return isBulananMode ? context[0].label : 'Tanggal ' + context[
                                            0].label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: yAxisMax,
                                border: {
                                    display: false
                                },
                                ticks: {
                                    callback: function(value) {
                                        return (value / 1000).toFixed(0) + 'k';
                                    },
                                    font: {
                                        size: 11
                                    },
                                    color: '#94a3b8',
                                    padding: 10
                                },
                                grid: {
                                    color: '#f1f5f9',
                                    drawBorder: false,
                                    borderDash: [5, 5]
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    color: '#94a3b8',
                                    padding: 5,
                                    autoSkip: true,
                                    maxTicksLimit: 10
                                }
                            }
                        }
                    }
                });
            }

            function renderDoughnutChart(chartData) {
                const ctx = document.getElementById('doughnutChart');
                const msgEl = document.getElementById('doughnut-empty-msg');
                const legendList = document.getElementById('doughnut-legend-list');

                if (!ctx) return;
                if (doughnutChartInstance) doughnutChartInstance.destroy();

                const createLegend = (labels, colors) => {
                    if (!legendList) return;
                    legendList.innerHTML = '';
                    if (!labels || labels.length === 0) {
                        legendList.innerHTML = '<li style="color:#ccc; font-size:0.8rem;">Belum ada data</li>';
                        return;
                    }
                    labels.slice(0, 5).forEach((label, i) => {
                        const li = document.createElement('li');
                        li.innerHTML =
                            `<span class="legend-color" style="background-color: ${colors[i % colors.length]};"></span> ${escapeHtml(label)}`;
                        legendList.appendChild(li);
                    });
                };

                const hasData = chartData.data && chartData.data.some(val => val > 0);

                if (!hasData) {
                    if (msgEl) msgEl.style.display = 'block';
                    doughnutChartInstance = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: [],
                            datasets: [{
                                data: [1],
                                backgroundColor: ['#f3f4f6'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '75%',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    enabled: false
                                }
                            }
                        }
                    });
                    createLegend([], []);
                    return;
                }

                if (msgEl) msgEl.style.display = 'none';

                const indexed = chartData.labels.map((label, i) => ({
                    label,
                    data: chartData.data[i],
                    index: i
                }));
                const top5 = indexed.sort((a, b) => b.data - a.data).slice(0, 5);
                const topLabels = top5.map(item => item.label);
                const topData = top5.map(item => item.data);
                const topColors = top5.map(item => chartColors.doughnut[item.index % chartColors.doughnut.length]);

                doughnutChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: topLabels,
                        datasets: [{
                            data: topData,
                            backgroundColor: topColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 6,
                                callbacks: {
                                    label: function(context) {
                                        return formatRupiah(context.parsed);
                                    }
                                }
                            }
                        }
                    }
                });
                createLegend(topLabels, topColors);
            }

            // --- 4. DATA LOADING ---
            async function loadDashboardData(searchQuery = '') {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                const url = new URL('{{ url('/api/dashboard') }}');
                if (searchQuery) url.searchParams.append('search', searchQuery);

                const dSelect = document.getElementById('doughnut-type-filter');
                if (dSelect) url.searchParams.append('doughnut_mode', dSelect.value);

                const now = new Date();
                const fmt = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' +
                    String(d.getDate()).padStart(2, '0');

                if (dashboardCurrentFilter === 'bulan_ini') {
                    url.searchParams.append('start_date', fmt(new Date(now.getFullYear(), now.getMonth(), 1)));
                    url.searchParams.append('end_date', fmt(new Date(now.getFullYear(), now.getMonth() + 1,
                    0)));
                } else if (dashboardCurrentFilter === 'bulan_lalu') {
                    url.searchParams.append('start_date', fmt(new Date(now.getFullYear(), now.getMonth() - 1,
                        1)));
                    url.searchParams.append('end_date', fmt(new Date(now.getFullYear(), now.getMonth(), 0)));
                } else if (dashboardCurrentFilter.match(/^\d{4}-\d{2}$/)) {
                    const [y, m] = dashboardCurrentFilter.split('-');
                    url.searchParams.append('start_date', fmt(new Date(y, m - 1, 1)));
                    url.searchParams.append('end_date', fmt(new Date(y, m, 0)));
                }
                url.searchParams.append('_t', new Date().getTime());

                const saldoEl = document.getElementById('summary-saldo');
                if (saldoEl) saldoEl.textContent = '...';

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        }
                    });
                    if (response.status === 401) {
                        localStorage.removeItem('auth_token');
                        window.location.href = '/login';
                        return;
                    }
                    const data = await response.json();

                    if (data.summary) {
                        updateSummaryCard('summary-saldo', data.summary.saldo);
                        updateSummaryCard('summary-pemasukan', data.summary.pemasukan);
                        updateSummaryCard('summary-pengeluaran', data.summary.pengeluaran);
                        updateSummaryCard('summary-laba', data.summary.laba);

                        ['pemasukan', 'pengeluaran', 'laba'].forEach(type => {
                            const pct = data.summary[type + '_percent_change'] || 0;
                            const textEl = document.getElementById(`summary-${type}-pct-text`);
                            const iconEl = document.getElementById(`summary-${type}-pct-icon`);
                            const isPositive = pct >= 0;
                            if (textEl) textEl.textContent = (isPositive ? '+' : '') + pct.toFixed(2) +
                                '%';

                            let iconSrc;
                            if (type === 'pengeluaran') {
                                iconSrc = isPositive ? '{{ asset('icons/upp_orange.png') }}' :
                                    '{{ asset('icons/down_green.png') }}';
                                updateTrendUI(`summary-${type}-trend`, `summary-${type}-pct`, true,
                                    true);
                            } else {
                                iconSrc = isPositive ? '{{ asset('icons/upp_green.png') }}' :
                                    '{{ asset('icons/down_orange.png') }}';
                                updateTrendUI(`summary-${type}-trend`, `summary-${type}-pct`,
                                    isPositive, false);
                            }
                            if (iconEl) iconEl.src = iconSrc;
                        });
                    }

                    if (data.line_chart) renderLineChart(data.line_chart);
                    if (data.doughnut_chart) renderDoughnutChart(data.doughnut_chart);

                    const txList = document.getElementById('recent-transactions-list');
                    if (txList) {
                        txList.innerHTML = '';
                        if (!data.recent_transactions || data.recent_transactions.length === 0) {
                            txList.innerHTML =
                                '<li class="transaction-item" style="justify-content: center; color: #94a3b8; padding: 20px;">Belum ada transaksi.</li>';
                        } else {
                            data.recent_transactions.forEach(tx => {
                                const li = document.createElement('li');
                                li.className = 'transaction-item';
                                const cat = tx.category || {};
                                const isMasuk = cat.tipe === 'pemasukan';
                                const amountClass = isMasuk ? 'text-green' : 'text-red';
                                const iconBg = isMasuk ? 'bg-green-light' : 'bg-blue-light';
                                const iconClass = cat.ikon || 'fa-solid fa-question';
                                const iconHtml = iconClass.includes('.') ?
                                    `<img src="{{ asset('icons') }}/${iconClass}" alt="icon">` :
                                    `<i class="${iconClass}"></i>`;
                                const dateObj = new Date(tx.tanggal_transaksi);

                                li.innerHTML = `
                                <div class="icon-circle ${iconBg}">${iconHtml}</div>
                                <div class="transaction-details"><strong>${escapeHtml(cat.nama_kategori || 'Tanpa Kategori')}</strong></div>
                                <div class="transaction-datetime">
                                    <span>${dateObj.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</span>
                                    <span class="time">${dateObj.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}</span>
                                </div>
                                <div class="transaction-note">${escapeHtml(tx.catatan || '')}</div>
                                <div class="transaction-amount ${amountClass}">${isMasuk?'+':'-'}${formatRupiah(tx.jumlah)}</div>
                            `;
                                txList.appendChild(li);
                            });
                        }
                    }
                } catch (error) {
                    console.error('Dashboard Error:', error);
                }
            }

            // --- 5. EVENT BINDING ---
            const filterBtn = document.getElementById('dashboard-filter-btn');
            const filterMenu = document.getElementById('dashboard-filter-menu');
            const btnSpan = filterBtn ? filterBtn.querySelector('span') : null;

            if (filterBtn && filterMenu) {
                filterBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    filterMenu.style.display = filterMenu.style.display === 'flex' ? 'none' : 'flex';
                });
                document.addEventListener('click', (e) => {
                    if (!filterMenu.contains(e.target) && !filterBtn.contains(e.target)) filterMenu.style
                        .display = 'none';
                });
                filterMenu.querySelectorAll('.dropdown-item').forEach(item => {
                    item.addEventListener('click', () => {
                        filterMenu.querySelectorAll('.dropdown-item').forEach(el => el.classList
                            .remove('active'));
                        item.classList.add('active');
                        if (btnSpan) btnSpan.textContent = item.textContent;
                        dashboardCurrentFilter = item.dataset.value;
                        filterMenu.style.display = 'none';
                        loadDashboardData();
                    });
                });
            }

            const filterPicker = document.getElementById('dashboard-month-picker');
            if (filterPicker) {
                filterPicker.addEventListener('change', function() {
                    if (this.value) {
                        if (btnSpan) {
                            const date = new Date(this.value + '-01');
                            btnSpan.textContent = date.toLocaleDateString('id-ID', {
                                month: 'long',
                                year: 'numeric'
                            });
                        }
                        dashboardCurrentFilter = this.value;
                        if (filterMenu) filterMenu.style.display = 'none';
                        loadDashboardData();
                    }
                });
            }

            const doughnutSelectEl = document.getElementById('doughnut-type-filter');
            if (doughnutSelectEl) {
                doughnutSelectEl.addEventListener('change', () => loadDashboardData());
            }

            const setupCompanyForm = document.querySelector('form[action*="company-setup"]');
            if (setupCompanyForm) {
                setupCompanyForm.addEventListener('submit', function() {
                    const btn = this.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.textContent = 'Sedang Menyimpan...';
                        btn.disabled = true;
                        btn.style.opacity = '0.7';
                    }
                });
            }

            // --- 6. INIT ---
            loadDashboardData();

            // --- 7. ACTIVITY TRACKER ---
            setInterval(() => {
                const token = localStorage.getItem('auth_token');
                if (token) {
                    fetch('{{ url('/api/update-activity') }}', {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json'
                        }
                    }).catch(e => {});
                }
            }, 10 * 60 * 1000);
        });
    </script>
@endpush
