<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Keuangan</title>
    <style>
        /* CSS Sederhana Khusus untuk PDF */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .header-section h1 {
            font-size: 24px;
            margin: 0;
        }
        .header-section h3 {
            font-size: 16px;
            font-weight: 500;
            margin: 5px 0;
        }
        .header-section p {
            font-size: 14px;
            margin: 0;
        }
        .summary-section {
            margin-bottom: 25px;
            width: 100%;
        }
        .summary-box {
            display: inline-block;
            width: 30%;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 5px;
            text-align: center;
        }
        .summary-box p {
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        .summary-box h2 {
            font-size: 18px;
            margin: 0;
        }
        .text-green { color: #00875a; }
        .text-red { color: #de350b; }
        .text-blue { color: #0d6efd; }
    </style>
</head>
<body>

    <div class="header-section">
        <h1>Laporan Buku Kas</h1>
        <h3>{{ $businessName }}</h3>
        <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>

    <div class="summary-section">
        <div class="summary-box">
            <p>Total Pemasukan</p>
            <h2 class="text-green">+ Rp {{ number_format($totalPemasukan, 2, ',', '.') }}</h2>
        </div>
        <div class="summary-box">
            <p>Total Pengeluaran</p>
            <h2 class="text-red">- Rp {{ number_format($totalPengeluaran, 2, ',', '.') }}</h2>
        </div>
        <div class="summary-box">
            <p>Keuntungan (Laba)</p>
            <h2 class="{{ $laba >= 0 ? 'text-blue' : 'text-red' }}">
                Rp {{ number_format($laba, 2, ',', '.') }}
            </h2>
        </div>
    </div>

    <h3>Rincian Transaksi</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Catatan</th>
                <th>Pemasukan (Rp)</th>
                <th>Pengeluaran (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $index => $transaction)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $transaction->tanggal_transaksi->format('d-m-Y') }}</td>
                    <td>{{ $transaction->category->nama_kategori }}</td>
                    <td>{{ $transaction->catatan }}</td>

                    @if ($transaction->category->tipe == 'pemasukan')
                        <td class="text-green">{{ number_format($transaction->jumlah, 2, ',', '.') }}</td>
                        <td>-</td>
                    @else
                        <td>-</td>
                        <td class="text-red">{{ number_format($transaction->jumlah, 2, ',', '.') }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
