@extends('layouts.app')

@section('title', 'Buku Kas')

@section('content')
    <div class="sekretaris-book">
        <div class="sekretaris-book__header">
            <div class="saldo-display-lg">
                <i class="fa-solid fa-wallet"></i>
                <h3 id="sekretaris-saldo">Rp 0</h3>
            </div>

            <button class="btn btn-gradient" id="sekretaris-print-btn">
                <i class="fa-solid fa-print"></i>
                <span class="btn-label">Cetak Buku Kas</span>
            </button>
        </div>

        <form class="sekretaris-filter-panel" id="sekretaris-filter-form">
            <div class="sekretaris-filter-field">
                <label>Rentang Waktu</label>
                <div class="sekretaris-date-range">
                    <input type="date" id="filter-start-date">
                    <input type="date" id="filter-end-date">
                </div>
            </div>

            <div class="sekretaris-filter-field">
                <label>Kategori</label>
                <select id="filter-category">
                    <option value="">Semua Kategori</option>
                </select>
            </div>

            <div class="sekretaris-filter-field">
                <label>Range Nominal (IDR)</label>
                <div class="sekretaris-nominal-range">
                    <input type="number" id="filter-min-nominal" placeholder="MIN">
                    <input type="number" id="filter-max-nominal" placeholder="Maks">
                </div>
            </div>

            <button type="submit" class="sekretaris-apply-filter">
                <i class="fa-solid fa-filter"></i>
                <span>Terapkan Filter</span>
            </button>
        </form>

        <div class="sekretaris-table-card">
            <div class="sekretaris-table">
                <div class="sekretaris-row sekretaris-row--header">
                    <div>Kategori</div>
                    <div>Tanggal & Waktu</div>
                    <div>Deskripsi</div>
                    <div>Status</div>
                    <div>Nominal</div>
                </div>

                <div id="sekretaris-transaction-list">
                    <div class="sekretaris-empty">Memuat data transaksi...</div>
                </div>
            </div>

            <div class="transaction-card-container sekretaris-card-list" id="sekretaris-card-list">
                <div class="transaction-card-empty">Memuat data transaksi...</div>
            </div>

            <div class="transaction-footer">
                <span class="footer-total footer-total-pemasukan">Total Pemasukan:
                    <strong class="text-green" id="sekretaris-total-pemasukan">Rp 0</strong>
                </span>
                <span class="footer-total footer-total-pengeluaran">Total Pengeluaran:
                    <strong class="text-red" id="sekretaris-total-pengeluaran">Rp 0</strong>
                </span>
                <span class="footer-laba">Laba:
                    <span class="laba-badge profit" id="sekretaris-laba">Rp 0</span>
                </span>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="sekretaris-status-modal" style="display: none;">
        <div class="modal-box sekretaris-status-box">
            <div class="modal-header">
                <h2>Edit Transaksi</h2>
                <button class="modal-close-btn" type="button" id="sekretaris-modal-close">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <form id="sekretaris-status-form">
                <input type="hidden" id="sekretaris-tx-id">

                <div class="modal-body">
                    <div class="form-group-modal">
                        <label>Jumlah</label>
                        <input type="text" id="sekretaris-modal-jumlah" class="form-input-modal" disabled>
                    </div>

                    <div class="form-group-modal">
                        <label>Kategori</label>
                        <input type="text" id="sekretaris-modal-kategori" class="form-input-modal" disabled>
                    </div>

                    <div class="form-group-modal">
                        <label>Tanggal</label>
                        <input type="text" id="sekretaris-modal-tanggal" class="form-input-modal" disabled>
                    </div>

                    <div class="form-group-modal">
                        <label>Catatan</label>
                        <textarea id="sekretaris-modal-catatan" class="form-input-modal" disabled></textarea>
                    </div>

                    <button type="button" class="sekretaris-receipt-btn" disabled>
                        <i class="fa-solid fa-camera"></i>
                        <span>Lihat Struk Transaksi</span>
                    </button>

                    <div class="form-group-modal">
                        <label>Status</label>
                        <select id="sekretaris-modal-status" class="form-input-modal">
                            <option value="verified">Verified</option>
                            <option value="flagged">Flagged</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div id="sekretaris-modal-message"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-modal" id="sekretaris-modal-cancel">Batal</button>
                    <button type="submit" class="btn btn-primary-modal" id="sekretaris-modal-save">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="sekretaris-print-overlay" style="display: none;">
        <div class="modal-box" style="max-width: 550px; max-height: 90vh; display: flex; flex-direction: column; padding: 0;">
            <div class="modal-header" style="padding: 20px; flex-shrink: 0; border-bottom: 1px solid #eee;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h2 style="margin:0; font-size: 1.25rem;">Preview Laporan Keuangan</h2>
                </div>
                <button class="modal-close-btn" type="button" id="sekretaris-print-close">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <div class="modal-body" style="padding: 20px; overflow-y: auto; flex: 1;">
                <div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px;">
                    <div style="margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="sekretaris-checkbox-ringkasan" checked>
                            <span style="font-weight: 500; color: #333;">Sertakan ringkasan keuangan</span>
                        </label>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="sekretaris-checkbox-grafik" checked>
                            <span style="font-weight: 500; color: #333;">Sertakan grafik kas</span>
                        </label>
                    </div>

                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" id="sekretaris-checkbox-rincian" checked>
                            <span style="font-weight: 500; color: #333;">Sertakan rincian transaksi</span>
                        </label>
                    </div>
                </div>

                <div id="sekretaris-print-preview"
                    style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background-color: white; min-height: 300px;">
                </div>
            </div>

            <div class="modal-footer" style="padding: 20px; flex-shrink: 0; border-top: 1px solid #eee; background: #fff;">
                <button type="button" class="btn btn-secondary-modal" id="sekretaris-print-cancel">Batal</button>
                <button type="button" class="btn btn-primary-modal" id="sekretaris-download-pdf">Download sebagai PDF</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const API_TRANSACTIONS = "{{ url('/api/transactions') }}";
            const API_CATEGORIES = "{{ url('/api/categories') }}";
            const API_PRINT = "{{ url('/api/print-laporan') }}";
            const API_PRINT_DATA = "{{ url('/api/dashboard-data') }}";
            const token = localStorage.getItem('auth_token');
            const headersGet = {
                'Accept': 'application/json',
                ...(token ? { 'Authorization': 'Bearer ' + token } : {})
            };
            const headersJson = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                ...(token ? { 'Authorization': 'Bearer ' + token } : {})
            };

            const listEl = document.getElementById('sekretaris-transaction-list');
            const cardListEl = document.getElementById('sekretaris-card-list');
            const filterForm = document.getElementById('sekretaris-filter-form');
            const categoryFilter = document.getElementById('filter-category');
            const modal = document.getElementById('sekretaris-status-modal');
            const modalMessage = document.getElementById('sekretaris-modal-message');
            const statusForm = document.getElementById('sekretaris-status-form');
            const printOverlay = document.getElementById('sekretaris-print-overlay');
            const printPreview = document.getElementById('sekretaris-print-preview');
            const checkboxRingkasan = document.getElementById('sekretaris-checkbox-ringkasan');
            const checkboxGrafik = document.getElementById('sekretaris-checkbox-grafik');
            const checkboxRincian = document.getElementById('sekretaris-checkbox-rincian');
            const btnDownloadPdf = document.getElementById('sekretaris-download-pdf');
            const transactionsById = new Map();
            let currentPrintData = null;

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(Number(number || 0));
            }

            function escapeHtml(text) {
                if (!text) return '';
                return String(text)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function parseTxDate(raw) {
                if (!raw) return new Date();
                if (typeof raw !== 'string') return new Date(raw);
                return new Date(raw.includes('T') ? raw : raw.replace(' ', 'T'));
            }

            function formatDate(raw) {
                const date = parseTxDate(raw);
                if (Number.isNaN(date.getTime())) return raw || '-';

                return `
                    <div class="sekretaris-date-cell">
                        <span>${date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</span>
                        <small>${date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false })}</small>
                    </div>
                `;
            }

            function formatDateText(raw) {
                const date = parseTxDate(raw);
                if (Number.isNaN(date.getTime())) return raw || '-';

                return date.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
            }

            function statusLabel(status) {
                const normalized = status || 'pending';
                return normalized.charAt(0).toUpperCase() + normalized.slice(1);
            }

            function iconHtml(category) {
                const icon = category?.ikon || 'fa-solid fa-question';
                if (icon.includes('.')) {
                    return `<img src="{{ asset('icons') }}/${icon}" alt="icon">`;
                }
                return `<i class="${icon}"></i>`;
            }

            function updateSummary(summary) {
                document.getElementById('sekretaris-saldo').textContent = formatRupiah(summary?.saldo_real || 0);
                document.getElementById('sekretaris-total-pemasukan').textContent = formatRupiah(summary?.total_pemasukan || 0);
                document.getElementById('sekretaris-total-pengeluaran').textContent = formatRupiah(summary?.total_pengeluaran || 0);

                const laba = Number(summary?.laba || 0);
                const labaEl = document.getElementById('sekretaris-laba');
                labaEl.textContent = formatRupiah(laba);
                labaEl.className = laba >= 0 ? 'laba-badge profit' : 'laba-badge loss';
                labaEl.style.backgroundColor = laba >= 0 ? '#16A34A' : '#DC2626';
                labaEl.style.color = '#ffffff';
            }

            function openStatusModal(tx) {
                const category = tx.category || {};
                const date = parseTxDate(tx.tanggal_transaksi);

                document.getElementById('sekretaris-tx-id').value = tx.id;
                document.getElementById('sekretaris-modal-jumlah').value = formatRupiah(tx.jumlah);
                document.getElementById('sekretaris-modal-kategori').value = category.nama_kategori || 'Tanpa Kategori';
                document.getElementById('sekretaris-modal-tanggal').value = Number.isNaN(date.getTime())
                    ? '-'
                    : date.toLocaleDateString('id-ID');
                document.getElementById('sekretaris-modal-catatan').value = tx.catatan || '';
                document.getElementById('sekretaris-modal-status').value = tx.status || 'pending';
                modalMessage.textContent = '';
                modal.style.display = 'flex';
            }

            function closeStatusModal() {
                modal.style.display = 'none';
            }

            function renderRows(transactions) {
                transactionsById.clear();
                listEl.innerHTML = '';
                cardListEl.innerHTML = '';

                if (!transactions.length) {
                    listEl.innerHTML = '<div class="sekretaris-empty">Belum ada transaksi sesuai filter.</div>';
                    cardListEl.innerHTML = '<div class="transaction-card-empty">Belum ada transaksi sesuai filter.</div>';
                    return;
                }

                transactions.forEach(tx => {
                    transactionsById.set(String(tx.id), tx);

                    const category = tx.category || {};
                    const isMasuk = category.tipe === 'pemasukan';
                    const amountClass = isMasuk ? 'text-green' : 'text-red';
                    const amountSign = isMasuk ? '+' : '-';
                    const shapeClass = isMasuk ? 'icon-shape-pemasukan' : 'icon-shape-pengeluaran';
                    const safeCategory = escapeHtml(category.nama_kategori || 'Tanpa Kategori');
                    const safeNote = escapeHtml(tx.catatan || '-');
                    const txStatus = tx.status || 'pending';

                    const row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'sekretaris-row sekretaris-row--item';
                    row.dataset.id = tx.id;
                    row.innerHTML = `
                        <div class="sekretaris-category-cell">
                            <span class="icon-wrapper ${shapeClass}">${iconHtml(category)}</span>
                            <span>${safeCategory}</span>
                        </div>
                        <div>${formatDate(tx.tanggal_transaksi)}</div>
                        <div class="sekretaris-note-cell">${safeNote}</div>
                        <div><span class="sekretaris-status sekretaris-status--${txStatus}">${statusLabel(txStatus)}</span></div>
                        <div class="${amountClass} sekretaris-amount">${amountSign}${formatRupiah(tx.jumlah)}</div>
                    `;
                    row.addEventListener('click', () => openStatusModal(tx));
                    listEl.appendChild(row);

                    const card = document.createElement('button');
                    card.type = 'button';
                    card.className = 'tx-item sekretaris-mobile-tx';
                    card.innerHTML = `
                        <div class="tx-item-left">
                            <span class="icon-wrapper ${shapeClass}">${iconHtml(category)}</span>
                            <div class="tx-item-text">
                                <div class="tx-item-title">${safeCategory}</div>
                                <div class="tx-item-subtitle">${safeNote}</div>
                                <span class="sekretaris-status sekretaris-status--${txStatus}">${statusLabel(txStatus)}</span>
                            </div>
                        </div>
                        <div class="tx-item-amount ${amountClass}">${amountSign}${formatRupiah(tx.jumlah)}</div>
                    `;
                    card.addEventListener('click', () => openStatusModal(tx));
                    cardListEl.appendChild(card);
                });
            }

            function buildUrl() {
                const url = new URL(API_TRANSACTIONS);
                url.searchParams.set('per_page', '50');

                const startDate = document.getElementById('filter-start-date').value;
                const endDate = document.getElementById('filter-end-date').value;
                const categoryId = categoryFilter.value;
                const minNominal = document.getElementById('filter-min-nominal').value;
                const maxNominal = document.getElementById('filter-max-nominal').value;

                if (startDate && endDate) {
                    url.searchParams.set('start_date', startDate);
                    url.searchParams.set('end_date', endDate);
                }
                if (categoryId) url.searchParams.set('category_id', categoryId);
                if (minNominal) url.searchParams.set('min_nominal', minNominal);
                if (maxNominal) url.searchParams.set('max_nominal', maxNominal);

                return url;
            }

            async function loadCategories() {
                try {
                    const response = await fetch(API_CATEGORIES, { headers: headersGet });
                    if (!response.ok) return;

                    const categories = await response.json();
                    categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.nama_kategori;
                        categoryFilter.appendChild(option);
                    });
                } catch (error) {
                    console.error('Gagal memuat kategori:', error);
                }
            }

            async function loadTransactions(url = buildUrl()) {
                listEl.innerHTML = '<div class="sekretaris-empty">Memuat data transaksi...</div>';
                cardListEl.innerHTML = '<div class="transaction-card-empty">Memuat data transaksi...</div>';

                try {
                    const response = await fetch(url.toString(), { headers: headersGet });
                    if (response.status === 401) {
                        localStorage.removeItem('auth_token');
                        window.location.href = "{{ route('login') }}";
                        return;
                    }

                    const data = await response.json();
                    const transactions = data.pagination?.data || [];

                    updateSummary(data.summary || {});
                    renderRows(transactions);
                } catch (error) {
                    console.error('Gagal memuat transaksi:', error);
                    listEl.innerHTML = '<div class="sekretaris-empty">Gagal memuat transaksi.</div>';
                    cardListEl.innerHTML = '<div class="transaction-card-empty">Gagal memuat transaksi.</div>';
                }
            }

            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                loadTransactions(buildUrl());
            });

            document.getElementById('sekretaris-modal-close').addEventListener('click', closeStatusModal);
            document.getElementById('sekretaris-modal-cancel').addEventListener('click', closeStatusModal);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeStatusModal();
            });

            statusForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const id = document.getElementById('sekretaris-tx-id').value;
                const status = document.getElementById('sekretaris-modal-status').value;
                const saveBtn = document.getElementById('sekretaris-modal-save');

                modalMessage.textContent = 'Menyimpan...';
                modalMessage.style.color = '#2563eb';
                saveBtn.disabled = true;

                try {
                    const response = await fetch(`${API_TRANSACTIONS}/${id}/status`, {
                        method: 'PATCH',
                        headers: headersJson,
                        body: JSON.stringify({ status })
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || 'Gagal menyimpan status.');
                    }

                    closeStatusModal();
                    loadTransactions(buildUrl());
                } catch (error) {
                    modalMessage.textContent = error.message;
                    modalMessage.style.color = '#dc2626';
                } finally {
                    saveBtn.disabled = false;
                }
            });

            async function loadPrintData() {
                try {
                    const response = await fetch(API_PRINT_DATA, { headers: headersGet });
                    if (!response.ok) throw new Error('Failed to load print data');
                    currentPrintData = await response.json();
                } catch (error) {
                    console.error('Gagal memuat preview laporan:', error);
                    currentPrintData = {};
                }
            }

            function updatePrintPreview() {
                printPreview.innerHTML = '';

                if (checkboxRingkasan.checked && currentPrintData) {
                    const summary = currentPrintData.summary || {};
                    printPreview.innerHTML += `
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

                if (checkboxGrafik.checked && currentPrintData) {
                    printPreview.innerHTML += `
                        <div style="margin-bottom: 20px;">
                            <h3 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #FF9800;">Grafik Kas</h3>
                            <p style="color: #999; font-size: 13px; text-align: center; padding: 30px 0;">Grafik akan ditampilkan dalam PDF</p>
                        </div>
                    `;
                }

                if (checkboxRincian.checked && currentPrintData) {
                    const transactions = currentPrintData.recent_transactions || [];
                    let detailHtml = `
                        <div style="margin-bottom: 20px;">
                            <h3 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #4CAF50;">Rincian Transaksi</h3>
                    `;

                    if (!transactions.length) {
                        detailHtml += '<p style="color: #999; text-align: center;">Tidak ada transaksi</p>';
                    } else {
                        detailHtml += '<div style="font-size: 12px;">';
                        transactions.slice(0, 10).forEach(tx => {
                            const isIncome = tx.category?.tipe === 'pemasukan';
                            const color = isIncome ? '#4caf50' : '#f44336';
                            const sign = isIncome ? '+' : '-';
                            detailHtml += `
                                <div style="display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <div>
                                        <p style="margin: 0; font-weight: 500;">${escapeHtml(tx.category?.nama_kategori || 'N/A')}</p>
                                        <p style="margin: 3px 0 0 0; color: #999; font-size: 11px;">${formatDateText(tx.tanggal_transaksi)}</p>
                                    </div>
                                    <p style="margin: 0; font-weight: bold; color: ${color};">${sign}${formatRupiah(tx.jumlah)}</p>
                                </div>
                            `;
                        });
                        detailHtml += '</div>';
                    }

                    detailHtml += '</div>';
                    printPreview.innerHTML += detailHtml;
                }

                if (!checkboxRingkasan.checked && !checkboxGrafik.checked && !checkboxRincian.checked) {
                    printPreview.innerHTML = '<p style="color: #999; text-align: center; padding: 40px;">Pilih minimal satu opsi untuk ditampilkan</p>';
                }
            }

            document.getElementById('sekretaris-print-btn').addEventListener('click', async function() {
                printOverlay.style.display = 'flex';
                printPreview.innerHTML = '<p style="color: #999; text-align: center; padding: 40px;">Memuat preview...</p>';
                await loadPrintData();
                updatePrintPreview();
            });

            document.getElementById('sekretaris-print-close').addEventListener('click', () => printOverlay.style.display = 'none');
            document.getElementById('sekretaris-print-cancel').addEventListener('click', () => printOverlay.style.display = 'none');
            printOverlay.addEventListener('click', function(e) {
                if (e.target === printOverlay) printOverlay.style.display = 'none';
            });

            [checkboxRingkasan, checkboxGrafik, checkboxRincian].forEach(checkbox => {
                checkbox.addEventListener('change', updatePrintPreview);
            });

            btnDownloadPdf.addEventListener('click', async function() {
                const selectedSections = {
                    ringkasan: checkboxRingkasan.checked,
                    grafik: checkboxGrafik.checked,
                    rincian: checkboxRincian.checked
                };

                if (!selectedSections.ringkasan && !selectedSections.grafik && !selectedSections.rincian) {
                    alert('Pilih minimal satu opsi untuk di-print');
                    return;
                }

                btnDownloadPdf.disabled = true;
                btnDownloadPdf.textContent = 'Membuat PDF...';
                try {
                    const response = await fetch(API_PRINT, {
                        method: 'POST',
                        headers: headersJson,
                        body: JSON.stringify({
                            sections: selectedSections
                        })
                    });

                    if (!response.ok) {
                        let message = 'Gagal mencetak buku kas.';
                        try {
                            const data = await response.json();
                            message = data.error || data.message || message;
                        } catch (e) {}
                        throw new Error(message);
                    }

                    const blob = await response.blob();
                    const objectUrl = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = objectUrl;
                    link.download = `Buku_Kas_${new Date().toLocaleDateString('id-ID').replace(/\//g, '-')}.pdf`;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(objectUrl);
                    printOverlay.style.display = 'none';
                } catch (error) {
                    alert(error.message);
                } finally {
                    btnDownloadPdf.disabled = false;
                    btnDownloadPdf.textContent = 'Download sebagai PDF';
                }
            });

            const now = new Date();
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
            const toInputDate = date => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            document.getElementById('filter-start-date').value = toInputDate(firstDay);
            document.getElementById('filter-end-date').value = toInputDate(lastDay);

            loadCategories();
            loadTransactions();
        });
    </script>
@endpush
