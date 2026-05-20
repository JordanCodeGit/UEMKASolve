@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="summary-grid bendahara-summary-grid">
        <div class="summary-card summary-saldo">
            <div class="card-content">
                <div class="card-header">
                    <div class="icon-box icon-white">
                        <img src="{{ asset('icons/saldo.png') }}" alt="Saldo" class="icon-img">
                    </div>
                    <p>SALDO</p>
                </div>
                <h3 id="bendahara-summary-saldo">Memuat...</h3>
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
                <h3 id="bendahara-summary-pemasukan">Memuat...</h3>
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
                <h3 id="bendahara-summary-pengeluaran">Memuat...</h3>
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
            <ul id="bendahara-recent-transactions-list" class="transaction-list">
                <li class="transaction-item" style="justify-content: center; color: var(--text-secondary);">
                    Memuat...
                </li>
            </ul>

            <div id="bendahara-recent-transactions-card-container" class="transaction-card-container" aria-live="polite">
                <div class="transaction-card-empty">Memuat...</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

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

            function renderRecentTransactions(transactions) {
                const list = document.getElementById('bendahara-recent-transactions-list');
                const cardContainer = document.getElementById('bendahara-recent-transactions-card-container');

                list.innerHTML = '';
                cardContainer.innerHTML = '';

                if (!transactions.length) {
                    list.innerHTML = '<li class="transaction-item" style="justify-content: center; color: #94a3b8; padding: 20px;">Belum ada transaksi.</li>';
                    cardContainer.innerHTML = '<div class="transaction-card-empty">Belum ada transaksi.</div>';
                    return;
                }

                transactions.forEach(tx => {
                    const category = tx.category || {};
                    const isPemasukan = category.tipe === 'pemasukan';
                    const amountClass = isPemasukan ? 'text-green' : 'text-red';
                    const iconBg = isPemasukan ? 'bg-green-light' : 'bg-blue-light';
                    const iconClass = category.ikon || 'fa-solid fa-question';
                    const iconHtml = iconClass.includes('.') ?
                        `<img src="{{ asset('icons') }}/${iconClass}" alt="icon">` :
                        `<i class="${iconClass}"></i>`;
                    const dateObj = parseTxDate(tx.tanggal_transaksi || tx.created_at);
                    const amountSign = isPemasukan ? '+' : '-';

                    const row = document.createElement('li');
                    row.className = 'transaction-item';
                    row.innerHTML = `
                        <div class="icon-circle ${iconBg}">${iconHtml}</div>
                        <div class="transaction-details"><strong>${escapeHtml(category.nama_kategori || 'Tanpa Kategori')}</strong></div>
                        <div class="transaction-datetime">
                            <span>${dateObj.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</span>
                            <span class="time">${dateObj.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}</span>
                        </div>
                        <div class="transaction-note">${escapeHtml(tx.catatan || '')}</div>
                        <div class="transaction-amount ${amountClass}">${amountSign}${formatRupiah(tx.jumlah)}</div>
                    `;
                    list.appendChild(row);

                    const card = document.createElement('div');
                    card.className = 'tx-item';
                    card.innerHTML = `
                        <div class="tx-item-left">
                            <span class="icon-wrapper ${isPemasukan ? 'icon-shape-pemasukan' : 'icon-shape-pengeluaran'}">${iconHtml}</span>
                            <div class="tx-item-text">
                                <div class="tx-item-title">${escapeHtml(category.nama_kategori || 'Tanpa Kategori')}</div>
                                <div class="tx-item-subtitle">${escapeHtml(tx.catatan || '-')}</div>
                            </div>
                        </div>
                        <div class="tx-item-amount ${amountClass}">${amountSign}${formatRupiah(tx.jumlah)}</div>
                    `;
                    cardContainer.appendChild(card);
                });
            }

            async function loadDashboard() {
                try {
                    const response = await fetch("{{ url('/api/dashboard') }}", {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token
                        }
                    });

                    if (response.status === 401) {
                        localStorage.removeItem('auth_token');
                        window.location.href = "{{ route('login') }}";
                        return;
                    }

                    const data = await response.json();
                    const summary = data.summary || {};

                    document.getElementById('bendahara-summary-saldo').textContent = formatRupiah(summary.saldo || 0);
                    document.getElementById('bendahara-summary-pemasukan').textContent = formatRupiah(summary.pemasukan || 0);
                    document.getElementById('bendahara-summary-pengeluaran').textContent = formatRupiah(summary.pengeluaran || 0);

                    renderRecentTransactions(data.recent_transactions || []);
                } catch (error) {
                    console.error('Bendahara dashboard error:', error);
                    renderRecentTransactions([]);
                }
            }

            loadDashboard();
        });
    </script>
@endpush
