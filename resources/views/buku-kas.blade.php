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
                        <button type="button" class="modal-tab-item active"
                            data-tx-tab-type="pengeluaran">Pengeluaran</button>
                        <button type="button" class="modal-tab-item" data-tx-tab-type="pemasukan">Pemasukan</button>
                        <input type="hidden" id="modal-tx-tipe" name="tipe" value="pengeluaran">
                    </div>

                    <div class="form-group-modal">
                        <label for="modal-tx-jumlah">Nominal</label>
                        <input type="text" id="modal-tx-jumlah" name="jumlah" class="form-input-modal"
                            placeholder="Nominal transaksi" inputmode="decimal" required>
                    </div>

                    <div class="form-group-modal">
                        <label class="label-modal">Kategori</label>

                        <input type="hidden" id="modal-tx-kategori-select" name="category_id">

                        <div class="custom-dropdown" id="category-dropdown">

                            <div class="dropdown-trigger" id="category-dropdown-btn">
                                <span id="selected-category-text">-- Pilih Kategori --</span>
                                <i class="fa-solid fa-chevron-down arrow-icon"></i>
                            </div>

                            <div class="dropdown-content">

                                <div class="dropdown-scroll-area" id="category-list-container">
                                    <div class="dropdown-item placeholder">Memuat...</div>
                                </div>

                                <div class="dropdown-add-btn" id="open-kategori-modal-link">
                                    <i class="fa-solid fa-plus-circle"></i> Tambah Kategori Baru
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-modal">
                        <label for="modal-tx-tanggal">Tanggal & Waktu</label>
                        <input type="datetime-local" id="modal-tx-tanggal" name="tanggal_transaksi"
                            class="form-input-modal" required>
                    </div>

                    <div class="form-group-modal">
                        <label for="modal-tx-catatan">Catatan</label>
                        <textarea id="modal-tx-catatan" name="catatan" class="form-input-modal" placeholder="Pembayaran cash..."></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-modal"
                        data-close-modal="transaksi-modal-overlay">Batal</button>
                    <button type="submit" class="btn btn-primary-modal" id="transaksi-modal-submit-btn">Tambah
                        Transaksi</button>
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

    <form id="transaksi-form-hidden">
        <input type="hidden" id="modal-tx-id" name="id">
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
                        <button type="button" class="modal-tab-item active"
                            data-kat-tab-type="pengeluaran">Pengeluaran</button>
                        <button type="button" class="modal-tab-item" data-kat-tab-type="pemasukan">Pemasukan</button>
                        <input type="hidden" id="modal-kat-tipe" name="tipe" value="pengeluaran">
                    </div>

                    <div class="form-group-modal">
                        <label for="modal-kat-nama">Nama Kategori</label>
                        <input type="text" id="modal-kat-nama" name="nama_kategori" class="form-input-modal"
                            placeholder="Masukkan nama kategori..." required>
                    </div>

                    <div class="form-group icon-modal">
                        <label class="block text-gray-700 text-sm font-bold mb-3">Pilih Ikon</label>

                        <input type="hidden" name="ikon" id="modal-ikon" required>

                        <div class="icon-picker-container" id="icon-grid-container-kat">
                            </div>

                        <small id="icon-error" class="text-red-500 text-xs hidden mt-1">Silakan pilih ikon terlebih
                            dahulu.</small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-modal"
                        data-close-modal="kategori-modal-overlay">Batal</button>
                    <button type="submit" class="btn btn-primary-modal" id="kategori-modal-submit-btn">Tambah
                        Kategori</button>
                </div>
            </form>

        </div>
    </div>

    <div class="dialog-overlay" id="dialog-overlay" style="display: none;">
        <div class="dialog-box">
            <div class="dialog-content">
                <div class="dialog-icon" id="dialog-icon"></div>
                <p id="dialog-message"></p>
            </div>
            <div class="dialog-actions">
                <button type="button" class="dialog-btn dialog-btn-cancel" id="dialog-btn-cancel"
                    style="display: none;">Batal</button>
                <button type="button" class="dialog-btn dialog-btn-confirm" id="dialog-btn-confirm">OK</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="print-laporan-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 550px; max-height: 90vh; overflow-y: auto;">

            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="{{ asset('icons/print_icon.png') }}" alt="Print" style="width: 24px; height: 24px;">
                    <h2>Preview Laporan Keuangan</h2>
                </div>
                <button class="modal-close-btn" data-close-modal="print-laporan-overlay">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <div class="modal-body">

                <div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="checkbox-ringkasan" name="ringkasan_keuangan" checked>
                            <span style="font-weight: 500; color: #333;">Sertakan ringkasan keuangan</span>
                        </label>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="checkbox-grafik" name="grafik_kas" checked>
                            <span style="font-weight: 500; color: #333;">Sertakan grafik kas</span>
                        </label>
                    </div>

                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="checkbox-rincian" name="rincian_transaksi" checked>
                            <span style="font-weight: 500; color: #333;">Sertakan rincian transaksi</span>
                        </label>
                    </div>
                </div>

                <div id="print-preview-container"
                    style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background-color: white; min-height: 300px;">
                    </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary-modal"
                    data-close-modal="print-laporan-overlay">Batal</button>
                <button type="button" class="btn btn-primary-modal" id="btn-download-pdf">Download sebagai PDF</button>
            </div>
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

            const date = new Date(dateString);

            // Validasi agar tidak error 'Invalid Date'
            if (isNaN(date.getTime())) return dateString;

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
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(
                /'/g, "&#039;");
        }

        // Fungsi Pilih Ikon untuk Modal Tambah Kategori
        function selectIconKat(element, filename) {
            document.querySelectorAll('#icon-grid-container-kat .icon-option').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('modal-ikon').value = filename;
        }

        document.addEventListener('DOMContentLoaded', function() {

            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '{{ url('/login') }}';
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
            const API_TRANSACTIONS = '{{ url('/api/transactions') }}';
            const API_CATEGORIES = '{{ url('/api/categories') }}';
            const API_DASHBOARD = '{{ url('/api/dashboard') }}';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            const API_HEADERS = {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            };
            const API_HEADERS_GET = {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token,
                'Cache-Control': 'no-cache'
            };

            // --- UTILITY FUNCTION: Format Nominal dengan Comma & Dot ---
            function formatNominal(value) {
                value = String(value);
                if (value.includes(',')) {
                    value = value.replace(/[^0-9,]/g, '');
                } else {
                    let lastDot = value.lastIndexOf('.');
                    if (lastDot !== -1 && value.length - lastDot - 1 === 2) {
                        value = value.substring(0, lastDot) + ',' + value.substring(lastDot + 1);
                    }
                    value = value.replace(/[^0-9,]/g, '');
                }
                let parts = value.split(',');
                if (parts[0].length > 15) {
                    parts[0] = parts[0].slice(0, 15);
                }
                if (parts.length > 2) {
                    parts = [parts[0], parts.slice(1).join('')];
                }
                if (parts[1]) {
                    parts[1] = parts[1].slice(0, 2);
                }
                let formatted = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return parts.length > 1 && parts[1] ? formatted + ',' + parts[1] : formatted;
            }

            // --- UTILITY FUNCTION: Dialog Minimalis untuk Konfirmasi & Notifikasi ---
            function showDialog(message, icon = 'info', isConfirm = false) {
                return new Promise((resolve) => {
                    const overlay = document.getElementById('dialog-overlay');
                    const messageEl = document.getElementById('dialog-message');
                    const iconEl = document.getElementById('dialog-icon');
                    const btnConfirm = document.getElementById('dialog-btn-confirm');
                    const btnCancel = document.getElementById('dialog-btn-cancel');

                    messageEl.textContent = message;

                    // Set icon
                    iconEl.className = `dialog-icon ${icon}`;
                    if (icon === 'success') iconEl.innerHTML = '<i class="fa-solid fa-check"></i>';
                    else if (icon === 'error') iconEl.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                    else if (icon === 'warning') iconEl.innerHTML =
                        '<i class="fa-solid fa-exclamation"></i>';
                    else iconEl.innerHTML = '<i class="fa-solid fa-info"></i>';

                    // Set tombol
                    if (isConfirm) {
                        btnCancel.style.display = 'block';
                        btnConfirm.textContent = 'Ya, Hapus';
                        btnConfirm.classList.add('dialog-btn-danger');
                    } else {
                        btnCancel.style.display = 'none';
                        btnConfirm.textContent = 'OK';
                        btnConfirm.classList.remove('dialog-btn-danger');
                    }

                    // Show overlay
                    overlay.style.display = 'flex';

                    // Handle clicks
                    const handleConfirm = () => {
                        cleanup();
                        resolve(true);
                    };

                    const handleCancel = () => {
                        overlay.style.display = 'none';
                        cleanup();
                        resolve(false);
                    };

                    // Klik OK/Confirm tidak langsung tutup overlay jika ini adalah konfirmasi
                    // Biarkan pemanggil (caller) yang menutup atau mengubah status dialog
                    const cleanup = () => {
                        btnConfirm.removeEventListener('click', handleConfirm);
                        btnCancel.removeEventListener('click', handleCancel);
                        // Jika bukan confirm, tutup langsung
                        if (!isConfirm) overlay.style.display = 'none';
                    };

                    btnConfirm.addEventListener('click', handleConfirm);
                    btnCancel.addEventListener('click', handleCancel);
                });
            }

            // --- UTILITY FUNCTION: Render Icon Grid untuk Kategori Modal ---
            function renderIconGrid(type = 'pengeluaran') {
                const container = document.getElementById('icon-grid-container-kat');
                if (!container) return;

                // Set class berdasarkan tipe untuk CSS selector
                container.className = `icon-picker-container grid-${type}`;

                // Nama icon: Button.png, Button-1.png, Button-2.png, ... Button-47.png
                const icons = ['Button.png'];
                for (let i = 1; i <= 47; i++) {
                    icons.push(`Button-${i}.png`);
                }

                const iconBasePath = '{{ asset('icons') }}';
                const shapeClass = type === 'pemasukan' ? 'icon-shape-pemasukan' : 'icon-shape-pengeluaran';

                container.innerHTML = icons.map(icon => `
                <div class="icon-option" onclick="selectIconKat(this, '${type}/${icon}')" data-neutral-icon="${iconBasePath}/netral/${icon}" data-type-icon="${iconBasePath}/${type}/${icon}">
                    <img class="icon-type" src="${iconBasePath}/${type}/${icon}" alt="${icon}"
                        onerror="this.src='${iconBasePath}/netral/${icon}'">
                    <img class="icon-neutral" src="${iconBasePath}/netral/${icon}" alt="${icon}" style="display: none;">
                </div>
            `).join('');
            }

            // --- UTILITY FUNCTION: Select Icon untuk Kategori Modal ---
            window.selectIconKat = function(element, iconValue) {
                // Hapus class active dari semua
                document.querySelectorAll('#icon-grid-container-kat .icon-option').forEach(el => {
                    const typeIcon = el.querySelector('.icon-type');
                    const neutralIcon = el.querySelector('.icon-neutral');
                    if (typeIcon) typeIcon.style.display = 'block';
                    if (neutralIcon) neutralIcon.style.display = 'none';
                    el.classList.remove('active');
                });
                // Tambah class active ke yang dipilih
                element.classList.add('active');
                const typeIcon = element.querySelector('.icon-type');
                const neutralIcon = element.querySelector('.icon-neutral');
                if (typeIcon) typeIcon.style.display = 'none';
                if (neutralIcon) neutralIcon.style.display = 'block';
                document.getElementById('modal-ikon').value = iconValue;
            }

            const btnCetakLaporan = document.getElementById('btn-cetak-laporan');
            const printLaporanOverlay = document.getElementById('print-laporan-overlay');
            const checkboxRingkasan = document.getElementById('checkbox-ringkasan');
            const checkboxGrafik = document.getElementById('checkbox-grafik');
            const checkboxRincian = document.getElementById('checkbox-rincian');
            const previewContainer = document.getElementById('print-preview-container');
            const btnDownloadPdf = document.getElementById('btn-download-pdf');

            let currentPrintData = null;

            btnCetakLaporan.addEventListener('click', async function() {
                printLaporanOverlay.style.display = 'flex';
                await loadPrintData();
                updatePrintPreview();
            });

            async function loadPrintData() {
                try {
                    const response = await fetch('/api/dashboard-data', {
                        headers: API_HEADERS_GET
                    });
                    if (!response.ok) throw new Error('Failed to load dashboard data');
                    currentPrintData = await response.json();
                } catch (error) {
                    console.error('Error loading print data:', error);
                    currentPrintData = {};
                }
            }

            function updatePrintPreview() {
                previewContainer.innerHTML = '';
                if (checkboxRingkasan.checked && currentPrintData) {
                    previewContainer.innerHTML += renderRingkasanKeuangan();
                }
                if (checkboxGrafik.checked && currentPrintData) {
                    previewContainer.innerHTML += renderGrafikKas();
                }
                if (checkboxRincian.checked && currentPrintData) {
                    previewContainer.innerHTML += renderRincianTransaksi();
                }
                if (!checkboxRingkasan.checked && !checkboxGrafik.checked && !checkboxRincian.checked) {
                    previewContainer.innerHTML =
                        '<p style="color: #999; text-align: center; padding: 40px;">Pilih minimal satu opsi untuk ditampilkan</p>';
                }
            }

            function renderRingkasanKeuangan() {
                const summary = currentPrintData.summary || {};
                return `
                <div style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #007BFF;">Ringkasan Keuangan</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="padding: 12px; background: #f0f7ff; border-radius: 6px;">
                            <p style="margin: 0; font-size: 12px; color: #666;">SALDO</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px; font-weight: bold; color: #007BFF;">${formatRupiah(summary.saldo_real || 0)}</p>
                        </div>
                        <div style="padding: 12px; background: #e8f5e9; border-radius: 6px;">
                            <p style="margin: 0; font-size: 12px; color: #666;">PEMASUKAN</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px; font-weight: bold; color: #4caf50;">${formatRupiah(summary.total_pemasukan || 0)}</p>
                        </div>
                        <div style="padding: 12px; background: #ffebee; border-radius: 6px;">
                            <p style="margin: 0; font-size: 12px; color: #666;">PENGELUARAN</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px; font-weight: bold; color: #f44336;">${formatRupiah(summary.total_pengeluaran || 0)}</p>
                        </div>
                        <div style="padding: 12px; background: #fff3e0; border-radius: 6px;">
                            <p style="margin: 0; font-size: 12px; color: #666;">LABA</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px; font-weight: bold; color: #ff9800;">${formatRupiah(summary.laba || 0)}</p>
                        </div>
                    </div>
                </div>
            `;
            }

            function renderGrafikKas() {
                return `
                <div style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #FF9800;">Grafik Kas</h3>
                    <p style="color: #999; font-size: 13px; text-align: center; padding: 30px 0;">Grafik akan ditampilkan dalam PDF</p>
                </div>
            `;
            }

            function renderRincianTransaksi() {
                const transactions = currentPrintData.recent_transactions || [];
                if (transactions.length === 0) {
                    return `<div style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #4CAF50;">Rincian Transaksi</h3>
                    <p style="color: #999; text-align: center;">Tidak ada transaksi</p>
                </div>`;
                }

                let html = `
                <div style="margin-bottom: 20px;">
                    <h3 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #4CAF50;">Rincian Transaksi</h3>
                    <div style="font-size: 12px;">
            `;

                transactions.slice(0, 10).forEach(tx => {
                    const isIncome = tx.category?.tipe === 'pemasukan';
                    const color = isIncome ? '#4caf50' : '#f44336';
                    const sign = isIncome ? '+' : '-';

                    html += `
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                        <div>
                            <p style="margin: 0; font-weight: 500;">${tx.category?.nama_kategori || 'N/A'}</p>
                            <p style="margin: 3px 0 0 0; color: #999; font-size: 11px;">${formatDate(tx.tanggal_transaksi)}</p>
                        </div>
                        <p style="margin: 0; font-weight: bold; color: ${color};">${sign}${formatRupiah(tx.jumlah)}</p>
                    </div>
                `;
                });

                html += `</div></div>`;
                return html;
            }

            checkboxRingkasan.addEventListener('change', updatePrintPreview);
            checkboxGrafik.addEventListener('change', updatePrintPreview);
            checkboxRincian.addEventListener('change', updatePrintPreview);

            btnDownloadPdf.addEventListener('click', async function() {
                const selectedSections = {
                    ringkasan: checkboxRingkasan.checked,
                    grafik: checkboxGrafik.checked,
                    rincian: checkboxRincian.checked
                };

                if (!selectedSections.ringkasan && !selectedSections.grafik && !selectedSections
                    .rincian) {
                    await showDialog('Pilih minimal satu opsi untuk di-print', 'warning');
                    return;
                }

                btnDownloadPdf.disabled = true;
                btnDownloadPdf.textContent = 'Membuat PDF...';

                try {
                    const response = await fetch('/api/print-laporan', {
                        method: 'POST',
                        headers: API_HEADERS,
                        body: JSON.stringify({
                            sections: selectedSections
                        })
                    });

                    if (response.ok) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download =
                            `Laporan_Keuangan_${new Date().toLocaleDateString('id-ID').replace(/\//g, '-')}.pdf`;
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                        window.URL.revokeObjectURL(url);
                        closeModal(printLaporanOverlay);
                        await showDialog('PDF berhasil diunduh', 'success');
                    } else {
                        try {
                            const errorData = await response.json();
                            let errorMsg = errorData.error || 'Kesalahan tidak diketahui';
                            if (response.status === 503 && errorData.message) {
                                errorMsg = errorData.message;
                                if (errorData.solution) {
                                    errorMsg += '\n\n' + errorData.solution;
                                }
                            } else if (errorData.message) {
                                errorMsg += ': ' + errorData.message;
                            }
                            await showDialog('Gagal membuat PDF:\n\n' + errorMsg, 'error');
                        } catch (e) {
                            await showDialog('Gagal membuat PDF: HTTP ' + response.status, 'error');
                        }
                    }
                } catch (error) {
                    console.error('Error generating PDF:', error);
                    await showDialog('Terjadi kesalahan saat membuat PDF: ' + error.message, 'error');
                } finally {
                    btnDownloadPdf.disabled = false;
                    btnDownloadPdf.textContent = 'Download sebagai PDF';
                }
            });

            /*FUNGSI PILIH BULAN DARI DROPDOWN*/
            let currentMonthFilter = 'bulan_ini';

            // --- A. LOGIKA INTERAKSI DROPDOWN ---
            const monthBtn = document.getElementById('month-filter-btn');
            const monthMenu = document.getElementById('month-filter-menu');
            const monthPicker = document.getElementById('custom-month-picker');
            const btnSpan = monthBtn.querySelector('span');

            monthBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                monthMenu.style.display = monthMenu.style.display === 'flex' ? 'none' : 'flex';
            });

            document.addEventListener('click', (e) => {
                if (monthMenu && !monthMenu.contains(e.target) && !monthBtn.contains(e.target)) {
                    monthMenu.style.display = 'none';
                }
            });

            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', () => {
                    document.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove(
                        'active'));
                    item.classList.add('active');
                    monthPicker.value = '';

                    btnSpan.textContent = item.textContent;
                    currentMonthFilter = item.dataset.value;
                    monthMenu.style.display = 'none';
                    fetchTransactions();
                });
            });

            monthPicker.addEventListener('change', function() {
                const selectedValue = this.value;
                if (selectedValue) {
                    document.querySelectorAll('.dropdown-item').forEach(el => el.classList.remove(
                        'active'));
                    const dateObj = new Date(selectedValue + '-01');
                    const monthName = dateObj.toLocaleDateString('id-ID', {
                        month: 'long',
                        year: 'numeric'
                    });
                    btnSpan.textContent = monthName;
                    currentMonthFilter = selectedValue;
                    monthMenu.style.display = 'none';
                    fetchTransactions();
                }
            });

            async function fetchTransactions(url = null) {
                let targetUrl;
                if (url) {
                    targetUrl = new URL(url);
                } else {
                    targetUrl = new URL(API_TRANSACTIONS);
                }

                const searchInputEl = document.getElementById('search-input');
                const minNominalEl = document.getElementById('filter-min-nominal');
                const maxNominalEl = document.getElementById('filter-max-nominal');
                const tipeFilterEl = document.getElementById('filter-tipe');

                const searchVal = searchInputEl ? searchInputEl.value : '';
                const minNominal = minNominalEl ? minNominalEl.value : '';
                const maxNominal = maxNominalEl ? maxNominalEl.value : '';
                const tipeVal = tipeFilterEl ? tipeFilterEl.value : '';

                if (searchVal) targetUrl.searchParams.set('search', searchVal);
                if (tipeVal) targetUrl.searchParams.set('tipe', tipeVal);
                if (minNominal) targetUrl.searchParams.set('min_nominal', minNominal);
                if (maxNominal) targetUrl.searchParams.set('max_nominal', maxNominal);

                const now = new Date();
                const fmt = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' +
                    String(d.getDate()).padStart(2, '0');

                const filterMode = (typeof currentMonthFilter !== 'undefined') ? currentMonthFilter :
                    'bulan_ini';

                if (filterMode === 'bulan_ini') {
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
                    const [y, m] = filterMode.split('-');
                    const year = parseInt(y);
                    const monthIndex = parseInt(m) - 1;
                    const start = new Date(year, monthIndex, 1);
                    const end = new Date(year, monthIndex + 1, 0);
                    targetUrl.searchParams.set('start_date', fmt(start));
                    targetUrl.searchParams.set('end_date', fmt(end));
                }

                if (transactionListContainer) {
                    transactionListContainer.innerHTML =
                        '<div class="transaction-row" style="justify-content: center; padding: 30px;">Sedang memuat data...</div>';
                }

                try {
                    const response = await fetch(targetUrl.toString(), {
                        headers: API_HEADERS_GET
                    });

                    if (response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }

                    const jsonData = await response.json();

                    if (jsonData.pagination && jsonData.pagination.data) {
                        renderTransactionRows(jsonData.pagination.data);
                        renderPaginationLinks(jsonData.pagination.links);
                    } else {
                        renderTransactionRows([]);
                    }

                    if (jsonData.summary) {
                        if (typeof updateFooterSummary === 'function') {
                            updateFooterSummary(jsonData.summary);
                        }
                    }

                } catch (error) {
                    console.error('Error:', error);
                    if (transactionListContainer) {
                        transactionListContainer.innerHTML =
                            '<div class="transaction-row" style="color:red; justify-content:center; padding:30px;">Gagal memuat data.</div>';
                    }
                }
            }

            const filterBtn = document.getElementById('filter-button');
            const filterOverlay = document.getElementById('filter-modal-overlay');
            const filterForm = document.getElementById('filter-form');
            const resetFilterBtn = document.getElementById('btn-reset-filter');

            if (filterBtn) {
                filterBtn.addEventListener('click', () => {
                    filterOverlay.style.display = 'flex';
                });
            }

            if (filterForm) {
                filterForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    closeModal(filterOverlay);
                    fetchTransactions();
                    filterBtn.style.color = '#2563eb';
                    filterBtn.style.borderColor = '#2563eb';
                });
            }

            if (resetFilterBtn) {
                resetFilterBtn.addEventListener('click', () => {
                    filterForm.reset();
                    closeModal(filterOverlay);
                    fetchTransactions();
                    filterBtn.style.color = '';
                    filterBtn.style.borderColor = '';
                });
            }

            const checkAllBtn = document.getElementById('check-all-transactions');
            checkAllBtn.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.check-item');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBulkDeleteButton();
            });

            transactionListContainer.addEventListener('change', function(e) {
                if (e.target.classList.contains('check-item')) {
                    updateBulkDeleteButton();
                }
            });

            function updateBulkDeleteButton() {
                const selected = document.querySelectorAll('.check-item:checked');
                const btn = document.getElementById('bulk-delete-btn');
                const countSpan = document.getElementById('selected-count');

                if (selected.length > 0) {
                    btn.style.display = 'inline-flex';
                    countSpan.textContent = selected.length;
                } else {
                    btn.style.display = 'none';
                }
            }

            // 4. Aksi Klik Tombol Hapus Massal (FIXED LOGIC)
            document.getElementById('bulk-delete-btn').addEventListener('click', async function() {
                const selected = document.querySelectorAll('.check-item:checked');
                if (selected.length === 0) return;

                const confirmed = await showDialog(
                    `Yakin ingin menghapus ${selected.length} transaksi terpilih?`,
                    'warning',
                    true
                );

                if (!confirmed) return;

                // Tampilkan loading dialog
                const dialogMessageEl = document.getElementById('dialog-message');
                const dialogOverlay = document.getElementById('dialog-overlay');
                if (dialogMessageEl) dialogMessageEl.textContent = 'Sedang menghapus...';
                if (dialogOverlay) dialogOverlay.style.display = 'flex';
                document.querySelector('.dialog-actions').style.display = 'none';

                const ids = Array.from(selected).map(cb => cb.dataset.id);

                let successCount = 0;
                let failCount = 0;

                // Loop fetch delete satu-satu
                for (const id of ids) {
                    try {
                        const response = await fetch(`${API_TRANSACTIONS}/${id}`, {
                            method: 'DELETE',
                            headers: API_HEADERS
                        });

                        // [CRITICAL FIX] Cek apakah server merespons OK (Status 200-299)
                        // Pastikan Backend (TransactionController) juga sudah diperbaiki
                        // agar me-return 200 meskipun data sudah soft-deleted (withTrashed)
                        if (response.ok) {
                            successCount++;
                            // Hapus baris dari tabel HTML secara langsung agar UI responsif
                            const checkbox = document.querySelector(`.check-item[data-id="${id}"]`);
                            if (checkbox) {
                                const row = checkbox.closest('.transaction-row');
                                if (row) row.remove();
                            }
                        } else {
                            console.warn(`Gagal menghapus ID ${id}: Status ${response.status}`);
                            failCount++;
                        }
                    } catch (e) {
                        console.error(`Network error pada ID ${id}:`, e);
                        failCount++;
                    }
                }

                // Tutup loading dialog
                if (dialogOverlay) dialogOverlay.style.display = 'none';
                document.querySelector('.dialog-actions').style.display = 'flex';

                // Tampilkan notifikasi hasil
                if (failCount > 0) {
                    await showDialog(`Berhasil: ${successCount}. Gagal: ${failCount}.`, 'warning', false);
                } else {
                    await showDialog(`Berhasil menghapus ${successCount} transaksi.`, 'success', false);
                }

                fetchTransactions(); // Refresh Tabel Total
                document.getElementById('bulk-delete-btn').style.display = 'none';
                document.getElementById('check-all-transactions').checked = false;
            });

            window.openEditModal = function(tx) {
                txForm.reset();
                document.getElementById('transaksi-modal-title').textContent = 'Edit Transaksi';
                document.getElementById('transaksi-modal-submit-btn').textContent = 'Simpan Perubahan';
                document.getElementById('modal-tx-id').value = tx.id;
                document.getElementById('modal-tx-jumlah').value = formatNominal(tx.jumlah.toString());
                document.getElementById('modal-tx-catatan').value = tx.catatan || '';

                if (tx.tanggal_transaksi) {
                    const dateVal = tx.tanggal_transaksi.replace(' ', 'T').substring(0, 16);
                    document.getElementById('modal-tx-tanggal').value = dateVal;
                }

                const kategori = tx.category || {};
                const tipe = kategori.tipe || 'pengeluaran';

                setActiveTab(txModalTabs, txTipeHidden, tipe);
                populateCategoryDropdown(tipe, kategori.id);
                txModalOverlay.style.display = 'flex';
            };

            openAddTxBtn.addEventListener('click', function() {
                txForm.reset();
                document.getElementById('transaksi-modal-title').textContent = 'Tambah Transaksi';
                document.getElementById('transaksi-modal-submit-btn').textContent = 'Tambah Transaksi';
                document.getElementById('modal-tx-id').value = '';

                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                document.getElementById('modal-tx-tanggal').value = now.toISOString().slice(0, 16);

                setActiveTab(txModalTabs, txTipeHidden, 'pengeluaran');
                populateCategoryDropdown('pengeluaran');
                txModalOverlay.style.display = 'flex';
            });

            function renderTransactionRows(transactions) {
                const container = document.getElementById('transaction-list-container');
                container.innerHTML = '';

                if (transactions.length === 0) {
                    container.innerHTML =
                        '<div class="transaction-row" style="justify-content: center; padding: 30px; color: #94a3b8;">Belum ada transaksi.</div>';
                    return;
                }

                transactions.forEach(tx => {
                    const row = document.createElement('div');
                    row.className = 'transaction-row';
                    row.dataset.json = JSON.stringify(tx);

                    const category = tx.category || {};
                    const isPemasukan = category.tipe === 'pemasukan';
                    const amountClass = isPemasukan ? 'text-green' : 'text-red';
                    const amountSign = isPemasukan ? '+' : '-';
                    const shapeClass = isPemasukan ? 'icon-shape-pemasukan' : 'icon-shape-pengeluaran';

                    let iconHtml = '';
                    if (category.ikon && (category.ikon.includes('.png') || category.ikon.includes(
                            '.jpg') || category.ikon.includes('.svg') || category.ikon.includes(
                            '.jpeg'))) {
                        let iconPath = category.ikon;
                        if (!iconPath.includes('/')) {
                            iconPath = `${category.tipe}/${iconPath}`;
                        }
                        const iconUrl = `{{ asset('icons') }}/${iconPath}`;
                        iconHtml =
                            `<img src="${iconUrl}" alt="icon" style="width:24px; height:24px; object-fit:contain;">`;
                    } else {
                        const iconClass = category.ikon || 'fa-solid fa-question';
                        iconHtml = `<i class="${iconClass}"></i>`;
                    }

                    const safeCatatan = escapeHtml(tx.catatan || '-');
                    const safeNamaKategori = category.nama_kategori ?
                        escapeHtml(category.nama_kategori) :
                        '<span style="color:red; font-style:italic;">(Kategori Terhapus)</span>';

                    const displayDate = formatDate(tx.tanggal_transaksi || tx.created_at);

                    row.innerHTML = `
                    <div class="cell-check" onclick="event.stopPropagation()">
                        <input type="checkbox" class="check-item" data-id="${tx.id}">
                    </div>

                    <div class="cell-kategori">
                        <span class="icon-wrapper ${shapeClass}">${iconHtml}</span>
                        ${safeNamaKategori}
                    </div>

                    <div class="cell-tanggal">${displayDate}</div>
                    <div class="cell-deskripsi" style="color: #334155;">${safeCatatan}</div>
                    <div class="cell-nominal ${amountClass}">${amountSign}${formatRupiah(tx.jumlah)}</div>
                `;

                    row.addEventListener('click', function() {
                        openEditModal(tx);
                    });
                    row.style.cursor = 'pointer';
                    container.appendChild(row);
                });
            }

            function renderPaginationLinks(links) {
                paginationLinksContainer.innerHTML = '';
                links.forEach(link => {
                    if (link.url && !isNaN(link.label)) {
                        const pageButton = document.createElement('button');
                        pageButton.innerHTML = link.label;
                        pageButton.className = `pagination-link ${link.active ? 'active' : ''}`;
                        if (link.active) pageButton.disabled = true;
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

            async function populateCategoryDropdown(selectedTipe = 'pengeluaran', selectId = null) {
                const listContainer = document.getElementById('category-list-container');
                const triggerText = document.getElementById('selected-category-text');
                const hiddenInput = document.getElementById('modal-tx-kategori-select');

                listContainer.innerHTML =
                    '<div class="dropdown-item placeholder" style="cursor:default;">Memuat...</div>';
                triggerText.textContent = '-- Pilih Kategori --';
                hiddenInput.value = '';

                try {
                    const response = await fetch(`${API_CATEGORIES}?tipe=${selectedTipe}`, {
                        headers: API_HEADERS_GET
                    });
                    const categories = await response.json();
                    listContainer.innerHTML = '';

                    if (categories.length > 0) {
                        categories.forEach(cat => {
                            const item = document.createElement('div');
                            item.className = 'dropdown-item';
                            item.textContent = cat.nama_kategori;
                            item.dataset.value = cat.id;

                            if (selectId && String(cat.id) === String(selectId)) {
                                item.classList.add('selected');
                                triggerText.textContent = cat.nama_kategori;
                                hiddenInput.value = cat.id;
                            }

                            item.addEventListener('click', function() {
                                triggerText.textContent = cat.nama_kategori;
                                hiddenInput.value = cat.id;
                                document.querySelectorAll('.dropdown-item').forEach(el => el
                                    .classList.remove('selected'));
                                this.classList.add('selected');
                                document.getElementById('category-dropdown').classList.remove(
                                    'active');
                            });
                            listContainer.appendChild(item);
                        });
                    } else {
                        listContainer.innerHTML =
                            '<div class="dropdown-item placeholder" style="cursor:default; color:#94a3b8;">Belum ada kategori</div>';
                    }
                } catch (error) {
                    console.error('Error fetching categories:', error);
                    listContainer.innerHTML =
                        '<div class="dropdown-item placeholder" style="color:red;">Gagal memuat</div>';
                }
            }

            const catDropdown = document.getElementById('category-dropdown');
            const catTrigger = document.getElementById('category-dropdown-btn');

            if (catTrigger) {
                catTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    catDropdown.classList.toggle('active');
                });
            }

            document.addEventListener('click', function(e) {
                if (catDropdown && !catDropdown.contains(e.target)) {
                    catDropdown.classList.remove('active');
                }
            });

            openAddTxBtn.addEventListener('click', function() {
                txForm.reset();
                txMessage.textContent = '';
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const currentLocalTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                document.getElementById('modal-tx-tanggal').value = currentLocalTime;
                setActiveTab(txModalTabs, txTipeHidden, 'pengeluaran');
                populateCategoryDropdown('pengeluaran');
                txModalOverlay.style.display = 'flex';
            });

            txForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                txMessage.textContent = 'Menyimpan...';

                const formData = new FormData(txForm);
                const data = Object.fromEntries(formData.entries());
                const cleanJumlah = data.jumlah.replace(/\./g, '').replace(',', '.');
                const jumlah = parseFloat(cleanJumlah);

                if (isNaN(jumlah) || jumlah < 0 || jumlah > 999999999999999) {
                    txMessage.textContent =
                        'Error: Nominal harus antara 0 hingga Rp 999.999.999.999.999';
                    return;
                }

                data.jumlah = cleanJumlah;
                const id = document.getElementById('modal-tx-id').value;
                let url = API_TRANSACTIONS;
                let method = 'POST';

                if (id) {
                    url = `${API_TRANSACTIONS}/${id}`;
                    method = 'PUT';
                }

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: API_HEADERS,
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();

                    if (response.ok) {
                        closeModal(txModalOverlay);
                        fetchTransactions();
                        txMessage.textContent = '';
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

            const nominalInput = document.getElementById('modal-tx-jumlah');
            nominalInput.addEventListener('input', function() {
                this.value = formatNominal(this.value);
            });

            openKategoriModalLink.addEventListener('click', function(e) {
                e.preventDefault();
                katForm.reset();
                katMessage.textContent = '';
                const currentTxTipe = txTipeHidden.value;
                setActiveTab(katModalTabs, katTipeHidden, currentTxTipe);
                renderIconGrid(currentTxTipe);
                katModalOverlay.style.display = 'flex';
            });

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
                    const result = await response.json();

                    if (response.status === 201) {
                        closeModal(katModalOverlay);
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
                    renderIconGrid(tipe);
                });
            });

            let debounceTimerBukuKas;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(debounceTimerBukuKas);
                debounceTimerBukuKas = setTimeout(() => {
                    const url = new URL(API_TRANSACTIONS);
                    url.searchParams.append('search', e.target.value);
                    fetchTransactions(url.toString());
                }, 500);
            });

            const initialUrl = new URL(API_TRANSACTIONS);
            const urlParams = new URLSearchParams(window.location.search);
            const searchQuery = urlParams.get('search');

            if (searchQuery) {
                initialUrl.searchParams.append('search', searchQuery);
                searchInput.value = searchQuery;
            }

            fetchTransactions(initialUrl.toString());
        });

        function updateFooterSummary(summary) {
            const elMasuk = document.getElementById('footer-total-pemasukan');
            const elKeluar = document.getElementById('footer-total-pengeluaran');
            const elLaba = document.getElementById('footer-laba-badge');
            const saldoDisplay = document.getElementById('saldo-display');

            if (elMasuk) elMasuk.textContent = formatRupiah(summary.total_pemasukan);
            if (elKeluar) elKeluar.textContent = formatRupiah(summary.total_pengeluaran);
            if (saldoDisplay) saldoDisplay.textContent = formatRupiah(summary.saldo_real);

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
