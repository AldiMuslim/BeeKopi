<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\BahanBaku;
use App\Models\Lapak;
use App\Models\LaporanLapak;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class DashboardAdmin extends Component
{
    // Filter State
    public $filterRange = 30; // 1 (Hari Ini), 7, 30, 90, 365
    public $filterLapak = ''; // '' = Semua Lapak, atau ID Lapak spesifik

    public function resetFilter()
    {
        $this->filterRange = 30;
        $this->filterLapak = '';
    }

    public function render()
    {
        $days = (int) $this->filterRange;
        $startDate = $days === 1
            ? now()->startOfDay()
            : now()->subDays($days)->startOfDay();

        // Cek skema kolom satu kali di awal (mencegah query berulang di dalam loop)
        $hasLapakOnTransaksi = Schema::hasColumn('transaksis', 'lapak_id');
        $hasStokSekarang     = Schema::hasColumn('bahan_bakus', 'stok_sekarang');
        $hasStatusProduk     = Schema::hasColumn('produks', 'status');
        $hasStatusUser       = Schema::hasColumn('users', 'status');
        $hasTableLaporan     = Schema::hasTable('laporan_lapaks');
        $hasTableAudit       = Schema::hasTable('audit_logs');

        // Daftar semua lapak untuk dropdown filter & status operasional
        $allLapaks = Lapak::orderBy('nama_lapak', 'asc')->get();

        // 1. Query Penjualan Harian (Grafik 1 & Total Omzet)
        $queryTransaksi = Transaksi::query()
            ->where('created_at', '>=', $startDate)
            ->where('status', 'selesai')
            ->when($this->filterLapak && $hasLapakOnTransaksi, function ($q) {
                $q->where('lapak_id', $this->filterLapak);
            });

        $totalTransaksiCount = (clone $queryTransaksi)->count();

        $penjualanHarian = (clone $queryTransaksi)
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(total_harga) as total_omzet')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'ASC')
            ->get();

        $totalOmzet = (float) $penjualanHarian->sum('total_omzet');

        // 2. Performa Penjualan Per Armada Lapak (Grafik 2 & Peringkat Lapak)
        $penjualanPerLapak = Lapak::withCount(['users'])
            ->withCount([
                'transaksis as total_transaksi_selesai' => function ($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate)->where('status', 'selesai');
                }
            ])
            ->withSum([
                'transaksis' => function ($query) use ($startDate) {
                    $query->where('created_at', '>=', $startDate)->where('status', 'selesai');
                }
            ], 'total_harga')
            ->orderByDesc('transaksis_sum_total_harga')
            ->get();

        // 3. Pengeluaran Operasional Harian (Grafik 4 & Total Pengeluaran)
        $pengeluaranHarian = collect();
        $laporanPendingCount = 0;

        if ($hasTableLaporan) {
            $hasLapakOnLaporan = Schema::hasColumn('laporan_lapaks', 'lapak_id');

            $pengeluaranHarian = LaporanLapak::select(
                'tanggal',
                DB::raw('SUM(COALESCE(uang_makan, 0) + COALESCE(pengeluaran_lain, 0)) as total_pengeluaran')
            )
                ->where('tanggal', '>=', $startDate->toDateString())
                ->where('status', 'approved')
                ->when($this->filterLapak && $hasLapakOnLaporan, function ($q) {
                    $q->where('lapak_id', $this->filterLapak);
                })
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'ASC')
                ->get();

            $laporanPendingCount = LaporanLapak::where('status', 'pending')->count();
        }

        $totalPengeluaran = (float) $pengeluaranHarian->sum('total_pengeluaran');

        // 4. Top 10 Produk Terlaris & Total Cup Terjual (Grafik 3)
        $queryDetailProduk = TransaksiDetail::query()
            ->join('produks', 'transaksi_details.produk_id', '=', 'produks.id')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->where('transaksis.created_at', '>=', $startDate)
            ->where('transaksis.status', 'selesai')
            ->when($this->filterLapak && $hasLapakOnTransaksi, function ($q) {
                $q->where('transaksis.lapak_id', $this->filterLapak);
            });

        $totalCupTerjual = (int) (clone $queryDetailProduk)->sum('transaksi_details.jumlah');

        $trendProduk = (clone $queryDetailProduk)
            ->select(
                'produks.nama_produk',
                DB::raw('SUM(transaksi_details.jumlah) as total_terjual')
            )
            ->groupBy('produks.nama_produk')
            ->orderBy('total_terjual', 'DESC')
            ->take(10)
            ->get();

        // 5. Kalkulasi Laba Bersih Operasional & Margin
        $estimasiLabaBersih = $totalOmzet - $totalPengeluaran;
        $persentaseMargin   = $totalOmzet > 0
            ? round(($estimasiLabaBersih / $totalOmzet) * 100, 1)
            : 0;

        // 6. Indikator Kesehatan Sistem & Peringatan Stok
        $lapakAktifCount = $allLapaks->where('status', 'aktif')->count();
        $totalLapakCount = $allLapaks->count();

        $semuaBahan = BahanBaku::orderBy('nama_bahan', 'asc')->get();
        $bahanKritisList = $semuaBahan->filter(function ($b) use ($hasStokSekarang) {
            $stok = $hasStokSekarang && isset($b->stok_sekarang)
                ? (float) $b->stok_sekarang
                : (float) ($b->stok ?? $b->stok_sekarang ?? 0);
            return $stok <= (float) ($b->stok_minimum ?? 0);
        })->values();

        $bahanKritisCount = $bahanKritisList->count();

        $produkStokRendahList = Produk::where('stok_pusat', '<=', 10)
            ->when($hasStatusProduk, fn($q) => $q->where('status', 'aktif'))
            ->orderBy('stok_pusat', 'asc')
            ->get();

        $produkMenipisCount = $produkStokRendahList->count();

        $userAktifCount = $hasStatusUser
            ? User::where('status', 'aktif')->count()
            : User::count();

        // 7. Live Feed 6 Aktivitas Audit Log Terakhir
        $recentLogs = $hasTableAudit
            ? AuditLog::with(['user.role', 'user.lapak'])->latest()->take(6)->get()
            : collect();

        return view('livewire.admin.dashboard-admin', [
            // Data Grafik
            'chartOmzetLabels'       => $penjualanHarian->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray(),
            'chartOmzetValues'       => $penjualanHarian->pluck('total_omzet')->map(fn($v) => (float) $v)->toArray(),
            'chartLapakLabels'       => $penjualanPerLapak->pluck('nama_lapak')->toArray(),
            'chartLapakValues'       => $penjualanPerLapak->pluck('transaksis_sum_total_harga')->map(fn($v) => (float) ($v ?? 0))->toArray(),
            'chartPengeluaranLabels' => $pengeluaranHarian->pluck('tanggal')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray(),
            'chartPengeluaranValues' => $pengeluaranHarian->pluck('total_pengeluaran')->map(fn($v) => (float) $v)->toArray(),
            'chartProdukLabels'      => $trendProduk->pluck('nama_produk')->toArray(),
            'chartProdukValues'      => $trendProduk->pluck('total_terjual')->map(fn($v) => (int) $v)->toArray(),

            // KPI Finansial & Volume
            'totalOmzet'             => $totalOmzet,
            'totalTransaksiCount'    => $totalTransaksiCount,
            'totalPengeluaran'       => $totalPengeluaran,
            'estimasiLabaBersih'     => $estimasiLabaBersih,
            'persentaseMargin'       => $persentaseMargin,
            'totalCupTerjual'        => $totalCupTerjual,

            // Indikator Operasional & Peringatan Stok
            'allLapaks'              => $allLapaks,
            'penjualanPerLapak'      => $penjualanPerLapak,
            'lapakAktifCount'        => $lapakAktifCount,
            'totalLapakCount'        => $totalLapakCount,
            'bahanKritisList'        => $bahanKritisList,
            'bahanKritisCount'       => $bahanKritisCount,
            'produkStokRendahList'   => $produkStokRendahList,
            'produkMenipisCount'     => $produkMenipisCount,
            'laporanPendingCount'    => $laporanPendingCount,
            'userAktifCount'         => $userAktifCount,
            'recentLogs'             => $recentLogs,
        ])->layout('layouts.app');
    }
}
