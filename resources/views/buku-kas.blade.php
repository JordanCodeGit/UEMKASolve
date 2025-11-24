@extends('layouts.app')

@section('title', 'Buku Kas')

@section('content')

<div class="bukukas-header">
    <div class="saldo-display-lg">
        <i class="fa-solid fa-wallet"></i>
        <h3 id="saldo-display">0</h3> 
    </div>
    <div class="header-actions">
        <div class="dropdown-with-icon" id="month-filter-wrapper">
            <i class="fa-solid fa-calendar-days"></i>
            
            <div id="month-filter-btn" class="dropdown-btn-custom">
                <span>Bulan Ini</span>
                <i class="fa-solid fa-chevron-down" style="font-size: 12px; margin-left: 5px;"></i>
            </div>

            <div class="dropdown-menu-custom" id="month-filter-menu">
                <div class="dropdown-item active" data-value="bulan_ini">Bulan Ini</div>
                <div class="dropdown-item" data-value="bulan_lalu">Bulan Lalu</div>
                <div class="dropdown-item" data-value="semua">Semua</div>
                
                <div class="dropdown-divider"></div>
                
                <div class="dropdown-item-custom">
                    <label for="custom-month-picker">Pilih Bulan Lain:</label>
                    <input type="month" id="custom-month-picker" class="form-input-month">
                </div>
            </div>
        </div>
        <button class="btn btn-gradient" id="btn-cetak-laporan">
            <i class="fa-solid fa-print"></i> Cetak Buku Kas
        </button>
    </div>
</div>

<div class="bukukas-toolbar">
    <div class="toolbar-left">
        <div class="search-bar-lg">
            <i class="fa-solid fa-search"></i>
            <input type="text" placeholder="Cari transaksi..." id="search-input">
        </div>
        
        <button class="btn-filter" id="filter-button">
            <i class="fa-solid fa-filter"></i> Filter
        </button>
    </div>
    
    <div class="toolbar-actions">
        <button class="btn-danger" id="bulk-delete-btn" style="display: none;">
            <i class="fa-solid fa-trash-can"></i> Hapus (<span id="selected-count">0</span>)
        </button>

        <button class="btn-primary-green" id="add-transaction-btn">
            <i class="fa-solid fa-plus"></i> Tambah Transaksi
        </button>
    </div>
</div>

<div class="transaction-table-card">
    
    <div class="transaction-row header">
        <div class="cell-check"><input type="checkbox" id="check-all-transactions"></div>
        <div class="cell-kategori">Kategori</div>
        <div class="cell-tanggal">
            Tanggal & Waktu
        </div>
        <div class="cell-deskripsi">Deskripsi</div>
        <div class="cell-nominal">Nominal</div>
    </div>
    
    <div id="transaction-list-container">
        <div class="transaction-row" style="justify-content: center; padding: 30px; color: var(--text-secondary);">
            Memuat data transaksi...
        </div>
    </div>
    
    <div class="pagination-container" id="pagination-links">
        </div>

    <div class="transaction-footer">
        <span>Total Pemasukan: <strong class="text-green" id="footer-total-pemasukan">Rp 0</strong></span>
        <span>Total Pengeluaran: <strong class="text-red" id="footer-total-pengeluaran">Rp 0</strong></span>
        <span>Laba: 
            <span class="laba-badge profit" id="footer-laba-badge">Rp 0</span>
        </span>
    </div>
</div>

<div class="modal-overlay" id="transaksi-modal-overlay" style="display: none;">
    <div class="modal-box">
        
        <div class="modal-header">
            <h2 id="transaksi-modal-title">Tambah Transaksi</h2> 
            <button class="modal-close-btn" data-close-modal="transaksi-modal-overlay">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <form id="transaksi-form">
            <div class="modal-body">
                
                <div id="transaksi-modal-message"></div>

                <div class="modal-tabs">
                    <button type="button" class="modal-tab-item active" data-tx-tab-type="pengeluaran">Pengeluaran</button>
                    <button type="button" class="modal-tab-item" data-tx-tab-type="pemasukan">Pemasukan</button>
                    <input type="hidden" id="modal-tx-tipe" name="tipe" value="pengeluaran">
                </div>
                
                <div class="form-group-modal">
                    <label for="modal-tx-jumlah">Jumlah</label>
                    <input type="number" id="modal-tx-jumlah" name="jumlah" class="form-input-modal" placeholder="Nominal transaksi" required>
                </div>

                <div class="form-group-modal">
                    <label for="modal-tx-kategori-select">Kategori</label>
                    <select id="modal-tx-kategori-select" name="category_id" class="form-input-modal" required>
                        <option value="">Memuat kategori...</option>
                    </select>
                    <a href="#" id="open-kategori-modal-link" style="font-size: 13px; color: #16a34a; margin-top: 8px; display: inline-block;">
                        <i class="fa-solid fa-plus"></i> Tambah Kategori
                    </a>
                </div>
                
                <div class="form-group-modal">
                    <label for="modal-tx-tanggal">Tanggal & Waktu</label>
                    <input type="datetime-local" id="modal-tx-tanggal" name="tanggal_transaksi" class="form-input-modal" required>
                </div>

                <div class="form-group-modal">
                    <label for="modal-tx-catatan">Catatan</label>
                    <textarea id="modal-tx-catatan" name="catatan" class="form-input-modal" placeholder="Pembayaran cash..."></textarea>
                </div>
                
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-modal" data-close-modal="transaksi-modal-overlay">Batal</button>
                <button type="submit" class="btn btn-primary-modal" id="transaksi-modal-submit-btn">Tambah Transaksi</button>
            </div>
        </form>
        
    </div>
