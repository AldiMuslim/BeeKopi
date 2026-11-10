<?php

namespace App\Livewire\Management;

use App\Models\Lapak;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AnalisisPenjualan extends Component
{
    public $periode = '7_hari';
    public $lapak_id = ''; // Filter Lapak ('': Semua Lapak)
    public $tanggal_mulai;
    public $tanggal_selesai;

    public function mount()
    {
        $this->setPeriodeDates();
    }

    public function updatedPeriode()
    {
        if ($this->periode !== 'custom') {
            $this->setPeriodeDates();
        }
    }

    public function setPeriodeDates()
    {
        if ($this->periode === 'hari_ini') {
            $this->tanggal_mulai = now()->format('Y-m-d');
            $this->tanggal_selesai = now()->format('Y-m-d');
        } elseif ($this->periode === '7_hari') {
            $this->tanggal_mulai = now()->subDays(6)->format('Y-m-d');
            $this->tanggal_selesai = now()->format('Y-m-d');
        } elseif ($this->periode === '15_hari') {
            $this->tanggal_mulai = now()->subDays(14)->format('Y-m-d');
            $this->tanggal_selesai = now()->format('Y-m-d');
        } elseif ($this->periode === '30_hari') {
            $this->tanggal_mulai = now()->subDays(29)->format('Y-m-d');
            $this->tanggal_selesai = now()->format('Y-m-d');
        } elseif ($this->periode === 'bulan_ini') {
            $this->tanggal_mulai = now()->startOfMonth()->format('Y-m-d');
            $this->tanggal_selesai = now()->endOfMonth()->format('Y-m-d');
        }
    }

    public function render()
    {
        $start = Carbon::parse($this->tanggal_mulai)->startOfDay();
        $end = Carbon::parse($this->tanggal_selesai)->endOfDay();
        $selectedLapak = $this->lapak_id;

        // Query Utama Transaksi Selesai
        $baseTransaksiQuery = Transaksi::where('status', 'selesai')
            ->whereBetween('created_at', [$start, $end])
            ->when($selectedLapak, fn($query) => $query->where('lapak_id', $selectedLapak));

        // 1. Core KPI Metrics
        $totalOmzet = (float) (clone $baseTransaksiQuery)->sum('total_harga');
        $totalTx = (int) (clone $baseTransaksiQuery)->count();

        $totalCup = (int) TransaksiDetail::whereHas('transaksi', function ($q) use ($start, $end, $selectedLapak) {
            $q->where('status', 'selesai')
                ->whereBetween('created_at', [$start, $end])
                ->when($selectedLapak, fn($query) => $query->where('lapak_id', $selectedLapak));
        })->sum('jumlah');

        $aov = $totalTx > 0 ? round($totalOmzet / $totalTx) : 0;

        // 2. Analisis Metode Pembayaran (Tunai vs QRIS vs Split)
        $transaksiAll = (clone $baseTransaksiQuery)->get();
        $omzetTunai = $transaksiAll->where('metode_pembayaran', 'tunai')->sum('total_harga')
            + $transaksiAll->where('metode_pembayaran', 'split')->sum('bayar_tunai');

        $omzetQris = $transaksiAll->where('metode_pembayaran', 'qris')->sum('total_harga')
            + $transaksiAll->where('metode_pembayaran', 'split')->sum('bayar_qris');

        $countTunai = $transaksiAll->where('metode_pembayaran', 'tunai')->count();
        $countQris = $transaksiAll->where('metode_pembayaran', 'qris')->count();
        $countSplit = $transaksiAll->where('metode_pembayaran', 'split')->count();

        // 3. Ranking Top 5 Produk Terlaris
        $topProduk = TransaksiDetail::with('produk')
            ->whereHas('transaksi', function ($q) use ($start, $end, $selectedLapak) {
                $q->where('status', 'selesai')
                    ->whereBetween('created_at', [$start, $end])
                    ->when($selectedLapak, fn($query) => $query->where('lapak_id', $selectedLapak));
            })
            ->select('produk_id', DB::raw('SUM(jumlah) as total_cup'), DB::raw('SUM(subtotal) as total_omzet'))
            ->groupBy('produk_id')
            ->orderByDesc('total_cup')
            ->take(5)
            ->get();

        // 4. Analisis Jam Sibuk Penjualan (Peak Hours)
        $jamSibuk = (clone $baseTransaksiQuery)
            ->select(DB::raw('HOUR(created_at) as jam'), DB::raw('COUNT(*) as total_transaksi'), DB::raw('SUM(total_harga) as total_omzet'))
            ->groupBy('jam')
            ->orderBy('jam')
            ->get();

        // 5. Tren Omzet Harian
        $trenHarian = (clone $baseTransaksiQuery)
            ->select(DB::raw('DATE(created_at) as tanggal'), DB::raw('SUM(total_harga) as omzet'), DB::raw('COUNT(*) as total_tx'))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // 6. Performa Penjualan Per Lapak Cabang
        $performaLapak = Lapak::when($selectedLapak, fn($query) => $query->where('id', $selectedLapak))
            ->withSum(['transaksis as total_omzet' => function ($q) use ($start, $end) {
                $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
            }], 'total_harga')
            ->withCount(['transaksis as total_tx' => function ($q) use ($start, $end) {
                $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
            }])
            ->get();

        $totalOmzetAll = $totalOmzet ?: 1;

        return view('livewire.management.analisis-penjualan', [
            'lapaks' => Lapak::all(),
            'totalOmzet' => $totalOmzet,
            'totalTx' => $totalTx,
            'totalCup' => $totalCup,
            'aov' => $aov,
            'omzetTunai' => $omzetTunai,
            'omzetQris' => $omzetQris,
            'countTunai' => $countTunai,
            'countQris' => $countQris,
            'countSplit' => $countSplit,
            'topProduk' => $topProduk,
            'jamSibuk' => $jamSibuk,
            'trenHarian' => $trenHarian,
            'performaLapak' => $performaLapak,
            'totalOmzetAll' => $totalOmzetAll,
        ])->layout('layouts.app');
    }
}
