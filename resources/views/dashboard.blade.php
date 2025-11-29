@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="summary-grid">

    <div class="summary-card summary-saldo">
        <div class="icon-box icon-white">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div class="card-content">
            <p>SALDO</p>
            <h3 id="summary-saldo">Memuat...</h3>
        </div>
    </div>

    <div class="summary-card">
        <div class="card-content">
            <div class="card-header">
                <div class="icon-box icon-blue">
                    <i class="fa-solid fa-file-arrow-down"></i>
                </div>
                <p>PEMASUKAN</p>
            </div>
            <h3 id="summary-pemasukan">Memuat...</h3>
            <span class="percentage-badge badge-green" id="summary-pemasukan-pct"></span>
        </div>
        <div class="card-trend-icon icon-green" id="summary-pemasukan-trend">
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
    </div>

    <div class="summary-card">
        <div class="card-content">
            <div class="card-header">
                <div class="icon-box icon-blue">
                    <i class="fa-solid fa-file-arrow-up"></i>
                </div>
                <p>PENGELUARAN</p>
            </div>
            <h3 id="summary-pengeluaran">Memuat...</h3>
            <span class="percentage-badge badge-red" id="summary-pengeluaran-pct"></span>
        </div>
        <div class="card-trend-icon icon-red" id="summary-pengeluaran-trend">
            <i class="fa-solid fa-arrow-trend-down"></i>
        </div>
    </div>
    
    <div class="summary-card">
        <div class="card-content">
            <div class="card-header">
                <div class="icon-box icon-blue">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <p>LABA</p>
            </div>
            <h3 id="summary-laba">Memuat...</h3>
            <span class="percentage-badge badge-green" id="summary-laba-pct"></span>
        </div>
        <div class="card-trend-icon icon-green" id="summary-laba-trend">
            <i class="fa-solid fa-arrow-trend-up"></i>
        </div>
    </div>
</div>

<div class="chart-grid">
    <div class="content-card">
        <div class="card-header">
            <div class="card-title-with-icon">
                <img src="{{ asset('icons/transaction_icon.png') }}" alt="Icon" class="custom-title-icon">
                <h3 class="card-title">Grafik Kas</h3>
            </div>
            <div class="grafik-kas-header">
                <div class="filter-container">
        
                    <div class="dropdown-with-icon" id="dashboard-filter-wrapper" style="position: relative; display: inline-block;">
                        
                        <div id="dashboard-filter-btn" class="dropdown-minimalis-grafik" style="min-width: 150px; justify-content: space-between; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-primary); display: flex; align-items: center; cursor: pointer;">
                            <i class="fa-solid fa-calendar" style="font-size: 14px; color: var(--text-secondary); margin-right: 6px;"></i>
                            <span style="font-size: 13px; font-weight: 500;">Bulan Ini</span>
                            <i class="fa-solid fa-chevron-down" style="font-size: 12px; color: var(--text-secondary); margin-left: auto;"></i>
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
        <div class="card-body">
            <div class="chart-container" id="lineChartContainer">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="content-card">
        <div class="card-header">
            <div class="card-title-with-icon">
                <img src="{{ asset('icons/transaction_icon.png') }}" alt="Icon" class="custom-title-icon">
                <h3 class="card-title">Persentase Kas</h3>
            </div>
            <select id="doughnut-type-filter" class="dropdown-minimalis" onchange="loadDashboardData()">
                <option value="pengeluaran">Pengeluaran</option>
                <option value="pemasukan">Pemasukan</option>
            </select>
        </div>
        <div class="card-body">
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