</div>

<div class="modal-overlay" id="filter-modal-overlay" style="display: none;">
    <div class="modal-box" style="max-width: 400px;">
        
        <div class="modal-header">
            <h2>Filter Transaksi</h2>
            <button class="modal-close-btn" data-close-modal="filter-modal-overlay">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <form id="filter-form">
            <div class="modal-body">

                <div class="form-group-modal">
                    <label style="font-weight:600; margin-bottom:5px; display:block;">Jenis Transaksi</label>
                    <select id="filter-tipe" class="form-input-modal">
                        <option value="">Semua Jenis</option>
                        <option value="pemasukan">Pemasukan (+)</option>
                        <option value="pengeluaran">Pengeluaran (-)</option>
                    </select>
                </div>
                <div class="form-group-modal">
                    <label style="font-weight:600; margin-bottom:5px; display:block;">Rentang Tanggal</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="filter-start-date" class="form-input-modal">
                        <input type="date" id="filter-end-date" class="form-input-modal">
                    </div>
                </div>

                <div class="form-group-modal">
                    <label style="font-weight:600; margin-bottom:5px; display:block;">Rentang Nominal (Rp)</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="number" id="filter-min-nominal" class="form-input-modal" placeholder="Min">
                        <span>-</span>
                        <input type="number" id="filter-max-nominal" class="form-input-modal" placeholder="Max">
                    </div>
                </div>

            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-modal" id="btn-reset-filter">Reset</button>
                <button type="submit" class="btn btn-primary-modal">Terapkan Filter</button>
            </div>
        </form>
    </div>
</div>

<form id="transaksi-form">
    <input type="hidden" id="modal-tx-id" name="id"> 
    
    <div class="modal-body">...</div>
</form>

