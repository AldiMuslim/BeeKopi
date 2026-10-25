<?php

namespace App\Livewire\Admin;

use App\Models\Lapak;
use App\Models\LaporanLapak;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardAdmin extends Component
{
    public $filterRange = 30; // Default 30 hari terakhir

    public function render()
    {
        $startDate = now()->subDays($this->filterRange)->startOfDay();

        // 1. Total Penjualan Harian
        $penjualanHarian = Transaksi::select(
            DB::raw('DATE(created_at) as tanggal'),
            DB::raw('SUM(total_harga) as total_omzet')
        )
            ->where('created_at', '>=', $startDate)
            ->where('status', 'selesai')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get();

        // 2. Penjualan Per Lapak
        $penjualanPerLapak = Lapak::withSum(['transaksis' => function ($query) use ($startDate) {
            $query->where('created_at', '>=', $startDate)->where('status', 'selesai');
        }], 'total_harga')
            ->get();

        // 3. Pengeluaran Harian
        $pengeluaranHarian = LaporanLapak::select(
            'tanggal',
            DB::raw('SUM(uang_makan + pengeluaran_lain) as total_pengeluaran')
        )
            ->where('tanggal', '>=', $startDate->toDateString())
            ->where('status', 'approved')
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get();

        // 4. Trend Penjualan Per Produk (Top Product Sales)
        $trendProduk = TransaksiDetail::select(
            'produks.nama_produk',
            DB::raw('SUM(transaksi_details.jumlah) as total_terjual')
        )
            ->join('produks', 'transaksi_details.produk_id', '=', 'produks.id')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->where('transaksis.created_at', '>=', $startDate)
            ->where('transaksis.status', 'selesai')
            ->groupBy('produks.nama_produk')
            ->orderBy('total_terjual', 'DESC')
            ->take(10)
            ->get();

        return view('livewire.admin.dashboard-admin', [
            'chartOmzetLabels' => $penjualanHarian->pluck('tanggal')->toArray(),
            'chartOmzetValues' => $penjualanHarian->pluck('total_omzet')->toArray(),
            'chartLapakLabels' => $penjualanPerLapak->pluck('nama_lapak')->toArray(),
            'chartLapakValues' => $penjualanPerLapak->pluck('transaksis_sum_total_harga')->map(fn($v) => $v ?? 0)->toArray(),
            'chartPengeluaranLabels' => $pengeluaranHarian->pluck('tanggal')->toArray(),
            'chartPengeluaranValues' => $pengeluaranHarian->pluck('total_pengeluaran')->toArray(),
            'chartProdukLabels' => $trendProduk->pluck('nama_produk')->toArray(),
            'chartProdukValues' => $trendProduk->pluck('total_terjual')->toArray(),
            'totalOmzet' => $penjualanHarian->sum('total_omzet'),
            'totalPengeluaran' => $pengeluaranHarian->sum('total_pengeluaran'),
        ])->layout('layouts.app');
    }
}
