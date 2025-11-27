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
            <h3 class="card-title">Grafik Kas</h3>
            <div class="filter-container">
    
                <div class="dropdown-with-icon" id="dashboard-filter-wrapper" style="position: relative; display: inline-block;">
                    
                    <div id="dashboard-filter-btn" class="dropdown-btn-custom" style="min-width: 130px; justify-content: space-between; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; display: flex; align-items: center; cursor: pointer;">
                        <span>Bulan Ini</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 12px; color: #64748b;"></i>
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
        </div>
        <div class="card-body">
            <div class="chart-legend">
                <span><i class="fa-solid fa-circle text-blue"></i> Pemasukan</span>
                <span><i class="fa-solid fa-circle text-orange"></i> Pengeluaran</span>
            </div>
            <div class="chart-container" id="lineChartContainer">
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Persentase Kas</h3>
            <select id="doughnut-type-filter" class="dropdown-simple" onchange="loadDashboardData()">
                <option value="pengeluaran">Pengeluaran</option>
                <option value="pemasukan">Pemasukan</option>
            </select>
        </div>
        <div class="card-body">
            <div class="chart-container" id="doughnutChartContainer">
                <canvas id="doughnutChart"></canvas>
            </div>
            
            <ul class="doughnut-legend" id="doughnut-legend-list">
                </ul>
        </div>
    </div>
</div>

<div class="card card-transaksi-terakhir">
    
    <div class="card-header">
        <h3>
            <i class="fa-solid fa-table-cells-large"></i> 
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
        
        labels.forEach((label, index) => {
            const color = colors[index % colors.length];
            const li = document.createElement('li');
            li.innerHTML = `<span class="legend-dot" style="background-color: ${color}; display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:5px;"></span> ${escapeHtml(label)}`;
            legendList.appendChild(li);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const token = localStorage.getItem('auth_token');
        if (!token) window.location.href = '/login';

        const filterBtn = document.getElementById('dashboard-filter-btn');
        const filterMenu = document.getElementById('dashboard-filter-menu');
        const filterPicker = document.getElementById('dashboard-month-picker');
        const btnSpan = filterBtn.querySelector('span');
        
        let currentFilter = 'bulan_ini'; // Default

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
                currentFilter = item.dataset.value;
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
                
                currentFilter = val; // "YYYY-MM"
                filterMenu.style.display = 'none';
                loadDashboardData();
            }
        });

        // --- FUNGSI LOAD DATA ---
        async function loadDashboardData(searchQuery = '') {
            const url = new URL('{{ url("/api/dashboard") }}');
            
            // 1. Parameter Search
            if (searchQuery) url.searchParams.append('search', searchQuery);

            // 2. Parameter Tanggal (Dinamis dari Dropdown)
            // Jika Anda punya dropdown di dashboard dengan ID 'dashboard-date-filter'
            const dateFilterEl = document.getElementById('dashboard-date-filter'); 
            const filterVal = dateFilterEl ? dateFilterEl.value : 'bulan_ini'; // Default
            const doughnutFilterEl = document.getElementById('doughnut-type-filter');
            const doughnutMode = doughnutFilterEl ? doughnutFilterEl.value : 'pengeluaran';
            
            url.searchParams.append('doughnut_mode', doughnutMode);

            const now = new Date();
            const fmt = d => d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            
            const doughnutSelect = document.getElementById('doughnut-type-filter');
            if (doughnutSelect) {
                // Kirim nilai dropdown ke API (pemasukan/pengeluaran)
                url.searchParams.append('doughnut_mode', doughnutSelect.value);
            }

            if (currentFilter === 'bulan_ini') {
                const start = new Date(now.getFullYear(), now.getMonth(), 1);
                const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                url.searchParams.append('start_date', fmt(start));
                url.searchParams.append('end_date', fmt(end));
            
            } else if (currentFilter === 'bulan_lalu') {
                const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const end = new Date(now.getFullYear(), now.getMonth(), 0);
                url.searchParams.append('start_date', fmt(start));
                url.searchParams.append('end_date', fmt(end));
            
            } else if (currentFilter === 'semua') {
                // Jangan kirim tanggal, Controller akan otomatis pakai Mode Bulanan (All Time)
            
            } else if (currentFilter.match(/^\d{4}-\d{2}$/)) { 
                // Custom YYYY-MM
                const [y, m] = currentFilter.split('-');
                const start = new Date(y, m - 1, 1);
                const end = new Date(y, m, 0);
                url.searchParams.append('start_date', fmt(start));
                url.searchParams.append('end_date', fmt(end));
            }

            
            if (doughnutSelect) {
                console.log("Mengambil mode donat:", doughnutSelect.value); // Debugging
                url.searchParams.append('doughnut_mode', doughnutSelect.value);
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

            lineChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        { label: 'Pemasukan', data: chartData.datasets[0].data, borderColor: chartColors.pemasukan, backgroundColor: chartColors.pemasukanBg, fill: true, tension: 0.4 },
                        { label: 'Pengeluaran', data: chartData.datasets[1].data, borderColor: chartColors.pengeluaran, backgroundColor: chartColors.pengeluaranBg, fill: true, tension: 0.4 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
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

            doughnutChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{ data: chartData.data, backgroundColor: chartColors.doughnut, borderWidth: 0 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { display: false } } }
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
            list.innerHTML = '';

            if (transactions.length === 0) {
                list.innerHTML = '<li class="transaction-item" style="justify-content: center; color: #999;">Belum ada transaksi.</li>';
                return;
            }

            transactions.forEach(tx => {
                const li = document.createElement('li');
                li.className = 'transaction-item';
                
                const isMasuk = tx.category.tipe === 'pemasukan';
                const amountClass = isMasuk ? 'text-green' : 'text-red';
                const sign = isMasuk ? '+' : '-';
                const iconBg = isMasuk ? 'bg-green-light' : 'bg-blue-light';
                
                const iconClass = tx.category.ikon || 'fa-solid fa-question';
                let iconHtml = iconClass.includes('.') 
                    ? `<img src="{{ asset('icons') }}/${iconClass}" style="width:20px;">`
                    : `<i class="${iconClass}"></i>`;

                const date = new Date(tx.tanggal_transaksi);
                const dateStr = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });

                li.innerHTML = `
                    <div class="icon-circle ${iconBg}">${iconHtml}</div>
                    <div class="transaction-details"><strong>${escapeHtml(tx.category.nama_kategori)}</strong></div>
                    <div class="transaction-datetime"><small>${dateStr}</small></div>
                    <div class="transaction-amount ${amountClass}">${sign}${formatRupiah(tx.jumlah)}</div>
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