<div class="modal-overlay" id="kategori-modal-overlay" style="display: none;">
    <div class="modal-box">
        
        <div class="modal-header">
            <h2 id="kategori-modal-title">Tambah Kategori Baru</h2> 
            <button class="modal-close-btn" data-close-modal="kategori-modal-overlay">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <form id="kategori-form">
            <div class="modal-body">
                
                <div id="kategori-modal-message"></div>

                <div class="modal-tabs">
                    <button type="button" class="modal-tab-item active" data-kat-tab-type="pengeluaran">Pengeluaran</button>
                    <button type="button" class="modal-tab-item" data-kat-tab-type="pemasukan">Pemasukan</button>
                    <input type="hidden" id="modal-kat-tipe" name="tipe" value="pengeluaran">
                </div>
                
                <div class="form-group-modal">
                    <label for="modal-kat-nama">Nama Kategori</label>
                    <input type="text" id="modal-kat-nama" name="nama_kategori" class="form-input-modal" placeholder="Masukkan nama kategori..." required>
                </div>
                
                <div class="form-group icon-modal">
                    <label class="block text-gray-700 text-sm font-bold mb-3">Pilih Ikon</label>

                    <input type="hidden" name="ikon" id="modal-ikon" required>

                    <div class="icon-picker-container">
                        
                        <div class="icon-option" onclick="selectIcon(this, 'logo1.png')">
                            <img src="{{ asset('icons/logo1.png') }}" alt="logo1">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo2.png')">
                            <img src="{{ asset('icons/logo2.png') }}" alt="logo2">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo3.png')">
                            <img src="{{ asset('icons/logo3.png') }}" alt="logo3">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo4.png')">
                            <img src="{{ asset('icons/logo4.png') }}" alt="logo4">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo5.png')">
                            <img src="{{ asset('icons/logo5.png') }}" alt="logo5">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo6.png')">
                            <img src="{{ asset('icons/logo6.png') }}" alt="logo6">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo7.png')">
                            <img src="{{ asset('icons/logo7.png') }}" alt="logo7">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo8.png')">
                            <img src="{{ asset('icons/logo8.png') }}" alt="logo8">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo9.png')">
                            <img src="{{ asset('icons/logo9.png') }}" alt="logo9">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo10.png')">
                            <img src="{{ asset('icons/logo10.png') }}" alt="logo10">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo11.png')">
                            <img src="{{ asset('icons/logo11.png') }}" alt="logo11">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo12.png')">
                            <img src="{{ asset('icons/logo12.png') }}" alt="logo12">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo13.png')">
                            <img src="{{ asset('icons/logo13.png') }}" alt="logo13">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo14.png')">
                            <img src="{{ asset('icons/logo14.png') }}" alt="logo14">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo15.png')">
                            <img src="{{ asset('icons/logo15.png') }}" alt="logo15">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo16.png')">
                            <img src="{{ asset('icons/logo16.png') }}" alt="logo16">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo17.png')">
                            <img src="{{ asset('icons/logo17.png') }}" alt="logo17">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo18.png')">
                            <img src="{{ asset('icons/logo18.png') }}" alt="logo18">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo19.png')">
                            <img src="{{ asset('icons/logo19.png') }}" alt="logo19">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo20.png')">
                            <img src="{{ asset('icons/logo20.png') }}" alt="logo20">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo21.png')">
                            <img src="{{ asset('icons/logo21.png') }}" alt="logo21">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo22.png')">
                            <img src="{{ asset('icons/logo22.png') }}" alt="logo22">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo23.png')">
                            <img src="{{ asset('icons/logo23.png') }}" alt="logo23">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo24.png')">
                            <img src="{{ asset('icons/logo24.png') }}" alt="logo24">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo25.png')">
                            <img src="{{ asset('icons/logo25.png') }}" alt="logo25">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo26.png')">
                            <img src="{{ asset('icons/logo26.png') }}" alt="logo26">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo27.png')">
                            <img src="{{ asset('icons/logo27.png') }}" alt="logo27">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo28.png')">
                            <img src="{{ asset('icons/logo28.png') }}" alt="logo28">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo29.png')">
                            <img src="{{ asset('icons/logo29.png') }}" alt="logo29">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo30.png')">
                            <img src="{{ asset('icons/logo30.png') }}" alt="logo30">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo31.png')">
                            <img src="{{ asset('icons/logo31.png') }}" alt="logo31">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo32.png')">
                            <img src="{{ asset('icons/logo32.png') }}" alt="logo32">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo33.png')">
                            <img src="{{ asset('icons/logo33.png') }}" alt="logo33">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo34.png')">
                            <img src="{{ asset('icons/logo34.png') }}" alt="logo34">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo35.png')">
                            <img src="{{ asset('icons/logo35.png') }}" alt="logo35">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo36.png')">
                            <img src="{{ asset('icons/logo36.png') }}" alt="logo36">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo37.png')">
                            <img src="{{ asset('icons/logo37.png') }}" alt="logo37">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo38.png')">
                            <img src="{{ asset('icons/logo38.png') }}" alt="logo38">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo39.png')">
                            <img src="{{ asset('icons/logo39.png') }}" alt="logo39">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo40.png')">
                            <img src="{{ asset('icons/logo40.png') }}" alt="logo40">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo41.png')">
                            <img src="{{ asset('icons/logo41.png') }}" alt="logo41">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo42.png')">
                            <img src="{{ asset('icons/logo42.png') }}" alt="logo42">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo43.png')">
                            <img src="{{ asset('icons/logo43.png') }}" alt="logo43">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo44.png')">
                            <img src="{{ asset('icons/logo44.png') }}" alt="logo44">
                        </div>
                        <div class="icon-option" onclick="selectIcon(this, 'logo45.png')">
                            <img src="{{ asset('icons/logo45.png') }}" alt="logo45">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo46.png')">
                            <img src="{{ asset('icons/logo46.png') }}" alt="logo46">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo47.png')">
                            <img src="{{ asset('icons/logo47.png') }}" alt="logo47">
                        </div>

                        <div class="icon-option" onclick="selectIcon(this, 'logo48.png')">
                            <img src="{{ asset('icons/logo48.png') }}" alt="logo48">
                        </div>

                        </div>
                    
                    <small id="icon-error" class="text-red-500 text-xs hidden mt-1">Silakan pilih ikon terlebih dahulu.</small>
                </div>
                
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-modal" data-close-modal="kategori-modal-overlay">Batal</button>
                <button type="submit" class="btn btn-primary-modal" id="kategori-modal-submit-btn">Tambah Kategori</button>
            </div>
        </form>
        
    </div>
</div>

@endsection

