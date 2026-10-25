<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi Bee Kopi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #d97706; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .summary-box { border: 1px solid #d97706; background-color: #fffbeb; padding: 10px; margin-top: 15px; border-radius: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>BEE KOPI - LAPORAN LABA & RUGI</h2>
        <p style="margin: 5px 0 0 0;">Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggal_selesai)->format('d M Y') }}</p>
    </div>

    <h3>1. Ringkasan Keuangan</h3>
    <table class="table">
        <tr>
            <th>Keterangan</th>
            <th class="text-right">Nominal</th>
        </tr>
        <tr>
            <td>Total Omzet Penjualan (Kotor)</td>
            <td class="text-right font-bold" style="color: #059669;">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>- Omzet Tunai</td>
            <td class="text-right">Rp {{ number_format($omzetTunai, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>- Omzet QRIS</td>
            <td class="text-right">Rp {{ number_format($omzetQris, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pengeluaran Belanja Bahan Baku (HPP)</td>
            <td class="text-right font-bold" style="color: #dc2626;">Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pengeluaran Operasional Lapak (Approved)</td>
            <td class="text-right font-bold" style="color: #d97706;">Rp {{ number_format($totalBiayaOperasional, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>- Uang Makan Staff</td>
            <td class="text-right">Rp {{ number_format($totalUangMakan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>- Pengeluaran Operasional Lain</td>
            <td class="text-right">Rp {{ number_format($totalPengeluaranLain, 0, ',', '.') }}</td>
        </tr>
        <tr style="background-color: #fef3c7;">
            <td class="font-bold">TOTAL ESTIMASI LABA BERSIH</td>
            <td class="text-right font-bold" style="font-size: 14px; color: #b45309;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
        </tr>
    </table>

    <h3 style="margin-top: 25px;">2. Kontribusi Penjualan Per Lapak</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Lapak Cabang</th>
                <th class="text-right">Total Transaksi</th>
                <th class="text-right">Total Omzet</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($performaLapaks as $lapak)
                <tr>
                    <td>{{ $lapak->nama_lapak }}</td>
                    <td class="text-right">{{ number_format($lapak->total_transaksi, 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($lapak->total_omzet ?? 0, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>