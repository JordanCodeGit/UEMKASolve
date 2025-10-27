@extends('layouts.app')

{{-- Judul di header top-bar akan "Dashboard" --}}
@section('title', 'Dashboard')

@section('content')

<div class="summary-grid">

    <div class="summary-card saldo">
        <div class="icon-box">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div class="card-content">
            <p>SALDO</p>
            <h3>Rp 3.103.000</h3>
        </div>
    </div>

    <div class="summary-card">
        <div class="icon-box">
            <i class="fa-solid fa-file-arrow-down"></i>
        </div>
        <div class="card-content">
            <p>PEMASUKAN</p>
            <h3>Rp 4.201.000</h3>
        </div>
        <div class="card-footer text-green">
            <i class="fa-solid fa-caret-up"></i> +10,74%
        </div>
    </div>

    <div class="summary-card">
        <div class="icon-box">
            <i class="fa-solid fa-file-arrow-up"></i>
        </div>
        <div class="card-content">
            <p>PENGELUARAN</p>
            <h3>Rp 5.304.000</h3>
        </div>
        <div class="card-footer text-red">
            <i class="fa-solid fa-caret-down"></i> -30,74%
        </div>
    </div>
    
    <div class="summary-card">
        <div class="icon-box">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="card-content">
            <p>LABA</p>
            <h3>Rp 1.103.000</h3>
        </div>
        <div class="card-footer text-green">
            <i class="fa-solid fa-arrow-trend-up"></i> +10,74%
        </div>
    </div>
</div>
<div class="chart-grid">
    
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Grafik Kas</h3>
            <div class="dropdown-with-icon">
                <i class="fa-solid fa-calendar-days"></i>
                <select class="dropdown-simple-dashboard">
                    <option>Oktober - 2024</option>
                    <option>September - 2024</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-legend">
                <span><i class="fa-solid fa-circle text-blue"></i> Pemasukan</span>
                <span><i class="fa-solid fa-circle text-red"></i> Pengeluaran</span>
            </div>
            <div class="chart-placeholder">
                <p>(Placeholder untuk Line Chart)</p>
            </div>
        </div>
    </div>
    
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Persentase Kas</h3>
            <select class="dropdown-simple">
                <option>Pengeluaran</option>
                <option>Pemasukan</option>
            </select>
        </div>
        <div class="card-body">
             <div class="doughnut-placeholder">
                <p>(Doughnut Chart)</p>
            </div>
            <ul class="doughnut-legend">
                <li><i class="fa-solid fa-circle" style="color: #36D1DC;"></i> Belanja bahan baku</li>
                <li><i class="fa-solid fa-circle" style="color: #0072ff;"></i> Gaji Karyawan</li>
                <li><i class="fa-solid fa-circle" style="color: #888;"></i> Sewa Ruko</li>
            </ul>
        </div>
    </div>
</div>
<div class="content-card">
    <div class="card-header">
        <h3 class="card-title">Transaksi Terakhir</h3>
    </div>
    <div class="card-body">
        <ul class="transaction-list">
            
            <li class="transaction-item">
                <div class="icon-circle"><i class="fa-solid fa-arrow-down"></i></div>
                <div class="transaction-details">
                    <strong>Penjualan Produk</strong>
                    <small>18 Oktober 2024</small>
                </div>
                <div class="transaction-note">
                    Penjualan Produk A hari Sabtu
                </div>
                <div class="transaction-amount text-green">
                    +Rp1.500.000
                </div>
            </li>
            
            <li class="transaction-item">
                <div class="icon-circle"><i class="fa-solid fa-arrow-down"></i></div>
                <div class="transaction-details">
                    <strong>Penjualan Produk</strong>
                    <small>18 Oktober 2024</small>
                </div>
                <div class="transaction-note">
                    Penjualan Produk A hari Sabtu
                </div>
                <div class="transaction-amount text-green">
                    +Rp1.500.000
                </div>
            </li>
            
            <li class="transaction-item">
                <div class="icon-circle"><i class="fa-solid fa-arrow-down"></i></div>
                <div class="transaction-details">
                    <strong>Penjualan Produk</strong>
                    <small>18 Oktober 2024</small>
                </div>
                <div class="transaction-note">
                    Penjualan Produk A hari Sabtu
                </div>
                <div class="transaction-amount text-green">
                    +Rp1.500.000
                </div>
            </li>
            
        </ul>
    </div>
</div>
@endsection