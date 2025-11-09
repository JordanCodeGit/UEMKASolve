@extends('layouts.app')

@section('title', 'Buku Kas')

@section('content')

<div class="bukukas-header">
    <div class="saldo-display-lg">
        <i class="fa-solid fa-wallet"></i>
        <h3 id="saldo-display">0</h3> 
    </div>
    <div class="header-actions">
        <div class="dropdown-with-icon">
            <i class="fa-solid fa-calendar-days"></i>
            <select class="dropdown-simple" id="date-filter-dropdown">
                <option value="bulan_ini">Bulan Ini</option>
                <option value="bulan_lalu">Bulan Lalu</option>
                <option value="semua">Semua</option>
            </select>
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
        <button class="btn-primary-green" id="add-transaction-btn">
            <i class="fa-solid fa-plus"></i> Tambah Transaksi
        </button>
    </div>
</div>

<div class="transaction-table-card">
    
    <div class="transaction-row header">
        <div class="cell-check"><input type="checkbox" id="check-all-transactions"></div>
        <div class="cell-kategori">Kategori</div>
        <div class="cell-tanggal">Tanggal & Waktu</div>
        <div class="cell-deskripsi">Deskripsi</div>
        <div class="cell-nominal">Nominal</div>
        <div class="cell-actions" style="text-align: right;">Actions</div> </div>
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
                
                <div id="transaksi-modal-message" style="color: red; margin-bottom: 15px; font-size: 14px;"></div>

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
                    <label for="modal-tx-tanggal">Tanggal</label>
                    <input type="date" id="modal-tx-tanggal" name="tanggal_transaksi" class="form-input-modal" required>
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
                
                <div id="kategori-modal-message" style="color: red; margin-bottom: 15px; font-size: 14px;"></div>

                <div class="modal-tabs">
                    <button type="button" class="modal-tab-item active" data-kat-tab-type="pengeluaran">Pengeluaran</button>
                    <button type="button" class="modal-tab-item" data-kat-tab-type="pemasukan">Pemasukan</button>
                    <input type="hidden" id="modal-kat-tipe" name="tipe" value="pengeluaran">
                </div>
                
                <div class="form-group-modal">
                    <label for="modal-kat-nama">Nama Kategori</label>
                    <input type="text" id="modal-kat-nama" name="nama_kategori" class="form-input-modal" placeholder="Masukkan nama kategori..." required>
                </div>
                
                <div class="form-group-modal">
                    <label for="modal-kat-ikon">Pilih Ikon</label>
                    <p style="font-size: 12px; color: var(--text-secondary);">(UI/UX Grid Ikon belum diimplementasikan. Masukkan nama kelas Font Awesome, cth: 'fa-solid fa-store')</p>
                    <input type="text" id="modal-kat-ikon" name="ikon" class="form-input-modal" placeholder="fa-solid fa-store">
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
    // --- Helper Functions (Fungsi Bantuan) ---
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        // Format tanggal sesuai desain (misal: 18 Oktober 2024)
        const fullDate = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        // Format waktu (misal: 14:30)
        const time = date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false });
        return `${fullDate}<small>${time}</small>`;
    }
    // ------------------------

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
        
        // Definisikan headers untuk POST/PUT/DELETE
        const API_HEADERS = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        };
        // Definisikan headers untuk GET (anti-cache)
        const API_HEADERS_GET = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token,
            'Cache-Control': 'no-cache' // Mencegah caching data lama
        };

        // --- 1. [READ] Fungsi Mengambil & Merender Transaksi ---
        async function fetchTransactions(url) {
            // Jika tidak ada URL (panggilan pertama), buat URL default
            if (!url) {
                const defaultUrl = new URL(API_TRANSACTIONS);
                // Cek apakah ada parameter 'search' di URL browser (dari dashboard)
                const urlParams = new URLSearchParams(window.location.search);
                const searchQuery = urlParams.get('search');
                
                if (searchQuery) {
                    defaultUrl.searchParams.append('search', searchQuery);
                    searchInput.value = searchQuery; // Isi search bar
                }
                url = defaultUrl.toString();
            }

            transactionListContainer.innerHTML = '<div class="transaction-row" style="justify-content: center; padding: 30px;">Memuat...</div>';
            try {
                const response = await fetch(url, { headers: API_HEADERS_GET }); 
                if (response.status === 401) return window.location.href = '{{ url("/login") }}';
                
                const data = await response.json();
                renderTransactionRows(data.data);
                renderPaginationLinks(data.links);
                fetchDashboardSaldo(); 
            } catch (error) {
                console.error('Error fetching transactions:', error);
                transactionListContainer.innerHTML = '<div class="transaction-row" style="color: red; justify-content: center; padding: 30px;">Gagal memuat data.</div>';
            }
        }

        // --- 2. [READ] Fungsi Merender Baris HTML ---
        function renderTransactionRows(transactions) {
            transactionListContainer.innerHTML = '';
            if (transactions.length === 0) {
                transactionListContainer.innerHTML = '<div class="transaction-row" style="justify-content: center; padding: 30px;">Belum ada transaksi ditemukan.</div>';
                return;
            }
            transactions.forEach(tx => {
                const row = document.createElement('div');
                row.className = 'transaction-row';
                const isPemasukan = tx.category.tipe === 'pemasukan';
                const amountClass = isPemasukan ? 'text-green' : 'text-red';
                const amountSign = isPemasukan ? '+' : '-';
                const iconClass = tx.category.ikon || 'fa-solid fa-question';
                const iconBg = isPemasukan ? 'bg-green-light' : 'bg-blue-light'; 

                row.innerHTML = `
                    <div class="cell-check"><input type="checkbox" class="check-item" data-id="${tx.id}"></div>
                    <div class="cell-kategori">
                        <span class="icon-wrapper ${iconBg}"><i class="${iconClass}"></i></span>
                        ${tx.category.nama_kategori}
                    </div>
                    <div class="cell-tanggal">${formatDate(tx.created_at)}</div>
                    <div class="cell-deskripsi">${tx.catatan || ''}</div>
                    <div class="cell-nominal ${amountClass}">${amountSign}${formatRupiah(tx.jumlah)}</div>
                `;
                transactionListContainer.appendChild(row);
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
                        // Pastikan parameter search tetap ada saat pindah halaman
                        const url = new URL(link.url);
                        url.searchParams.append('search', searchInput.value || '');
                        fetchTransactions(url.toString()); 
                    });
                    paginationLinksContainer.appendChild(pageButton);
                }
            });
        }

        // --- 4. [READ] Fungsi Ambil Saldo & Total ---
        async function fetchDashboardSaldo() {
            const saldoDisplay = document.getElementById('saldo-display');
            try {
                const response = await fetch(API_DASHBOARD, { headers: API_HEADERS_GET }); 
                const data = await response.json();
                saldoDisplay.textContent = formatRupiah(data.summary.saldo);
                // (Update footer totals di sini jika BE sudah support)
            } catch (error) {
                console.error('Error fetching saldo:', error);
                saldoDisplay.textContent = 'Error';
            }
        }
        
        // --- 5. [READ] Fungsi Mengisi Dropdown Kategori ---
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
                        
                        // Otomatis pilih kategori baru jika ada
                        if (selectId && cat.id === selectId) {
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
            document.getElementById('modal-tx-tanggal').valueAsDate = new Date(); 
            setActiveTab(txModalTabs, txTipeHidden, 'pengeluaran'); 
            populateCategoryDropdown('pengeluaran'); 
            txModalOverlay.style.display = 'flex';
        });

        // --- 7. [CREATE] Fungsi Submit Transaksi ---
        txForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            txMessage.textContent = 'Menyimpan...';

            const formData = new FormData(txForm);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(API_TRANSACTIONS, {
                    method: 'POST',
                    headers: API_HEADERS,
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (response.status === 201) { 
                    closeModal(txModalOverlay);
                    fetchTransactions(); // Muat ulang daftar
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
                    // Panggil populateCategoryDropdown dan kirim ID baru
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

        // --- 10. Helper Modal (Tutup & Ganti Tab) ---
        function closeModal(modal) {
            if (modal) modal.style.display = 'none';
        }
        function setActiveTab(tabs, hiddenInput, tipe) {
            hiddenInput.value = tipe;
            tabs.forEach(tab => {
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
                populateCategoryDropdown(tipe); // Ambil kategori baru
            });
        });

        katModalTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const tipe = tab.dataset.katTabType;
                setActiveTab(katModalTabs, katTipeHidden, tipe);
            });
        });
        
        let debounceTimerBukuKas; // Timer untuk debouncing
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimerBukuKas); // Hapus timer lama
            debounceTimerBukuKas = setTimeout(() => {
                handleFilterChange(); // Panggil fungsi filter utama
            }, 300); // Jeda 300ms
        });

        // --- Panggilan Awal ---
        // Modifikasi panggilan awal untuk menghormati search query dari URL
        const initialUrl = new URL(API_TRANSACTIONS);
        const urlParams = new URLSearchParams(window.location.search);
        const searchQuery = urlParams.get('search');
        
        if (searchQuery) {
            initialUrl.searchParams.append('search', searchQuery);
            searchInput.value = searchQuery; // Isi search bar
        }
        
        fetchTransactions(initialUrl.toString()); // Panggil saat halaman dimuat
    });
</script>
@endpush