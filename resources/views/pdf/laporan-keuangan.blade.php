<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Keuangan</title>
    <style>
        /* --- STYLE DASAR SAMA SEPERTI SEBELUMNYA --- */
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #334155; font-size: 12px; margin: 0; padding: 0; }
        h1, h2, h3, p { margin: 0; padding: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-green { color: #10b981; }
        .text-red { color: #ef4444; }
        .font-bold { font-weight: bold; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }

        /* HEADER */
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; }
        .header h1 { font-size: 20px; color: #1e293b; margin-bottom: 5px; }
        .header h2 { font-size: 16px; color: #64748b; font-weight: normal; }

        /* SUMMARY CARDS */
        .summary-table { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-bottom: 30px; margin-left: -10px; }
        .card { padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; background-color: #ffffff; width: 25%; vertical-align: top; }
        .card-blue { background-color: #3b82f6; color: #ffffff; border: none; }
        .card-title { font-size: 10px; text-transform: uppercase; margin-bottom: 8px; opacity: 0.9; }
        .card-value { font-size: 16px; font-weight: bold; }
        .card-blue .card-title, .card-blue .card-value { color: #ffffff; }

        /* CHARTS CONTAINER */
        .full-width-chart {
            width: 100%; margin-bottom: 30px; padding: 15px;
            border: 1px solid #e2e8f0; border-radius: 8px; background: #fff;
        }
        .chart-row { width: 100%; border-collapse: separate; border-spacing: 15px 0; margin-left: -15px; }
        .chart-col { width: 50%; vertical-align: top; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; }
        .chart-title { font-size: 14px; font-weight: bold; margin-bottom: 15px; color: #1e293b; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }

        /* LEGEND TABLE */
        .legend-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 10px; }
        .legend-table td { padding: 6px 0; border-bottom: 1px solid #f8fafc; }
        .legend-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
        .legend-percent { font-weight: bold; color: #334155; }
        .legend-nominal { color: #94a3b8; font-size: 9px; }

        /* TRANSACTION TABLE */
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #1e293b; }
        .table-data { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; }
        .table-data th { background-color: #f8fafc; color: #64748b; font-size: 11px; padding: 10px; text-align: left; }
        .table-data td { padding: 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        .cat-badge { padding: 3px 6px; border-radius: 4px; background: #f1f5f9; font-size: 9px; font-weight: bold; color: #475569; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Keuangan</h1>
        <h2>{{ $company['name'] }}</h2>
        <p>Dicetak pada: {{ $date }}</p>
    </div>

    @if($sections['ringkasan'] ?? false)
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
                    <div class="card-value text-green">Rp {{ number_format($summary['total_pemasukan'], 0, ',', '.') }}</div>
                </td>
                <td class="card">
                    <div class="card-title">Pengeluaran</div>
                    <div class="card-value text-red">Rp {{ number_format($summary['total_pengeluaran'], 0, ',', '.') }}</div>
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

    {{-- 1. Grafik Arus Kas (Full Width) --}}
    <div class="full-width-chart">
        <div class="chart-title">Grafik Arus Kas (Harian)</div>
        @if($lineChartBase64)
            <img src="{{ $lineChartBase64 }}" style="width: 100%; height: 200px; object-fit: contain;">
        @else
            <p class="text-center" style="color:#ccc; padding:30px;">Data tidak tersedia</p>
        @endif
    </div>

    {{-- 2. Grafik Pemasukan & Pengeluaran (Side by Side) --}}
    <table class="chart-row">
        <tr>
            <td class="chart-col">
                <div class="chart-title text-green">Persentase Pemasukan</div>
                @if($incomeChartBase64 && count($income_categories) > 0)
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 40%; text-align: center; padding-right: 10px;">
                                <img src="{{ $incomeChartBase64 }}" style="width: 100%; max-height: 120px; object-fit: contain;">
                            </td>
                            <td style="width: 60%; vertical-align: top;">
                                <table class="legend-table">
                                    @foreach($income_categories as $cat)
                                    @php
                                        $persen = ($cat['total'] / ($summary['total_pemasukan'] ?: 1)) * 100;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="legend-dot" style="background-color: {{ $cat['color'] }};"></span>
                                            {{ Str::limit($cat['nama_kategori'], 15) }}
                                        </td>
                                        <td class="text-right">
                                            <div class="legend-percent">{{ number_format($persen, 1) }}%</div>
                                            <div class="legend-nominal">Rp {{ number_format($cat['total'], 0, ',', '.') }}</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>
                @else
                    <p class="text-center" style="color:#ccc; padding: 20px;">Belum ada pemasukan.</p>
                @endif
            </td>

            <td class="chart-col">
                <div class="chart-title text-red">Persentase Pengeluaran</div>
                @if($expenseChartBase64 && count($expense_categories) > 0)
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 40%; text-align: center; padding-right: 10px;">
                                <img src="{{ $expenseChartBase64 }}" style="width: 100%; max-height: 120px; object-fit: contain;">
                            </td>
                            <td style="width: 60%; vertical-align: top;">
                                <table class="legend-table">
                                    @foreach($expense_categories as $cat)
                                    @php
                                        $persen = ($cat['total'] / ($summary['total_pengeluaran'] ?: 1)) * 100;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="legend-dot" style="background-color: {{ $cat['color'] }};"></span>
                                            {{ Str::limit($cat['nama_kategori'], 15) }}
                                        </td>
                                        <td class="text-right">
                                            <div class="legend-percent">{{ number_format($persen, 1) }}%</div>
                                            <div class="legend-nominal">Rp {{ number_format($cat['total'], 0, ',', '.') }}</div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>
                @else
                    <p class="text-center" style="color:#ccc; padding: 20px;">Belum ada pengeluaran.</p>
                @endif
            </td>
        </tr>
    </table>
    @endif

    @if($sections['rincian'] ?? false)
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
                    @php $isMasuk = optional($tx->category)->tipe === 'pemasukan'; @endphp
                    <tr>
                        <td>
                            <div class="font-bold">{{ $tx->tanggal_transaksi->format('d M Y') }}</div>
                            <div style="font-size: 10px; color: #94a3b8;">{{ $tx->tanggal_transaksi->format('H:i') }}</div>
                        </td>
                        <td><span class="cat-badge">{{ optional($tx->category)->nama_kategori ?? 'Umum' }}</span></td>
                        <td>{{ $tx->catatan ?? '-' }}</td>
                        <td class="text-right font-bold {{ $isMasuk ? 'text-green' : 'text-red' }}">
                            {{ $isMasuk ? '+' : '-' }} Rp {{ number_format($tx->jumlah, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

</body>
</html>
