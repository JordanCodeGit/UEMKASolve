<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #007BFF;
        }

        .header h1 {
            font-size: 24px;
            color: #007BFF;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 13px;
            color: #666;
        }

        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: white;
            background-color: #007BFF;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        /* Ringkasan Keuangan Styles */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .summary-card {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background-color: #f9f9f9;
        }

        .summary-card-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-card-value {
            font-size: 18px;
            font-weight: bold;
            color: #007BFF;
        }

        .summary-card.income .summary-card-value {
            color: #4caf50;
        }

        .summary-card.expense .summary-card-value {
            color: #f44336;
        }

        .summary-card.profit .summary-card-value {
            color: #2196F3;
        }

        /* Tabel Rincian */
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .table th {
            background-color: #007BFF;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        .table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company['name'] ?? 'Laporan Keuangan' }}</h1>
        <p>Laporan Keuangan - {{ $date ?? date('d M Y') }}</p>
    </div>

    <!-- RINGKASAN KEUANGAN -->
    @if($sections['ringkasan'] ?? false)
        <div class="section">
            <div class="section-title">Ringkasan Keuangan</div>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-card-label">Saldo Total</div>
                    <div class="summary-card-value">Rp {{ number_format($summary['saldo_real'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="summary-card income">
                    <div class="summary-card-label">Total Pemasukan</div>
                    <div class="summary-card-value">Rp {{ number_format($summary['total_pemasukan'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="summary-card expense">
                    <div class="summary-card-label">Total Pengeluaran</div>
                    <div class="summary-card-value">Rp {{ number_format($summary['total_pengeluaran'] ?? 0, 0, ',', '.') }}</div>
                </div>
                <div class="summary-card profit">
                    <div class="summary-card-label">Laba / Rugi</div>
                    <div class="summary-card-value">Rp {{ number_format($summary['laba'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- GRAFIK KAS -->
    @if($sections['grafik'] ?? false)
        <div class="section">
            <div class="section-title">Grafik Kas</div>
            <p style="padding: 20px; text-align: center; color: #666;">Grafik akan ditampilkan dalam versi interaktif di dashboard</p>
        </div>
    @endif

    <!-- RINCIAN TRANSAKSI -->
    @if($sections['rincian'] ?? false)
        <div class="section">
            <div class="section-title">Rincian Transaksi</div>
            @if(count($transactions ?? []) > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 25%;">Kategori</th>
                            <th style="width: 40%;">Catatan</th>
                            <th style="width: 20%;" class="text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $tx)
                            @php
                                // Handle both object and array formats
                                $tanggal = $tx->tanggal_transaksi ?? ($tx['tanggal_transaksi'] ?? '');
                                $kategoriNama = $tx->category?->nama_kategori ?? ($tx['category']['nama_kategori'] ?? 'Tidak Diketahui');
                                $kategoriTipe = $tx->category?->tipe ?? ($tx['category']['tipe'] ?? '');
                                $catatan = $tx->catatan ?? ($tx['catatan'] ?? '-');
                                $jumlah = $tx->jumlah ?? ($tx['jumlah'] ?? 0);
                                
                                // Parse tanggal
                                try {
                                    if (is_object($tanggal)) {
                                        $tanggalFormatted = $tanggal->format('d/m/Y');
                                    } else {
                                        $tanggalFormatted = \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
                                    }
                                } catch (\Exception $e) {
                                    $tanggalFormatted = $tanggal;
                                }
                            @endphp
                            <tr>
                                <td>{{ $tanggalFormatted }}</td>
                                <td>{{ $kategoriNama }} <span style="color: #999; font-size: 11px;">({{ ucfirst($kategoriTipe) }})</span></td>
                                <td>{{ substr($catatan, 0, 50) }}</td>
                                <td class="text-right">Rp {{ number_format($jumlah, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-data">Tidak ada transaksi</div>
            @endif
        </div>
    @endif

</body>
</html>
