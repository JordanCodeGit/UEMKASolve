<!DOCTYPE html>
<html>
<head>
    {{-- // Bagian Head & Style PDF --}}
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Keuangan</title>
    <style>
        /* --- RESET & BASE --- */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #334155; /* Slate-700 */
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        h1, h2, h3, p { margin: 0; padding: 0; }

        /* --- UTILS --- */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-green { color: #10b981; }
        .text-red { color: #ef4444; }
        .font-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }

        /* --- HEADER --- */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
        }
        .header h1 { font-size: 20px; color: #1e293b; margin-bottom: 5px; }
        .header h2 { font-size: 16px; color: #64748b; font-weight: normal; margin-bottom: 5px; }
        .header p { font-size: 12px; color: #94a3b8; }

        /* --- SUMMARY CARDS (LAYOUT TABLE) --- */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0; /* Jarak antar kartu */
            margin-bottom: 30px;
            margin-left: -10px; /* Kompensasi spacing */
            margin-right: -10px;
        }
        .card {
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            width: 25%; /* 4 kartu = 25% */
            vertical-align: top;
        }
        .card-blue {
            background-color: #3b82f6; /* Biru terang */
            color: #ffffff;
            border: none;
        }
        .card-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            opacity: 0.9;
        }
        .card-value {
            font-size: 16px;
            font-weight: bold;
        }
        .card-blue .card-title, .card-blue .card-value { color: #ffffff; }

        /* --- CHARTS LAYOUT --- */
        .charts-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .chart-cell {
            width: 50%;
            vertical-align: top;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
        }
        .chart-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1e293b;
        }
        .chart-img {
            width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: contain;
        }

        /* --- TRANSACTION TABLE --- */
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #1e293b;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #e2e8f0;
        }
        .table-data th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-data td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            font-size: 12px;
        }
        .table-data tr:last-child td { border-bottom: none; }

        .cat-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            background: #f1f5f9;
            font-size: 10px;
            font-weight: bold;
            color: #475569;
        }
    </style>
</head>
<body>

    {{-- // Bagian Header Laporan --}}
    <div class="header">
        <h1>Laporan Keuangan</h1>
        <h2>{{ $company['name'] }}</h2>
        <p>Dicetak pada: {{ $date }}</p>
    </div>

    @if($sections['ringkasan'] ?? false)
    {{-- // Bagian Ringkasan Keuangan --}}
    <div class="mb-20">
        <h3 class="section-title">Ringkasan Keuangan</h3>
        <table class="summary-table">
            <tr>
                <td class="card card-blue">
                    <div class="card-title">Saldo Akhir</div>
                    <div class="card-value">Rp {{ number_format($summary['saldo_real'], 0, ',', '.') }}</div>
                </td>

                <td class="card">
                    <div class="card-title">Pemasukan</div>
                    <div class="card-value text-green">
                        Rp {{ number_format($summary['total_pemasukan'], 0, ',', '.') }}
                    </div>
                </td>

                <td class="card">
                    <div class="card-title">Pengeluaran</div>
                    <div class="card-value text-red">
                        Rp {{ number_format($summary['total_pengeluaran'], 0, ',', '.') }}
                    </div>
                </td>

                <td class="card">
                    <div class="card-title">Laba Bersih</div>
                    <div class="card-value {{ $summary['laba'] >= 0 ? 'text-green' : 'text-red' }}">
                        Rp {{ number_format($summary['laba'], 0, ',', '.') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    @if($sections['grafik'] ?? false)
    {{-- // Bagian Grafik Kas & Kategori --}}
    <table class="charts-table">
        <tr>
            <td class="chart-cell" style="width: 58%; padding-right: 10px;">
                <div class="chart-title">Grafik Kas</div>
                @if($lineChartBase64)
                    <img src="{{ $lineChartBase64 }}" class="chart-img">
                @else
                    <p style="color: #ccc; text-align: center; padding: 20px;">Grafik tidak tersedia</p>
                @endif
            </td>

            <td class="chart-cell" style="width: 38%; padding-left: 10px;">
                <div class="chart-title">Persentase Kategori</div>
                @if($doughnutChartBase64)
                    <img src="{{ $doughnutChartBase64 }}" class="chart-img">
                @else
                    <p style="color: #ccc; text-align: center; padding: 20px;">Data tidak cukup</p>
                @endif
            </td>
        </tr>
    </table>
    @endif

    @if($sections['rincian'] ?? false)
    {{-- // Bagian Rincian Transaksi --}}
    <div class="mt-20">
        <h3 class="section-title">Rincian Transaksi Terakhir</h3>
        <table class="table-data">
            <thead>
                <tr>
                    <th width="20%">Tanggal</th>
                    <th width="25%">Kategori</th>
                    <th width="30%">Deskripsi</th>
                    <th width="25%" class="text-right">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    @php
                        $isMasuk = optional($tx->category)->tipe === 'pemasukan';
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight: bold;">{{ $tx->tanggal_transaksi->format('d M Y') }}</div>
                            <div style="font-size: 10px; color: #94a3b8;">{{ $tx->tanggal_transaksi->format('H:i') }}</div>
                        </td>
                        <td>
                            <span class="cat-badge">
                                {{ optional($tx->category)->nama_kategori ?? 'Umum' }}
                            </span>
                        </td>
                        <td>{{ $tx->catatan ?? '-' }}</td>
                        <td class="text-right font-bold {{ $isMasuk ? 'text-green' : 'text-red' }}">
                            {{ $isMasuk ? '+' : '-' }} Rp {{ number_format($tx->jumlah, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center" style="padding: 20px;">Belum ada data transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

</body>
</html>
