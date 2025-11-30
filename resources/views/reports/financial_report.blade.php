<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Buku Kas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 900px;
            margin: 0 auto;
        }
        
        /* Header Section */
        .header-section {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 20px;
        }
        
        .header-section h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #1a1a1a;
        }
        
        .header-section h2 {
            font-size: 18px;
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        
        .header-section p {
            font-size: 13px;
            color: #999;
            margin: 0;
        }
        
        /* Ringkasan Keuangan Section */
        .ringkasan-section {
            margin-bottom: 35px;
        }
        
        .ringkasan-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2385F2;
        }
        
        .ringkasan-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .ringkasan-card {
            padding: 20px;
            border-radius: 12px;
            text-align: left;
            page-break-inside: avoid;
        }
        
        .ringkasan-card.saldo {
            background: linear-gradient(135deg, #2385F2 0%, #3B9EFF 100%);
            color: white;
            grid-column: 1 / 2;
            grid-row: 1 / 3;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .ringkasan-card.pemasukan {
            background-color: #e8f5e9;
            border-left: 5px solid #4caf50;
        }
        
        .ringkasan-card.pengeluaran {
            background-color: #fce4ec;
            border-left: 5px solid #e91e63;
        }
        
        .ringkasan-card.laba {
            background-color: #fff3e0;
            border-left: 5px solid #ff9800;
        }
        
        .ringkasan-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            opacity: 0.85;
        }
        
        .ringkasan-value {
            font-size: 22px;
            font-weight: 700;
        }
        
        .ringkasan-card.pemasukan .ringkasan-label { color: #2e7d32; }
        .ringkasan-card.pemasukan .ringkasan-value { color: #1b5e20; }
        
        .ringkasan-card.pengeluaran .ringkasan-label { color: #c2185b; }
        .ringkasan-card.pengeluaran .ringkasan-value { color: #880e4f; }
        
        .ringkasan-card.laba .ringkasan-label { color: #e65100; }
        .ringkasan-card.laba .ringkasan-value { color: #bf360c; }
        
        .grafik-section {
            margin-bottom: 35px;
        }
        
        .grafik-title {
            font-size: 16px;
            font-weight: 700;
            color: white;
            background-color: #2385F2;
            margin: 0 0 0 0;
            padding: 12px 15px;
            border-radius: 8px 8px 0 0;
        }
        
        .grafik-content {
            background-color: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            padding: 15px;
            text-align: center;
            min-height: auto;
            display: block;
        }
        
        .grafik-content p {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        
        
        .transaksi-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2385F2;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        thead {
            background-color: #f0f0f0;
        }
        
        th {
            padding: 12px 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: #333;
            border-bottom: 2px solid #ddd;
        }
        
        td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 12px;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-green {
            color: #059669;
            font-weight: 600;
        }
        
        .text-red {
            color: #dc2626;
            font-weight: 600;
        }
        
        .text-blue {
            color: #0066cc;
            font-weight: 600;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        
        /* Print specific styles */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header-section">
            <h1>Laporan Buku Kas</h1>
            <h2>{{ $businessName }}</h2>
            <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
        </div>
        
        <!-- Ringkasan Keuangan -->
        <div class="ringkasan-section">
            <div class="ringkasan-title">Ringkasan Keuangan</div>
            <div class="ringkasan-grid">
                <div class="ringkasan-card saldo">
                    <div class="ringkasan-label">Saldo</div>
                    <div class="ringkasan-value">Rp {{ number_format($saldo ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="ringkasan-card pemasukan">
                    <div class="ringkasan-label">Pemasukan</div>
                    <div class="ringkasan-value">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
                </div>
                <div class="ringkasan-card pengeluaran">
                    <div class="ringkasan-label">Pengeluaran</div>
                    <div class="ringkasan-value">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
                </div>
                <div class="ringkasan-card laba">
                    <div class="ringkasan-label">Laba</div>
                    <div class="ringkasan-value {{ $laba >= 0 ? 'text-blue' : 'text-red' }}">Rp {{ number_format($laba, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        
        <!-- Grafik Kas Section -->
        <div class="grafik-section">
            <div class="grafik-title">Grafik Kas - Breakdown Kategori</div>
            <div class="grafik-content">
                @if(!empty($categoryBreakdown))
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                        <thead>
                            <tr style="background-color: #f5f5f5; border-bottom: 2px solid #2385F2;">
                                <th style="padding: 8px; text-align: left; border-right: 1px solid #ddd;">Kategori</th>
                                <th style="padding: 8px; text-align: center; border-right: 1px solid #ddd; width: 80px;">Tipe</th>
                                <th style="padding: 8px; text-align: center; border-right: 1px solid #ddd; width: 70px;">Jumlah</th>
                                <th style="padding: 8px; text-align: right; width: 120px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryBreakdown as $category)
                                <tr style="border-bottom: 1px solid #e0e0e0;">
                                    <td style="padding: 8px; border-right: 1px solid #ddd;">{{ $category['nama'] }}</td>
                                    <td style="padding: 8px; text-align: center; border-right: 1px solid #ddd;">
                                        <span style="font-size: 11px; font-weight: 600; padding: 3px 6px; border-radius: 3px; {{ $category['tipe'] === 'pemasukan' ? 'background-color: #d4edda; color: #155724;' : 'background-color: #f8d7da; color: #721c24;' }}">
                                            {{ ucfirst($category['tipe']) }}
                                        </span>
                                    </td>
                                    <td style="padding: 8px; text-align: center; border-right: 1px solid #ddd;">{{ $category['count'] }}</td>
                                    <td style="padding: 8px; text-align: right; {{ $category['tipe'] === 'pemasukan' ? 'color: #059669; font-weight: 600;' : 'color: #dc2626; font-weight: 600;' }}">
                                        {{ $category['tipe'] === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($category['total'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p style="color: #666; margin: 0;">Tidak ada data kategori untuk periode ini.</p>
                @endif
            </div>
        </div>
        
            <div class="transaksi-title">Rincian Transaksi</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">Tanggal & Waktu</th>
                        <th style="width: 15%;">Kategori</th>
                        <th style="width: 40%;">Deskripsi</th>
                        <th style="width: 25%; text-align: right;">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $index => $transaction)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $transaction->tanggal_transaksi->format('d-m-Y H:i') }}</td>
                            <td>{{ $transaction->category->nama_kategori }}</td>
                            <td>{{ $transaction->catatan ?? '-' }}</td>
                            <td style="text-align: right;">
                                @if ($transaction->category->tipe == 'pemasukan')
                                    <span class="text-green">+ Rp {{ number_format($transaction->jumlah, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-red">- Rp {{ number_format($transaction->jumlah, 0, ',', '.') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">Tidak ada data transaksi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Laporan ini dibuat secara otomatis oleh UEMKASolve</p>
        </div>
    </div>
</body>
</html>