@push('scripts')
<script>

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString.endsWith('Z') ? dateString : dateString + 'Z');
        
        const fullDate = date.toLocaleDateString('id-ID', { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
        });
        
        const time = date.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit', 
            hour12: false 
        });

        return `
            <div style="line-height: 1.2;">
                ${fullDate} <br>
                <small style="color: #64748b; font-size: 0.85em;">Pukul ${time}</small>
            </div>
        `;
    }

    function escapeHtml(text) {
        if (!text) return text;
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    // Fungsi Pilih Ikon untuk Modal Tambah Kategori
    function selectIcon(element, filename) {
        document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');
        document.getElementById('modal-kat-ikon').value = filename;
    }

    document.addEventListener('DOMContentLoaded', function() {
        
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '{{ url("/login") }}';
            return;
        }

        // --- Elemen Halaman Utama ---
        const transactionListContainer = document.getElementById('transaction-list-container');
        const paginationLinksContainer = document.getElementById('pagination-links');
        const searchInput = document.getElementById('search-input');
        const openAddTxBtn = document.getElementById('add-transaction-btn');
        const saldoDisplay = document.getElementById('saldo-display');

        // --- Elemen Modal 1 (Tambah Transaksi) ---
        const txModalOverlay = document.getElementById('transaksi-modal-overlay');
        const txForm = document.getElementById('transaksi-form');
        const txMessage = document.getElementById('transaksi-modal-message');
        const txTipeHidden = document.getElementById('modal-tx-tipe');
        const txKategoriSelect = document.getElementById('modal-tx-kategori-select');
        const openKategoriModalLink = document.getElementById('open-kategori-modal-link');
        const txModalTabs = document.querySelectorAll('#transaksi-modal-overlay .modal-tab-item');

        // --- Elemen Modal 2 (Tambah Kategori) ---
        const katModalOverlay = document.getElementById('kategori-modal-overlay');
        const katForm = document.getElementById('kategori-form');
        const katMessage = document.getElementById('kategori-modal-message');
        const katTipeHidden = document.getElementById('modal-kat-tipe');
        const katModalTabs = document.querySelectorAll('#kategori-modal-overlay .modal-tab-item');

        // --- Variabel API URL ---
        const API_TRANSACTIONS = '{{ url("/api/transactions") }}';
        const API_CATEGORIES = '{{ url("/api/categories") }}';
        const API_DASHBOARD = '{{ url("/api/dashboard") }}';
        
        const API_HEADERS = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        };
        const API_HEADERS_GET = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
            'Cache-Control': 'no-cache'
        };

        /*FUNGSI PILIH BULAN DARI DROPDOWN*/
        let currentMonthFilter = 'bulan_ini'; 

        // --- A. LOGIKA INTERAKSI DROPDOWN ---
        const monthBtn = document.getElementById('month-filter-btn');
        const monthMenu = document.getElementById('month-filter-menu');
        const monthPicker = document.getElementById('custom-month-picker');
        const btnSpan = monthBtn.querySelector('span');

        // 1. Toggle Menu (Buka/Tutup)
        monthBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            monthMenu.style.display = monthMenu.style.display === 'flex' ? 'none' : 'flex';
        });

        // 2. Tutup menu jika klik di luar
        document.addEventListener('click', (e) => {
            if (monthMenu && !monthMenu.contains(e.target) && !monthBtn.contains(e.target)) {
                monthMenu.style.display = 'none';
            }
        });

        // 3. Klik Opsi Standar (Bulan Ini, Lalu, Semua)
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', () => {
                // Update UI Active
                document.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove('active'));
                item.classList.add('active');
                monthPicker.value = ''; // Reset picker custom
                
                // Update Text Tombol
                btnSpan.textContent = item.textContent;
                
                // Set Filter & Fetch
                currentMonthFilter = item.dataset.value; // 'bulan_ini', 'bulan_lalu', 'semua'
                monthMenu.style.display = 'none';
                fetchTransactions(); 
            });
        });

        // 4. Pilih dari Month Picker (Custom)
        monthPicker.addEventListener('change', function() {
            const selectedValue = this.value; // Format: "2025-11"
            if (selectedValue) {
                document.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove('active'));
                
                // Format text tombol (misal: "November 2025")
                const dateObj = new Date(selectedValue + '-01');
                const monthName = dateObj.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                btnSpan.textContent = monthName;

                // Set Filter & Fetch
                currentMonthFilter = selectedValue; // Simpan "YYYY-MM"
                monthMenu.style.display = 'none';
                fetchTransactions();
            }
        });

        // --- 1. [READ] Fungsi Mengambil & Merender Transaksi ---
        // --- 1. FUNGSI FETCH DATA (PERBAIKAN) ---
        async function fetchTransactions(url = null) {
            let targetUrl;
            if (url) {
                targetUrl = new URL(url);
            } else {
                targetUrl = new URL(API_TRANSACTIONS);
            }

            // [PERBAIKAN UTAMA DI SINI]
            // Kita ambil elemen HTML-nya LANSUNG di dalam fungsi ini agar tidak "undefined"
            const searchInputEl = document.getElementById('search-input');
            const dateDropdownEl = document.getElementById('current-month-filter');

            // Ambil nilainya dengan pengecekan aman (Safe Check)
            const searchVal = searchInputEl ? searchInputEl.value : '';
            if (searchVal) targetUrl.searchParams.set('search', searchVal);
            const filterMode = (typeof currentMonthFilter !== 'undefined') ? currentMonthFilter : 'bulan_ini';

            const customStart = document.getElementById('filter-start-date').value;
            const customEnd = document.getElementById('filter-end-date').value;
            const minNominal = document.getElementById('filter-min-nominal').value;
            const maxNominal = document.getElementById('filter-max-nominal').value;
            const tipeFilterEl = document.getElementById('filter-tipe');
            const tipeVal = tipeFilterEl ? tipeFilterEl.value : '';


            if (minNominal) targetUrl.searchParams.set('min_nominal', minNominal);
            if (maxNominal) targetUrl.searchParams.set('max_nominal', maxNominal);

            if (tipeVal) {
                targetUrl.searchParams.set('tipe', tipeVal);
            }

            const fmt = d => d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            const now = new Date();

            if (customStart && customEnd) {
                // Prioritas 1: Filter Custom Range (Modal Filter)
                targetUrl.searchParams.set('start_date', customStart);
                targetUrl.searchParams.set('end_date', customEnd);
            } 
            else if (filterMode === 'bulan_ini') {
                const start = new Date(now.getFullYear(), now.getMonth(), 1);
                const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                targetUrl.searchParams.set('start_date', fmt(start));
                targetUrl.searchParams.set('end_date', fmt(end));
            
            } else if (filterMode === 'bulan_lalu') {
                const start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const end = new Date(now.getFullYear(), now.getMonth(), 0);
                targetUrl.searchParams.set('start_date', fmt(start));
                targetUrl.searchParams.set('end_date', fmt(end));
            
            } else if (filterMode.match(/^\d{4}-\d{2}$/)) { 
                // Prioritas 3: Custom Month Picker (YYYY-MM)
                const [y, m] = filterMode.split('-');
                const year = parseInt(y);
                const monthIndex = parseInt(m) - 1; 
                const start = new Date(year, monthIndex, 1);
                const end = new Date(year, monthIndex + 1, 0);
                targetUrl.searchParams.set('start_date', fmt(start));
                targetUrl.searchParams.set('end_date', fmt(end));
            } else if (filterMode === 'semua') {
                // Tidak kirim parameter tanggal, biar API ambil semua
            
            }  else {
                // Fallback ke Filter Range Manual (Start - End) dari Modal Filter Lanjutan
                const customStart = document.getElementById('filter-start-date').value;
                const customEnd = document.getElementById('filter-end-date').value;
                if (customStart && customEnd) {
                    targetUrl.searchParams.set('start_date', customStart);
                    targetUrl.searchParams.set('end_date', customEnd);
                }
            }

            // Tampilkan Loading
            transactionListContainer.innerHTML = '<div class="transaction-row" style="justify-content: center; padding: 30px; color: #64748b;">Sedang memuat data...</div>';

            try {
                const response = await fetch(targetUrl.toString(), { headers: API_HEADERS_GET });
                
                if (response.status === 401) {
                    window.location.href = '/login';
                    return;
                }

                const jsonData = await response.json(); 

                // Render Tabel
                if (jsonData.pagination && jsonData.pagination.data) {
                    renderTransactionRows(jsonData.pagination.data);
                    renderPaginationLinks(jsonData.pagination.links);
                } else {
                    renderTransactionRows([]);
                }

                // Render Saldo
                if (jsonData.summary) {
                    // Pastikan fungsi ini ada (lihat di bawah)
                    updateFooterSummary(jsonData.summary); 
                }

            } catch (error) {
                console.error('Error:', error);
                transactionListContainer.innerHTML = '<div class="transaction-row" style="color:red; justify-content:center; padding:30px;">Gagal memuat data.</div>';
            }
        }

        const filterBtn = document.getElementById('filter-button'); // Tombol di toolbar
        const filterOverlay = document.getElementById('filter-modal-overlay');
        const filterForm = document.getElementById('filter-form');
        const resetFilterBtn = document.getElementById('btn-reset-filter');

        // 1. Buka Modal
        if (filterBtn) {
            filterBtn.addEventListener('click', () => {
                filterOverlay.style.display = 'flex';
            });
        }

        // 2. Submit Filter (Terapkan)
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                closeModal(filterOverlay);
                fetchTransactions(); // Refresh data dengan filter baru
                
                // Opsional: Ubah warna tombol filter jika aktif
                filterBtn.style.color = '#2563eb'; 
                filterBtn.style.borderColor = '#2563eb'; 
            });
        }

        // 3. Reset Filter
        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', () => {
                filterForm.reset(); // Kosongkan input
                closeModal(filterOverlay);
                fetchTransactions(); // Refresh data (kembali ke default)
                
                // Reset warna tombol
                filterBtn.style.color = ''; 
                filterBtn.style.borderColor = '';
            });
        }

        // --- LOGIKA CHECKBOX & BULK DELETE ---
    
        // 1. Handle Check All (Header)
        const checkAllBtn = document.getElementById('check-all-transactions'); // Pastikan ID ini ada di header tabel
        checkAllBtn.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.check-item');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkDeleteButton();
        });

        // 2. Handle Check Item (Event Delegation di Container List)
        transactionListContainer.addEventListener('change', function(e) {
            if (e.target.classList.contains('check-item')) {
                updateBulkDeleteButton();
            }
        });

        // 3. Fungsi Update Tombol Hapus
        function updateBulkDeleteButton() {
            const selected = document.querySelectorAll('.check-item:checked');
            const btn = document.getElementById('bulk-delete-btn');
            const countSpan = document.getElementById('selected-count');
            
            if (selected.length > 0) {
                btn.style.display = 'inline-flex'; // Munculkan tombol
                countSpan.textContent = selected.length;
            } else {
                btn.style.display = 'none'; // Sembunyikan tombol
            }
        }

        // 4. Aksi Klik Tombol Hapus Massal
        document.getElementById('bulk-delete-btn').addEventListener('click', async function() {
            const selected = document.querySelectorAll('.check-item:checked');
            if (selected.length === 0) return;

            if (!confirm(`Yakin ingin menghapus ${selected.length} transaksi terpilih?`)) return;

            // Kumpulkan ID
            const ids = Array.from(selected).map(cb => cb.dataset.id);

            // Kirim Request Hapus (Looping fetch atau Batch API jika ada)
            // Cara sederhana: Loop fetch delete satu-satu
            let successCount = 0;
            for (const id of ids) {
                try {
                    await fetch(`${API_TRANSACTIONS}/${id}`, { 
                        method: 'DELETE', 
                        headers: API_HEADERS 
                    });
                    successCount++;
                } catch(e) { console.error(e); }
            }

            alert(`Berhasil menghapus ${successCount} transaksi.`);
            fetchTransactions(); // Refresh Tabel
            document.getElementById('bulk-delete-btn').style.display = 'none';
            checkAllBtn.checked = false;
        });

        function escapeHtml(text) {
        if (!text) return text;
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

        // --- FUNGSI BUKA MODAL EDIT (LOGIKA PENTING) ---
        window.openEditModal = function(tx) {
            // 1. Reset & Ubah Judul Modal
            txForm.reset();
            document.getElementById('transaksi-modal-title').textContent = 'Edit Transaksi';
            document.getElementById('transaksi-modal-submit-btn').textContent = 'Simpan Perubahan';
            
            // 2. Isi Input Hidden ID (Kunci agar sistem tahu ini EDIT, bukan BARU)
            document.getElementById('modal-tx-id').value = tx.id;
            
            // 3. Isi Input Biasa
            document.getElementById('modal-tx-jumlah').value = parseInt(tx.jumlah); // Hapus .00
            document.getElementById('modal-tx-catatan').value = tx.catatan || '';

            // 4. Isi Tanggal (Format Harus YYYY-MM-DDTHH:mm)
            if (tx.tanggal_transaksi) {
                // Ubah "2025-11-24 14:30:00" menjadi "2025-11-24T14:30"
                const dateVal = tx.tanggal_transaksi.replace(' ', 'T').substring(0, 16);
                document.getElementById('modal-tx-tanggal').value = dateVal;
            }

            // 5. Handle Kategori & Tipe
            const kategori = tx.category || {};
            const tipe = kategori.tipe || 'pengeluaran'; // Default

            // A. Set Tab Aktif (Pemasukan/Pengeluaran)
            setActiveTab(txModalTabs, txTipeHidden, tipe);

            // B. Isi Dropdown & Pilih Kategori
            // Kita panggil populateCategoryDropdown, lalu tunggu sebentar agar option ter-render
            // Baru kita set valuenya.
            populateCategoryDropdown(tipe, kategori.id);

            // 6. Tampilkan Modal
            txModalOverlay.style.display = 'flex';
        };

        // Reset Judul saat tombol Tambah (Hijau) diklik agar kembali bersih
        openAddTxBtn.addEventListener('click', function() {
            txForm.reset();
            document.getElementById('transaksi-modal-title').textContent = 'Tambah Transaksi';
            document.getElementById('transaksi-modal-submit-btn').textContent = 'Tambah Transaksi';
            document.getElementById('modal-tx-id').value = ''; // KOSONGKAN ID (Penting!)
            
            // Setup tanggal now
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('modal-tx-tanggal').value = now.toISOString().slice(0, 16);
            
            setActiveTab(txModalTabs, txTipeHidden, 'pengeluaran'); 
            populateCategoryDropdown('pengeluaran'); 
            txModalOverlay.style.display = 'flex';
        });

        // --- 2. [READ] Fungsi Merender Baris HTML ---
        function renderTransactionRows(transactions) {
            // Gunakan variabel global container agar konsisten
            const container = document.getElementById('transaction-list-container');
            container.innerHTML = '';
            
            if (transactions.length === 0) {
                container.innerHTML = '<div class="transaction-row" style="justify-content: center; padding: 30px; color: #94a3b8;">Belum ada transaksi.</div>';
                return;
            }

            transactions.forEach(tx => {
                const row = document.createElement('div');
                row.className = 'transaction-row';
                
                // Simpan data untuk edit
                row.dataset.json = JSON.stringify(tx);

                // --- Logika Data ---
                const category = tx.category || {}; 
                const isPemasukan = category.tipe === 'pemasukan';
                const amountClass = isPemasukan ? 'text-green' : 'text-red';
                const amountSign = isPemasukan ? '+' : '-';
                const iconBg = isPemasukan ? 'bg-green-light' : 'bg-blue-light'; 
                
                const iconClass = category.ikon || 'fa-solid fa-question';
                let iconHtml = iconClass.includes('.') 
                    ? `<img src="{{ asset('icons') }}/${iconClass}" alt="icon" style="width:24px; height:24px; object-fit:contain;">`
                    : `<i class="${iconClass}"></i>`;

                const safeCatatan = escapeHtml(tx.catatan || '-');
                const safeNamaKategori = category.nama_kategori 
                                         ? escapeHtml(category.nama_kategori) 
                                         : '<span style="color:red; font-style:italic;">(Kategori Terhapus)</span>';
                
                const displayDate = formatDate(tx.tanggal_transaksi || tx.created_at);

                // --- HTML ---
                row.innerHTML = `
                    <div class="cell-check" onclick="event.stopPropagation()">
                        <input type="checkbox" class="check-item" data-id="${tx.id}">
                    </div>
                    
                    <div class="cell-kategori">
                        <span class="icon-wrapper ${iconBg}">${iconHtml}</span>
                        ${safeNamaKategori}
                    </div>
                    
                    <div class="cell-tanggal">${displayDate}</div>
                    
                    <div class="cell-deskripsi" style="color: #334155;">${safeCatatan}</div>
                    
                    <div class="cell-nominal ${amountClass}">${amountSign}${formatRupiah(tx.jumlah)}</div>
                `;
                
                row.addEventListener('click', function() {
                    openEditModal(tx);
                });

                // Tambahkan style cursor pointer agar user tahu bisa diklik
                row.style.cursor = 'pointer';
                container.appendChild(row);
            });
        }
        
        // --- 3. [READ] Fungsi Merender Paginasi ---
        function renderPaginationLinks(links) {
            paginationLinksContainer.innerHTML = '';
            links.forEach(link => {
                if (link.url && !isNaN(link.label)) {
                    const pageButton = document.createElement('button');
                    pageButton.innerHTML = link.label;
                    pageButton.className = `pagination-link ${link.active ? 'active' : ''}`;
                    if(link.active) pageButton.disabled = true; 
                    pageButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        const url = new URL(link.url);
                        url.searchParams.append('search', searchInput.value || '');
                        fetchTransactions(url.toString()); 
                    });
                    paginationLinksContainer.appendChild(pageButton);
                }
            });
        }
        
        // --- 5. [READ] Fungsi Mengisi Dropdown Kategori (DIPERBAIKI) ---
        async function populateCategoryDropdown(selectedTipe = 'pengeluaran', selectId = null) {
            txKategoriSelect.innerHTML = '<option value="">Memuat kategori...</option>';
            try {
                const response = await fetch(`${API_CATEGORIES}?tipe=${selectedTipe}`, { headers: API_HEADERS_GET }); 
                const categories = await response.json();
                
                txKategoriSelect.innerHTML = '<option value="">-- Pilih kategori --</option>';
                if (categories.length > 0) {
                    categories.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.nama_kategori;
                        
                        // [LOGIKA PENTING] Pilih kategori yang baru dibuat (selectId)
                        // Pastikan tipe datanya sama (string vs number)
                        if (selectId && String(cat.id) === String(selectId)) {
                            option.selected = true;
                        }
                        
                        txKategoriSelect.appendChild(option);
                    });
                } else {
                    txKategoriSelect.innerHTML = '<option value="">-- Belum ada kategori --</option>';
                }
            } catch (error) {
                console.error('Error fetching categories:', error);
                txKategoriSelect.innerHTML = '<option value="">Gagal memuat kategori</option>';
            }
        }

        // --- 6. [CREATE] Fungsi Buka Modal Transaksi ---
        openAddTxBtn.addEventListener('click', function() {
            txForm.reset();
            txMessage.textContent = '';
            
            // [LOGIKA BARU: DETEKSI JAM LOKAL USER]
            const now = new Date();
            
            // Menggeser waktu UTC ke waktu Lokal User (WIB/WITA/WIT)
            // getTimezoneOffset() mengembalikan selisih menit (WIB = -420)
            // Kita kurangi negatif, jadinya ditambah.
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            
            // Format ke string ISO: "2025-11-24T14:05"
            // slice(0, 16) membuang detik dan zona waktu di belakang
            const currentLocalTime = now.toISOString().slice(0, 16);
            
            // Masukkan ke input
            document.getElementById('modal-tx-tanggal').value = currentLocalTime; 
            
            setActiveTab(txModalTabs, txTipeHidden, 'pengeluaran'); 
            populateCategoryDropdown('pengeluaran'); 
            txModalOverlay.style.display = 'flex';
        });

        // --- 7. [CREATE] Fungsi Submit Transaksi ---
        // --- Event Listener Form Transaksi (Tambah & Edit) ---
        txForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            txMessage.textContent = 'Menyimpan...';

            const formData = new FormData(txForm);
            const data = Object.fromEntries(formData.entries());

            // Cek apakah ada ID? Jika ada = Edit, Jika tidak = Baru
            const id = document.getElementById('modal-tx-id').value;
            
            let url = API_TRANSACTIONS;
            let method = 'POST';

            if (id) {
                url = `${API_TRANSACTIONS}/${id}`; // URL Update
                method = 'PUT'; // Method Update
            }   

            try {
                // [PERBAIKAN TYPO 'cconst']
                const response = await fetch(url, {
                    method: method,
                    headers: API_HEADERS,
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();

                // [PERBAIKAN LOGIKA STATUS]
                // response.ok mencakup status 200-299 (Sukses)
                if (response.ok) { 
                    closeModal(txModalOverlay);
                    fetchTransactions(); // Refresh tabel
                    txMessage.textContent = ''; // Bersihkan pesan
                } else if (response.status === 422) {
                    txMessage.textContent = 'Error: ' + Object.values(result.errors)[0][0];
                } else {
                    txMessage.textContent = 'Error: ' + (result.message || 'Gagal menyimpan.');
                }
            } catch (error) {
                console.error('Error submitting transaction:', error);
                txMessage.textContent = 'Gagal terhubung ke server.';
            }
        });

        // --- 8. [CREATE Kategori] Buka Modal Kategori ---
        openKategoriModalLink.addEventListener('click', function(e) {
            e.preventDefault();
            katForm.reset();
            katMessage.textContent = '';
            
            // [FIX] Ambil tipe yang sedang aktif di modal transaksi
            // Agar user tidak bingung (cth: lagi buat transaksi Pemasukan -> Tambah Kategori -> Otomatis tab Pemasukan)
            const currentTxTipe = txTipeHidden.value;
            
            setActiveTab(katModalTabs, katTipeHidden, currentTxTipe);
            katModalOverlay.style.display = 'flex';
        });

        // --- 9. [CREATE Kategori] Submit Form Kategori ---
        katForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            katMessage.textContent = 'Menyimpan...';

            const formData = new FormData(katForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(API_CATEGORIES, {
                    method: 'POST',
                    headers: API_HEADERS,
                    body: JSON.stringify(data)
                });
                const result = await response.json(); // result = kategori yg baru dibuat

                if (response.status === 201) {
                    closeModal(katModalOverlay);
                    
                    // [FIX] Refresh dropdown di modal transaksi & pilih kategori baru
                    // Kita harus pastikan tab di modal transaksi JUGA pindah ke tipe kategori baru
                    // Misal: User di tab Pengeluaran -> Buat kategori Pemasukan -> Modal Transaksi harus pindah ke tab Pemasukan
                    
                    if (txTipeHidden.value !== data.tipe) {
                        setActiveTab(txModalTabs, txTipeHidden, data.tipe);
                    }
                    
                    populateCategoryDropdown(data.tipe, result.id); 
                    
                } else if (response.status === 422) {
                    katMessage.textContent = 'Error: ' + Object.values(result.errors)[0][0];
                } else {
                    katMessage.textContent = 'Error: ' + (result.message || 'Gagal menyimpan.');
                }
            } catch (error) {
                console.error('Error submitting category:', error);
                katMessage.textContent = 'Gagal terhubung ke server.';
            }
        });

        // --- 10. Helper Modal ---
        function closeModal(modal) {
            if (modal) modal.style.display = 'none';
        }
        function setActiveTab(tabs, hiddenInput, tipe) {
            hiddenInput.value = tipe;
            tabs.forEach(tab => {
                // [FIX] Cek dataset yang benar (txTabType atau katTabType)
                const tabTipe = tab.dataset.txTabType || tab.dataset.katTabType;
                if (tabTipe === tipe) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
        }


        // --- Event Listeners Global ---
        document.querySelectorAll('.modal-close-btn, .btn-secondary-modal').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modalId = e.currentTarget.getAttribute('data-close-modal');
                closeModal(document.getElementById(modalId));
            });
        });
        
        txModalTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tipe = tab.dataset.txTabType;
                setActiveTab(txModalTabs, txTipeHidden, tipe);
                populateCategoryDropdown(tipe); 
            });
        });

        katModalTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tipe = tab.dataset.katTabType;
                setActiveTab(katModalTabs, katTipeHidden, tipe);
            });
        });
        
        let debounceTimerBukuKas; 
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimerBukuKas);
            debounceTimerBukuKas = setTimeout(() => {
                // [FIX] Panggil fetchTransactions dengan search query baru
                const url = new URL(API_TRANSACTIONS);
                url.searchParams.append('search', e.target.value);
                fetchTransactions(url.toString());
            }, 500); 
        });

        // --- Panggilan Awal ---
        const initialUrl = new URL(API_TRANSACTIONS);
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('search');
        
        if (searchQuery) {
            initialUrl.searchParams.append('search', searchQuery);
            searchInput.value = searchQuery; 
        }
        
        fetchTransactions(initialUrl.toString()); 
    });

    // --- FUNGSI UPDATE SALDO (SAFE MODE) ---
        // --- Fungsi Update Angka Footer (Versi Aman) ---
        function updateFooterSummary(summary) {
            const elMasuk = document.getElementById('footer-total-pemasukan');
            const elKeluar = document.getElementById('footer-total-pengeluaran');
            const elLaba = document.getElementById('footer-laba-badge');
            const saldoDisplay = document.getElementById('saldo-display');

            // Cek apakah elemen ada sebelum diisi (Mencegah Error Null)
            if (elMasuk) elMasuk.textContent = formatRupiah(summary.total_pemasukan);
            if (elKeluar) elKeluar.textContent = formatRupiah(summary.total_pengeluaran);
            if (saldoDisplay) saldoDisplay.textContent = formatRupiah(summary.laba);
            
            if (elLaba) {
                elLaba.textContent = formatRupiah(summary.laba);
                if (summary.laba >= 0) {
                    elLaba.className = 'laba-badge profit'; 
                    elLaba.style.backgroundColor = '#16A34A';
                    elLaba.style.color = '#ffffff';
                } else {
                    elLaba.className = 'laba-badge loss'; 
                    elLaba.style.backgroundColor = '#DC2626';
                    elLaba.style.color = '#ffffff';
                }
            }
        }

</script>
@endpush