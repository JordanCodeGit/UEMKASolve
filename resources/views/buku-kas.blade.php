@extends('layouts.app')

@section('title', 'Buku Kas')

@section('content')

<div class="bukukas-header">
    <div class="saldo-display-lg">
        <i class="fa-solid fa-wallet"></i>
        <h3>Rp 1.103.000</h3> 
    </div>
    <div class="header-actions">
        <div class="dropdown-with-icon">
            <i class="fa-solid fa-calendar-days"></i>
            <select class="dropdown-simple-bukukas">
                <option>Oktober - 2024</option>
                <option>September - 2024</option>
            </select>
        </div>

        <button class="btn btn-gradient">
            <i class="fa-solid fa-print"></i> Cetak Buku Kas
        </button>
    </div>
</div>

<div class="bukukas-toolbar">
    
    <div class="toolbar-left">
        <div class="search-bar-lg">
            <i class="fa-solid fa-search"></i>
            <input type="text" placeholder="Cari transaksi...">
        </div>
        
        <button class="btn-filter"> <i class="fa-solid fa-filter"></i> Filter
        </button>
    </div>
    
    <div class="toolbar-actions">
        <button class="btn-primary-green">
            <i class="fa-solid fa-plus"></i> Tambah Transaksi
        </button>
    </div>
</div>

<div class="transaction-table-card">
    
    <div class="transaction-row header">
        <div class="cell-check"><input type="checkbox" id="check-all"></div>
        <div class="cell-kategori">Kategori</div>
        <div class="cell-tanggal">Tanggal & Waktu</div>
        <div class="cell-deskripsi">Deskripsi</div>
        <div class="cell-nominal">Nominal</div>
    </div>
    
    <div class="transaction-row">
        <div class="cell-check"><input type="checkbox" class="check-item"></div>
        <div class="cell-kategori">
            <span class="icon-wrapper bg-green-light"><i class="fa-solid fa-shopping-cart"></i></span>
            Penjualan Produk
        </div>
        <div class="cell-tanggal">
            18 Oktober 2024
            <small>14:30</small>
        </div>
        <div class="cell-deskripsi">Penjualan Produk A hari Sabtu</div>
        <div class="cell-nominal text-green">+Rp1.500.000</div>
    </div>
    
    <div class="transaction-row">
        <div class="cell-check"><input type="checkbox" class="check-item"></div>
        <div class="cell-kategori">
            <span class="icon-wrapper bg-blue-light"><i class="fa-solid fa-receipt"></i></span>
            Belanja Bahan Baku
        </div>
        <div class="cell-tanggal">
            18 Oktober 2024
            <small>10:15</small>
        </div>
        <div class="cell-deskripsi">Pembelian bahan baku untuk produksi</div>
        <div class="cell-nominal text-red">-Rp850.000</div>
    </div>

    <div class="transaction-row">
        <div class="cell-check"><input type="checkbox" class="check-item"></div>
        <div class="cell-kategori">
            <span class="icon-wrapper bg-orange-light"><i class="fa-solid fa-users"></i></span>
            Gaji Karyawan
        </div>
        <div class="cell-tanggal">
            17 Oktober 2024
            <small>09:00</small>
        </div>
        <div class="cell-deskripsi">Pembayaran gaji karyawan bulan Oktober</div>
        <div class="cell-nominal text-red">-Rp3.200.000</div>
    </div>

    <div class="transaction-row">
        <div class="cell-check"><input type="checkbox" class="check-item"></div>
        <div class="cell-kategori">
            <span class="icon-wrapper bg-purple-light"><i class="fa-solid fa-building"></i></span>
            Sewa Ruko
        </div>
        <div class="cell-tanggal">
            16 Oktober 2024
            <small>11:20</small>
        </div>
        <div class="cell-deskripsi">Pembayaran sewa ruko bulan Oktober</div>
        <div class="cell-nominal text-red">-Rp1.500.000</div>
    </div>

    <div class="transaction-footer">
        <span>Total Pemasukan: <strong class="text-green">Rp 7.550.000</strong></span>
        <span>Total Pengeluaran: <strong class="text-red">Rp 5.950.000</strong></span>
        
        @php
            $laba = 1600000; // Ini hanya contoh, ganti dengan data dinamis
            $status = ($laba >= 0) ? 'profit' : 'loss';
        @endphp
        
        <span>Laba: 
            <span class="laba-badge {{ $status }}">
                {{ ($status == 'loss' ? '-' : '') }}Rp {{ number_format(abs($laba), 0, ',', '.') }}
            </span>
        </span>
    </div>
</div>

