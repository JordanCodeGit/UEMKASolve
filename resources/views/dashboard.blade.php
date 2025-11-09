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
            <div class="dropdown-with-icon">
                <i class="fa-solid fa-calendar-days"></i>
                <select class="dropdown-simple-dashboard" id="dashboard-date-filter">
                    <option value="bulan_ini">Bulan Ini</option>
                </select>
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
            <select class="dropdown-simple" id="doughnut-chart-filter">
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

<div class="content-card">
    <div class="card-header">
        <h3 class="card-title">Transaksi Terakhir</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <ul class="transaction-list" id="recent-transactions-list">
            <li class="transaction-item" style="justify-content: center; color: var(--text-secondary);">Memuat data...</li>
        </ul>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // --- 1. DEKLARASI HELPER FUNCTIONS ---
    
    // (Instance Chart harus dideklarasikan di luar agar bisa dihancurkan)
    let lineChartInstance;
    let doughnutChartInstance;

    /**
     * Memformat angka menjadi string Rupiah (misal: "Rp 1.500.000")
     */
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0 // Pastikan tidak ada desimal
        }).format(number);
    }

    /**
     * Membuat legenda kustom untuk Doughnut Chart
     */
    function createDoughnutLegend(labels, colors) {
        const legendList = document.getElementById('doughnut-legend-list');
        legendList.innerHTML = ''; // Kosongkan list
        
        labels.forEach((label, index) => {
            const color = colors[index % colors.length];
            const li = document.createElement('li');
            li.innerHTML = `<i class="fa-solid fa-circle" style="color: ${color};"></i> ${label}`;
            legendList.appendChild(li);
        });
    }

    /**
     * Mengisi daftar 5 Transaksi Terakhir
     */
    function populateTransactions(transactions) {
        const transactionList = document.getElementById('recent-transactions-list');
        transactionList.innerHTML = '';
        
        if (transactions.length === 0) {
            transactionList.innerHTML = '<li class="transaction-item" style="justify-content: center; color: var(--text-secondary);">Belum ada transaksi bulan ini.</li>';
            return;
        }

        transactions.forEach(tx => {
            const li = document.createElement('li');
            li.className = 'transaction-item';
            
            const isPemasukan = tx.category.tipe === 'pemasukan';
            const amountClass = isPemasukan ? 'text-green' : 'text-red';
            const amountSign = isPemasukan ? '+' : '-';
            const iconClass = tx.category.ikon || (isPemasukan ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up');
            const iconBg = isPemasukan ? 'bg-green-light' : 'bg-blue-light'; 

            const date = new Date(tx.tanggal_transaksi);
            const formattedDate = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

            li.innerHTML = `
                <div class="icon-circle ${iconBg}"><i class="${iconClass}"></i></div>
                <div class="transaction-details">
                    <strong>${tx.category.nama_kategori}</strong>
                    <small>${formattedDate}</small>
                </div>
                <div class="transaction-note">${tx.catatan || ''}</div>
                <div class="transaction-amount ${amountClass}">${amountSign}${formatRupiah(tx.jumlah)}</div>
            `;
            transactionList.appendChild(li);
        });
    }

    /**
     * Merender Line Chart (Grafik Kas)
     */
    function renderLineChart(data, colors) {
        if (lineChartInstance) {
            lineChartInstance.destroy(); 
        }
        const ctx = document.getElementById('lineChart');
        if (!ctx) return; // Hentikan jika canvas tidak ada
        
        lineChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: data.datasets[0].label,
                        data: data.datasets[0].data,
                        borderColor: colors.pemasukan,
                        backgroundColor: colors.pemasukanTransparent,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: data.datasets[1].label,
                        data: data.datasets[1].data,
                        borderColor: colors.pengeluaran,
                        backgroundColor: colors.pengeluaranTransparent,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    /**
     * Merender Doughnut Chart (Persentase Kas)
     */
    function renderDoughnutChart(data, colors) {
        if (doughnutChartInstance) {
            doughnutChartInstance.destroy();
        }
        const ctx = document.getElementById('doughnutChart');
        if (!ctx) return; // Hentikan jika canvas tidak ada
        
        doughnutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Pengeluaran',
                    data: data.data,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } }
        });
    }

    // --- 2. FUNGSI UTAMA (DOM CONTENT LOADED) ---
    document.addEventListener('DOMContentLoaded', function() {
        
        const token = localStorage.getItem('auth_token');
        if (!token) {
            console.error('Token tidak ditemukan. Mengarahkan ke login.');
            window.location.href = '{{ url("/login") }}';
            return;
        }

        const headerSearchInput = document.getElementById('header-search-input');
        
        
        // Definisikan warna-warna (PERBAIKAN POIN 2)
        const chartColors = {
            pemasukan: '#5EDB65', // Pemasukan (Hijau)
            pemasukanTransparent: 'rgba(94, 219, 101, 0.2)',
            pengeluaran: '#FFA142', // Pengeluaran (Oranye)
            pengeluaranTransparent: 'rgba(255, 161, 66, 0.2)',
            doughnutColors: ['#36D1DC', '#0072ff', '#888', '#f59e0b', '#ef4444', '#6b21a8']
        };

        // Fungsi untuk mengambil data
        async function loadDashboardData(searchQuery = '') {
            const url = new URL('{{ url("/api/dashboard") }}');
            if (searchQuery) {
                url.searchParams.append('search', searchQuery);
            }
            
            // Tampilkan loading
            document.getElementById('recent-transactions-list').innerHTML = '<li class="transaction-item" style="justify-content: center;">Memuat...</li>';
            document.getElementById('summary-saldo').textContent = 'Memuat...';
            document.getElementById('summary-pemasukan').textContent = 'Memuat...';
            document.getElementById('summary-pengeluaran').textContent = 'Memuat...';
            document.getElementById('summary-laba').textContent = 'Memuat...';

            try {
                const response = await fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    }
                });

                if (response.status === 401) { 
                    localStorage.removeItem('auth_token');
                    window.location.href = '{{ url("/login") }}'; 
                }
                if (!response.ok) { throw new Error('Network response was not ok'); }
                
                const data = await response.json();

                // 1. Isi Summary Cards
                document.getElementById('summary-saldo').textContent = formatRupiah(data.summary.saldo);
                document.getElementById('summary-pemasukan').textContent = formatRupiah(data.summary.pemasukan);
                document.getElementById('summary-pengeluaran').textContent = formatRupiah(data.summary.pengeluaran);
                document.getElementById('summary-laba').textContent = formatRupiah(data.summary.laba);
                
                // 2. [PERBAIKAN BUG PANAH]
                // Ambil elemen Ikon & Badge
                const labaTrendIcon = document.getElementById('summary-laba-trend');
                const labaTrendIconFa = labaTrendIcon.querySelector('i');
                const labaPctBadge = document.getElementById('summary-laba-pct');
                
                const pengeluaranTrendIcon = document.getElementById('summary-pengeluaran-trend');
                const pengeluaranTrendIconFa = pengeluaranTrendIcon.querySelector('i');
                const pengeluaranPctBadge = document.getElementById('summary-pengeluaran-pct');
                
                // Logika untuk LABA
                if (data.summary.laba >= 0) {
                    labaTrendIcon.className = 'card-trend-icon icon-green';
                    labaTrendIconFa.className = 'fa-solid fa-arrow-trend-up';
                    labaPctBadge.className = 'percentage-badge badge-green';
                } else {
                    labaTrendIcon.className = 'card-trend-icon icon-red';
                    labaTrendIconFa.className = 'fa-solid fa-arrow-trend-down';
                    labaPctBadge.className = 'percentage-badge badge-red';
                }
                
                // Logika untuk PENGELUARAN
                if (data.summary.pengeluaran > 0) {
                    pengeluaranTrendIcon.className = 'card-trend-icon icon-red';
                    pengeluaranTrendIconFa.className = 'fa-solid fa-arrow-trend-down';
                    pengeluaranPctBadge.className = 'percentage-badge badge-red';
                } else {
                     pengeluaranTrendIcon.className = 'card-trend-icon icon-green';
                     pengeluaranTrendIconFa.className = 'fa-solid fa-arrow-trend-up';
                     pengeluaranPctBadge.className = 'percentage-badge badge-green';
                }
                
                // Kosongkan badge persen (API kita belum mengirim data ini)
                labaPctBadge.textContent = ''; 
                pengeluaranPctBadge.textContent = '';
                document.getElementById('summary-pemasukan-pct').textContent = '';

                // 3. Isi Transaksi Terakhir
                populateTransactions(data.recent_transactions);
                
                // 4. Render Grafik
                renderLineChart(data.line_chart, chartColors);
                renderDoughnutChart(data.doughnut_chart, chartColors.doughnutColors);
                
                // 5. Buat Legenda Doughnut
                createDoughnutLegend(data.doughnut_chart.labels, chartColors.doughnutColors);
                
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
                document.getElementById('summary-saldo').textContent = 'Error';
                document.getElementById('summary-pemasukan').textContent = 'Error';
                document.getElementById('summary-pengeluaran').textContent = 'Error';
                document.getElementById('summary-laba').textContent = 'Error';
            }
        }

        let debounceTimerDashboard;
        if (headerSearchInput) {
            headerSearchInput.addEventListener('input', function(e) {
                // Hapus timer sebelumnya (batalkan request lama)
                clearTimeout(debounceTimerDashboard);
                
                const query = headerSearchInput.value;

                // Set timer baru. Request hanya akan dikirim setelah 300ms
                // user berhenti mengetik.
                debounceTimerDashboard = setTimeout(() => {
                    loadDashboardData(query); // Panggil fungsi fetch data
                }, 300); // Jeda 300 milidetik
            });
        }
        
        // Panggilan Awal
        loadDashboardData(); // Panggil saat halaman dimuat
    });
</script>
@endpush