@if($needsCompanySetup)
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
    // --- 1. HELPER & CHART CONFIG ---
    let lineChartInstance;
    let doughnutChartInstance;
    
    const chartColors = {
        pemasukan: '#10b981', 
        pemasukanBg: 'rgba(16, 185, 129, 0.1)',
        pengeluaran: '#f43f5e',
        pengeluaranBg: 'rgba(244, 63, 94, 0.1)',
        doughnut: ['#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6']
    };

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(number);
    }

    function escapeHtml(text) {
        if (!text) return text;
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // [FIX ERROR 2] Helper Legend yang Aman
    function createDoughnutLegend(labels, colors) {
        const legendList = document.getElementById('doughnut-legend-list');
        if (!legendList) return;
        
        legendList.innerHTML = ''; 
        
        // Cek apakah labels ada dan berupa array
        if (!labels || !Array.isArray(labels) || labels.length === 0) {
            legendList.innerHTML = '<li style="color:#ccc; font-size:0.8rem;">Belum ada data</li>';
            return;
        }
        
        // Ambil hanya 5 item pertama (top 5)
        const topLabels = labels.slice(0, 5);
        
        topLabels.forEach((label, index) => {
            const color = colors[index % colors.length];
            const li = document.createElement('li');
            li.innerHTML = `<span class="legend-color" style="background-color: ${color};"></span> ${escapeHtml(label)}`;
            legendList.appendChild(li);
        });
    }

    // Global Filter Variable
    let dashboardCurrentFilter = 'bulan_ini';

    document.addEventListener('DOMContentLoaded', function() {
        const token = localStorage.getItem('auth_token');
        if (!token) window.location.href = '/login';

        const filterBtn = document.getElementById('dashboard-filter-btn');
        const filterMenu = document.getElementById('dashboard-filter-menu');
        const filterPicker = document.getElementById('dashboard-month-picker');
        const btnSpan = filterBtn.querySelector('span');

        const setupForm = document.querySelector('form[action*="company-setup"]');

        if (setupForm) {
            setupForm.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                
                // Ubah tombol jadi loading agar user tau proses sedang berjalan
                if (btn) {
                    btn.textContent = 'Sedang Menyimpan...';
                    btn.disabled = true; // Matikan tombol biar gak double submit
                    btn.style.opacity = '0.7';
                    btn.style.cursor = 'not-allowed';
                }
            });
        }

        // 1. Toggle Menu
        filterBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            filterMenu.style.display = filterMenu.style.display === 'flex' ? 'none' : 'flex';
        });

        // 2. Tutup Menu Klik Luar
        document.addEventListener('click', (e) => {
            if (!filterMenu.contains(e.target) && !filterBtn.contains(e.target)) {
                filterMenu.style.display = 'none';
            }
        });

        // 3. Klik Opsi (Bulan Ini/Lalu/Semua)
        filterMenu.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', () => {
                // Update UI
                filterMenu.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove('active'));
                item.classList.add('active');
                filterPicker.value = ''; 
                btnSpan.textContent = item.textContent;

                // Set Filter & Load
                dashboardCurrentFilter = item.dataset.value;
                filterMenu.style.display = 'none';
                loadDashboardData(); // Reload Data
            });
        });

        // 4. Custom Picker
        filterPicker.addEventListener('change', function() {
            const val = this.value;
            if(val) {
                filterMenu.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove('active'));
                const date = new Date(val + '-01');
                btnSpan.textContent = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                
                dashboardCurrentFilter = val; // "YYYY-MM"
                filterMenu.style.display = 'none';
                loadDashboardData();
            }
        });

        // --- FUNGSI LOAD DATA ---
        async function loadDashboardData(searchQuery = '') {
            const url = new URL('{{ url("/api/dashboard") }}');
            
            // 1. Parameter Search
            if (searchQuery) url.searchParams.append('search', searchQuery);

            // 2. Parameter Doughnut Mode
            const doughnutSelect = document.getElementById('doughnut-type-filter');
            if (doughnutSelect) {
                url.searchParams.append('doughnut_mode', doughnutSelect.value);
            }

            // 3. Parameter Tanggal (Menggunakan Global dashboardCurrentFilter)
            const now = new Date();
            const fmt = d => d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');

            if (dashboardCurrentFilter === 'bulan_ini') {
                const start = new Date(now.getFullYear(), now.getMonth(), 1);
                const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                url.searchParams.append('start_date', fmt(start));
                url.searchParams.append('end_date', fmt(end));
            
            } else if (dashboardCurrentFilter === 'bulan_lalu') {
                const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const end = new Date(now.getFullYear(), now.getMonth(), 0);
                url.searchParams.append('start_date', fmt(start));
                url.searchParams.append('end_date', fmt(end));
            
            } else if (dashboardCurrentFilter === 'semua') {
                // Jangan kirim tanggal, Controller akan otomatis pakai Mode Bulanan (All Time)
            
            } else if (dashboardCurrentFilter.match(/^\d{4}-\d{2}$/)) { 
                // Custom YYYY-MM
                const [y, m] = dashboardCurrentFilter.split('-');
                const start = new Date(y, m - 1, 1);
                const end = new Date(y, m, 0);
                url.searchParams.append('start_date', fmt(start));
                url.searchParams.append('end_date', fmt(end));
            }

            // 4. Anti-Cache (Penting agar browser tidak menyimpan hasil lama)
            url.searchParams.append('_t', new Date().getTime());

            // Loading State
            document.getElementById('summary-saldo').textContent = '...';

            try {
                const response = await fetch(url.toString(), {
                    headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                });
                
                if (response.status === 401) { localStorage.removeItem('auth_token'); window.location.href = '/login'; return; }
                const data = await response.json();

                // A. Update Summary
                if (data.summary) {
                    document.getElementById('summary-saldo').textContent = formatRupiah(data.summary.saldo);
                    document.getElementById('summary-pemasukan').textContent = formatRupiah(data.summary.pemasukan);
                    document.getElementById('summary-pengeluaran').textContent = formatRupiah(data.summary.pengeluaran);
                    document.getElementById('summary-laba').textContent = formatRupiah(data.summary.laba);
                    
                    // [FIX ERROR 1] Update UI Panah (Dengan Pengecekan Elemen)
                    updateTrendUI('summary-laba-trend', 'summary-laba-pct', data.summary.laba >= 0, false);
                    updateTrendUI('summary-pengeluaran-trend', 'summary-pengeluaran-pct', data.summary.pengeluaran > 0, true);
                }

                // B. Render Charts
                if (data.line_chart) renderLineChart(data.line_chart);
                
                if (data.doughnut_chart) {
                    renderDoughnutChart(data.doughnut_chart);
                    createDoughnutLegend(data.doughnut_chart.labels, chartColors.doughnut);
                }

                // C. List Transaksi
                if (data.recent_transactions) populateTransactions(data.recent_transactions);

            } catch (error) {
                console.error('Dashboard Error:', error);
            }
        }

        // Helper Update Panah (Safe Check)
        function updateTrendUI(containerId, badgeId, isPositive, isReverse) {
            const container = document.getElementById(containerId);
            const badge = document.getElementById(badgeId);
            
            if (!container || !badge) return; // Stop jika elemen HTML tidak ada

            const icon = container.querySelector('i');
            
            let color = 'green'; // Default untung
            if (isReverse) { // Untuk pengeluaran (Naik = Buruk/Merah)
                color = isPositive ? 'red' : 'green';
            } else { // Untuk Laba (Naik = Bagus/Hijau)
                color = isPositive ? 'green' : 'red';
            }

            // Reset Class
            container.className = `card-trend card-trend-icon icon-${color}`; // Pastikan class CSS ada
            badge.className = `percentage-badge badge-${color}`;
            
            if(icon) {
                // Arrow Up jika positif (terlepas baik/buruk), Down jika negatif
                // Tapi sederhananya kita pakai logika warna di atas saja
                icon.className = isPositive ? 'fa-solid fa-arrow-trend-up' : 'fa-solid fa-arrow-trend-down';
            }
        }

        // --- RENDER LINE CHART ---
        function renderLineChart(chartData) {
            const ctx = document.getElementById('lineChart');
            if(!ctx) return;
            if(lineChartInstance) lineChartInstance.destroy();

            // Deteksi mode: jika filter adalah "semua", gunakan mode bulanan dinamis
            const isBulananMode = (dashboardCurrentFilter === 'semua');
            
            let chartLabels, pemasukanData, pengeluaranData;

            if (isBulananMode) {
                // MODE BULANAN (Dinamis) - Gunakan label dari API langsung
                chartLabels = chartData.labels || [];
                pemasukanData = chartData.datasets[0]?.data || [];
                pengeluaranData = chartData.datasets[1]?.data || [];
            } else {
                // MODE HARIAN (Statis 1-31) - Map ke full month
                chartLabels = Array.from({length: 31}, (_, i) => String(i + 1));

                const mapDataToFullMonth = (apiData, apiLabels) => {
                    const fullData = new Array(31).fill(null);
                    
                    if (apiData && apiLabels) {
                        apiData.forEach((value, index) => {
                            const label = apiLabels[index];
                            // Extract tanggal dari label (format: "29 Nov" -> "29")
                            const dateMatch = label ? label.split(' ')[0] : null;
                            if (dateMatch) {
                                const dateNum = parseInt(dateMatch) - 1; // 0-indexed
                                if (dateNum >= 0 && dateNum < 31) {
                                    fullData[dateNum] = value;
                                }
                            }
                        });
                    }
                    return fullData;
                };

                pemasukanData = mapDataToFullMonth(chartData.datasets[0]?.data, chartData.labels);
                pengeluaranData = mapDataToFullMonth(chartData.datasets[1]?.data, chartData.labels);
            }

            // Hitung max value untuk dynamic Y-axis
            const allData = [...pemasukanData, ...pengeluaranData].filter(v => v != null);
            const maxValue = Math.max(...allData, 0);
            const yAxisMax = Math.ceil(maxValue * 1.1 / 100000) * 100000; // Round up to nearest 100k

            lineChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { 
                            label: 'Pemasukan', 
                            data: pemasukanData, 
                            borderColor: '#49CABE',
                            backgroundColor: 'rgba(73, 202, 190, 0.1)',
                            fill: true, 
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointBackgroundColor: '#49CABE',
                            pointBorderColor: '#49CABE',
                            pointBorderWidth: 0,
                            pointHoverRadius: 0
                        },
                        { 
                            label: 'Pengeluaran', 
                            data: pengeluaranData, 
                            borderColor: '#FFA142',
                            backgroundColor: 'rgba(255, 161, 66, 0.1)',
                            fill: true, 
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 0,
                            pointBackgroundColor: '#FFA142',
                            pointBorderColor: '#FFA142',
                            pointBorderWidth: 0,
                            pointHoverRadius: 0
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
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 12, weight: 600 },
                            bodyFont: { size: 12 },
                            titleMarginBottom: 8,
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    let value = context.parsed.y;
                                    if (value === null || value === undefined) {
                                        return context.dataset.label + ': -';
                                    }
                                    // Format dengan separator ribuan dan 2 desimal
                                    const formatted = new Intl.NumberFormat('id-ID', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    }).format(value);
                                    return context.dataset.label + ': Rp ' + formatted;
                                },
                                title: function(context) {
                                    if (isBulananMode) {
                                        return context[0].label;
                                    } else {
                                        return 'Tanggal ' + context[0].label;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: yAxisMax,
                            ticks: {
                                callback: function(value) {
                                    return (value / 1000).toFixed(0) + 'k';
                                },
                                font: { size: 12 },
                                color: 'var(--text-secondary)',
                                padding: 8
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 12 },
                                color: 'var(--text-secondary)',
                                padding: 8
                            }
                        }
                    }
                }
            });
        }

        const doughnutSelect = document.getElementById('doughnut-type-filter');
        if (doughnutSelect) {
            doughnutSelect.addEventListener('change', function() {
                console.log("Dropdown berubah, memuat ulang...");
                loadDashboardData();
            });
        }

        // --- RENDER DOUGHNUT CHART ---
        function renderDoughnutChart(chartData) {
            const ctx = document.getElementById('doughnutChart');
            const msgEl = document.getElementById('doughnut-empty-msg');
            if(!ctx) return;

            if(doughnutChartInstance) doughnutChartInstance.destroy();

            // Cek Data Kosong
            const hasData = chartData.data && chartData.data.some(val => val > 0);

            if (!hasData) {
                if(msgEl) msgEl.style.display = 'block';
                // Render Empty State
                doughnutChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: { labels: [], datasets: [{ data: [1], backgroundColor: ['#f3f4f6'], borderWidth: 0 }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
                });
                return;
            }

            if(msgEl) msgEl.style.display = 'none';

            // Filter Top 5 - membuat array dengan index untuk sorting
            const indexed = chartData.labels.map((label, i) => ({
                label,
                data: chartData.data[i],
                index: i
            }));
            
            // Sort by data descending dan ambil top 5
            const top5 = indexed.sort((a, b) => b.data - a.data).slice(0, 5);
            
            // Buat data array baru untuk top 5
            const topLabels = top5.map(item => item.label);
            const topData = top5.map(item => item.data);
            const topColors = top5.map(item => chartColors.doughnut[item.index % chartColors.doughnut.length]);

            doughnutChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: topLabels,
                    datasets: [{ data: topData, backgroundColor: topColors, borderWidth: 0 }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    cutout: '75%', 
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 12, weight: 600 },
                            bodyFont: { size: 12 },
                            cornerRadius: 6,
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const formatted = new Intl.NumberFormat('id-ID', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    }).format(value);
                                    return 'Rp ' + formatted;
                                },
                                title: function(context) {
                                    return context[0].label;
                                }
                            }
                        }
                    } 
                }
            });
        }

        // --- HELPER UPDATE UI ---
        function updateSummaryCard(id, value) {
            const el = document.getElementById(id);
            if(el) el.textContent = formatRupiah(value);
        }

        function updateTrendUI(containerId, badgeId, isPositive, isReverse) {
            const container = document.getElementById(containerId);
            const badge = document.getElementById(badgeId);
            if (!container || !badge) return;

            const icon = container.querySelector('i');
            let color = isReverse ? (isPositive ? 'red' : 'green') : (isPositive ? 'green' : 'red');

            container.className = `card-trend-right card-trend-icon icon-${color}`; // Sesuaikan dengan class CSS baru Anda
            if(icon) icon.className = isPositive ? 'fa-solid fa-arrow-trend-up' : 'fa-solid fa-arrow-trend-down';
        }

        function createDoughnutLegend(labels, colors) {
            const list = document.getElementById('doughnut-legend-list');
            if(!list) return;
            list.innerHTML = '';
            if(!labels || labels.length === 0) return;

            labels.forEach((label, i) => {
                const li = document.createElement('li');
                li.innerHTML = `<span style="background:${colors[i % colors.length]}; width:10px; height:10px; display:inline-block; border-radius:50%; margin-right:5px;"></span> ${escapeHtml(label)}`;
                list.appendChild(li);
            });
        }

        // --- POPULATE TRANSACTIONS ---
        function populateTransactions(transactions) {
        const list = document.getElementById('recent-transactions-list');
        if (!list) return;
        list.innerHTML = ''; // Bersihkan loading

        if (transactions.length === 0) {
            list.innerHTML = '<li class="transaction-item" style="justify-content: center; color: #94a3b8; padding: 20px;">Belum ada transaksi.</li>';
            return;
        }

        transactions.forEach(tx => {
            const li = document.createElement('li');
            li.className = 'transaction-item';
            
            // Data Safe Check
            const category = tx.category || {};
            const isPemasukan = category.tipe === 'pemasukan';
            
            // Styling
            const amountClass = isPemasukan ? 'text-green' : 'text-red';
            const amountSign = isPemasukan ? '+' : '-';
            const iconBg = isPemasukan ? 'bg-green-light' : 'bg-blue-light'; 
            
            // Icon Logic
            const iconClass = category.ikon || 'fa-solid fa-question';
            let iconHtml = iconClass.includes('.') 
                ? `<img src="{{ asset('icons') }}/${iconClass}" alt="icon">`
                : `<i class="${iconClass}"></i>`;

            // Format Tanggal & Jam (Pisah baris)
            const dateObj = new Date(tx.tanggal_transaksi);
            const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            const timeStr = dateObj.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            // Anti XSS
            const safeKategori = escapeHtml(category.nama_kategori || 'Tanpa Kategori');
            const safeCatatan = escapeHtml(tx.catatan || '');

            // Render HTML Struktur Baru (Flexbox Row)
            li.innerHTML = `
                <div class="icon-circle ${iconBg}">
                    ${iconHtml}
                </div>
                
                <div class="transaction-details">
                    <strong>${safeKategori}</strong>
                </div>

                <div class="transaction-datetime">
                    <span>${dateStr}</span>
                    <span class="time">${timeStr}</span>
                </div>

                <div class="transaction-note">
                    ${safeCatatan}
                </div>
                
                <div class="transaction-amount ${amountClass}">
                    ${amountSign}${formatRupiah(tx.jumlah)}
                </div>
            `;
            list.appendChild(li);
        });
    }

        // Event Listener Dropdown (Jika ada)
        const dateDropdown = document.getElementById('dashboard-date-filter');
        if (dateDropdown) {
            dateDropdown.addEventListener('change', () => loadDashboardData());
        }

        // Init
        loadDashboardData();
    });
</script>
@endpush