<div class="modal-overlay-report" id="reportModalOverlay">
    <div class="modal-box-report">
        
        <div class="modal-header-report">
            <div class="modal-title-report">
                <h2>Preview Laporan Keuangan</h2>
                <div class="report-options">
                    <label class="checkbox-report-option">
                        <input type="checkbox" checked> Sertakan ringkasan keuangan
                    </label>
                    <label class="checkbox-report-option">
                        <input type="checkbox" checked> Sertakan grafik kas
                    </label>
                    <label class="checkbox-report-option">
                        <input type="checkbox" checked> Sertakan rincian transaksi
                    </label>
                </div>
            </div>
            <button class="modal-close-btn-report" id="closeReportModal">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body-report">
            
            {{-- Bagian Konten Laporan --}}
            <div class="report-content-wrapper">
                
                <div class="report-header-section">
                    <h3>Laporan Buku Kas</h3>
                    <h4>Yantokopi</h4>
                    <p class="report-period">Periode: September 2025</p>
                </div>

                {{-- Ringkasan Keuangan --}}
                <div class="report-section">
                    <h3 class="section-title">Ringkasan keuangan</h3>
                    <div class="summary-cards-grid">
                        
                        <div class="summary-card">
                            <span class="summary-icon icon-wallet"><i class="fa-solid fa-wallet"></i></span>
                            <div class="summary-details">
                                <p>SALDO</p>
                                <h3>Rp 3.103.000</h3>
                            </div>
                            <span class="summary-trend trend-neutral"></span>
                        </div>
                        
                        <div class="summary-card">
                            <span class="summary-icon icon-inflow"><i class="fa-solid fa-arrow-right"></i></span>
                            <div class="summary-details">
                                <p>Pemasukan</p>
                                <h3>Rp 4.201.000</h3>
                            </div>
                            <span class="summary-trend trend-up">+12.24% <i class="fa-solid fa-arrow-up"></i></span>
                        </div>
                        
                        <div class="summary-card">
                            <span class="summary-icon icon-outflow"><i class="fa-solid fa-arrow-left"></i></span>
                            <div class="summary-details">
                                <p>Pengeluaran</p>
                                <h3>Rp 5.304.000</h3>
                            </div>
                            <span class="summary-trend trend-down">-6.56% <i class="fa-solid fa-arrow-down"></i></span>
                        </div>
                        
                        <div class="summary-card">
                            <span class="summary-icon icon-profit"><i class="fa-solid fa-sack-dollar"></i></span>
                            <div class="summary-details">
                                <p>Laba</p>
                                <h3>Rp 1.103.000</h3>
                            </div>
                            <span class="summary-trend trend-up">+8.74% <i class="fa-solid fa-arrow-up"></i></span>
                        </div>
                        
                    </div>
                </div>

                {{-- Grafik Kas --}}
                <div class="report-section">
                    <h3 class="section-title">Grafik Kas</h3>
                    <div class="chart-grid">
                        
                        <div class="chart-card">
                            <div class="chart-header">
                                <h4>Grafik Kas</h4>
                                <div class="chart-options">
                                    <select class="dropdown-simple">
                                        <option>Oktober - 2024</option>
                                    </select>
                                    <button class="chart-toggle-btn active">Pemasukan</button>
                                    <button class="chart-toggle-btn">Pengeluaran</button>
                                </div>
                            </div>
                            <div class="chart-placeholder">
                                {{-- Placeholder untuk Chart --}}
                                <img src="https://via.placeholder.com/400x200?text=Placeholder+Chart+1" alt="Chart Placeholder" style="width:100%; height: auto; display: block;">
                            </div>
                        </div>

                        <div class="chart-card">
                            <div class="chart-header">
                                <h4>Persentase Kas</h4>
                                <div class="chart-options">
                                    <select class="dropdown-simple">
                                        <option>Pengeluaran</option>
                                    </select>
                                </div>
                            </div>
                            <div class="chart-placeholder">
                                {{-- Placeholder untuk Chart --}}
                                <img src="https://via.placeholder.com/250x200?text=Placeholder+Chart+2" alt="Chart Placeholder" style="width:100%; height: auto; display: block;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rincian Transaksi --}}
                <div class="report-section">
                    <h3 class="section-title">Rincian Transaksi</h3>
                    <div class="transaction-table-card"> {{-- Menggunakan kembali style transaksi --}}
                        <div class="transaction-row header">
                            <div class="cell-check"><input type="checkbox"></div>
                            <div class="cell-kategori">Kategori</div>
                            <div class="cell-tanggal">Tanggal & Waktu</div>
                            <div class="cell-deskripsi">Deskripsi</div>
                            <div class="cell-nominal">Nominal</div>
                        </div>
                        
                        {{-- Contoh data transaksi, sesuaikan dengan loop nyata Anda --}}
                        <div class="transaction-row">
                            <div class="cell-check"><input type="checkbox"></div>
                            <div class="cell-kategori"><span class="icon-wrapper bg-green-light"><i class="fa-solid fa-shopping-cart"></i></span> Penjualan Produk</div>
                            <div class="cell-tanggal">18 Oktober 2024<small>14:30</small></div>
                            <div class="cell-deskripsi">Penjualan Produk A hari Sabtu</div>
                            <div class="cell-nominal text-green">+Rp1.500.000</div>
                        </div>
                        <div class="transaction-row">
                            <div class="cell-check"><input type="checkbox"></div>
                            <div class="cell-kategori"><span class="icon-wrapper bg-blue-light"><i class="fa-solid fa-book"></i></span> Belanja Bahan Baku</div>
                            <div class="cell-tanggal">18 Oktober 2024<small>10:15</small></div>
                            <div class="cell-deskripsi">Pembelian bahan baku untuk produksi</div>
                            <div class="cell-nominal text-red">-Rp850.000</div>
                        </div>
                        <div class="transaction-row">
                            <div class="cell-check"><input type="checkbox"></div>
                            <div class="cell-kategori"><span class="icon-wrapper bg-orange-light"><i class="fa-solid fa-users"></i></span> Gaji Karyawan</div>
                            <div class="cell-tanggal">17 Oktober 2024<small>09:00</small></div>
                            <div class="cell-deskripsi">Pembayaran gaji karyawan bulan Oktober</div>
                            <div class="cell-nominal text-red">-Rp3.200.000</div>
                        </div>
                        <div class="transaction-row">
                            <div class="cell-check"><input type="checkbox"></div>
                            <div class="cell-kategori"><span class="icon-wrapper bg-blue-light"><i class="fa-solid fa-home"></i></span> Sewa Ruko</div>
                            <div class="cell-tanggal">16 Oktober 2024<small>11:20</small></div>
                            <div class="cell-deskripsi">Pembayaran sewa ruko bulan Oktober</div>
                            <div class="cell-nominal text-red">-Rp1.500.000</div>
                        </div>
                        <div class="transaction-row">
                            <div class="cell-check"><input type="checkbox"></div>
                            <div class="cell-kategori"><span class="icon-wrapper bg-purple-light"><i class="fa-solid fa-truck"></i></span> Transportasi</div>
                            <div class="cell-tanggal">15 Oktober 2024<small>08:45</small></div>
                            <div class="cell-deskripsi">Biaya pengiriman barang ke pelanggan</div>
                            <div class="cell-nominal text-red">-Rp250.000</div>
                        </div>
                    </div>
                </div>
            </div> {{-- End report-content-wrapper --}}
            
        </div>
        
        <div class="modal-footer-report">
            <button class="btn btn-gradient">Download sebagai PDF</button>
            <button class="btn btn-secondary-modal" id="closeReportModalFooter">Tutup</button>
        </div>
        
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // 1. Ambil semua elemen
        const checkAll = document.getElementById('check-all');
        const checkItems = document.querySelectorAll('.check-item');
        const totalItems = checkItems.length;

        // --- LOGIKA UTAMA ---

        // 2. Saat "Select All" (header) diklik
        checkAll.addEventListener('click', function() {
            // Ceklis semua item di baris sesuai dengan status "Select All"
            checkItems.forEach(item => {
                item.checked = this.checked;
            });
        });

        // 3. Saat salah satu item (baris) diklik
        checkItems.forEach(item => {
            item.addEventListener('click', function() {
                // Hitung berapa banyak item yang ter-ceklis
                const checkedCount = document.querySelectorAll('.check-item:checked').length;

                // Jika semua ter-ceklis, ceklis juga "Select All"
                if (checkedCount === totalItems) {
                    checkAll.checked = true;
                    checkAll.indeterminate = false; // Hapus status "setengah"
                } 
                // Jika ada beberapa (tapi tidak semua) yang ter-ceklis
                else if (checkedCount > 0) {
                    checkAll.checked = false; // Jangan ceklis penuh
                    checkAll.indeterminate = true; // Buat jadi "setengah" (strip)
                } 
                // Jika tidak ada yang ter-ceklis
                else {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                }
            });
        });

    });

    document.addEventListener('DOMContentLoaded', function() {
        const reportModalOverlay = document.getElementById('reportModalOverlay');
        const openReportModalBtn = document.querySelector('.bukukas-header .btn-gradient'); // Tombol "Cetak Buku Kas"
        const closeReportModalBtn = document.getElementById('closeReportModal'); // Tombol X
        const closeReportModalFooterBtn = document.getElementById('closeReportModalFooter'); // Tombol Tutup di footer

        function openReportModal() {
            if (reportModalOverlay) {
                reportModalOverlay.style.display = 'flex';
                // Jika ingin body tidak bisa di-scroll saat modal terbuka
                document.body.style.overflow = 'hidden'; 
            }
        }

        function closeReportModal() {
            if (reportModalOverlay) {
                reportModalOverlay.style.display = 'none';
                document.body.style.overflow = ''; // Kembalikan scroll body
            }
        }

        if (openReportModalBtn) {
            openReportModalBtn.addEventListener('click', openReportModal);
        }
        if (closeReportModalBtn) {
            closeReportModalBtn.addEventListener('click', closeReportModal);
        }
        if (closeReportModalFooterBtn) {
            closeReportModalFooterBtn.addEventListener('click', closeReportModal);
        }

        // Tutup modal jika klik di luar modal box (tapi di overlay)
        if (reportModalOverlay) {
            reportModalOverlay.addEventListener('click', function(event) {
                if (event.target === reportModalOverlay) {
                    closeReportModal();
                }
            });
        }
    });
</script>
@endsection