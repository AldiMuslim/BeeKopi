<?php

namespace App\Livewire\Management;

use App\Models\Lapak;
use App\Models\LaporanLapak;
use App\Models\RiwayatBahanBaku;
use App\Models\Transaksi;
use Carbon\Carbon;
use Livewire\Component;

class DashboardLabaRugi extends Component
{
    public $periode = 'bulan_ini'; // 'hari_ini', 'bulan_ini', 'tahun_ini', 'custom'
    public $tanggal_mulai;
    public $tanggal_selesai;

    public function mount()
    {
        $this->setPeriodeDates();
    }

    public function updatedPeriode()
    {
        $this->setPeriodeDates();
    }

    public function setPeriodeDates()
    {
        if ($this->periode === 'hari_ini') {
            $this->tanggal_mulai = now()->format('Y-m-d');
            $this->tanggal_selesai = now()->format('Y-m-d');
        } elseif ($this->periode === 'bulan_ini') {
            $this->tanggal_mulai = now()->startOfMonth()->format('Y-m-d');
            $this->tanggal_selesai = now()->endOfMonth()->format('Y-m-d');
        } elseif ($this->periode === 'tahun_ini') {
            $this->tanggal_mulai = now()->startOfYear()->format('Y-m-d');
            $this->tanggal_selesai = now()->endOfYear()->format('Y-m-d');
        }
    }

    public function render()
    {
        $start = Carbon::parse($this->tanggal_mulai)->startOfDay();
        $end = Carbon::parse($this->tanggal_selesai)->endOfDay();

        // 1. Total Omzet Penjualan (Seluruh Lapak)
        $totalOmzet = Transaksi::where('status', 'selesai')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_harga');

        $omzetTunai = Transaksi::where('status', 'selesai')
            ->where('metode_pembayaran', 'tunai')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_harga');

        $omzetQris = Transaksi::where('status', 'selesai')
            ->where('metode_pembayaran', 'qris')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_harga');

        // 2. Total Pengeluaran Belanja Bahan Baku (Gudang Pusat)
        $totalBiayaBahan = RiwayatBahanBaku::where('jenis', 'masuk')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_biaya');

        // 3. Total Pengeluaran Operasional Lapak (Laporan Approved)
        $laporansApproved = LaporanLapak::where('status', 'approved')
            ->whereBetween('tanggal', [$this->tanggal_mulai, $this->tanggal_selesai])
            ->get();

        $totalUangMakan = $laporansApproved->sum('uang_makan');
        $totalPengeluaranLain = $laporansApproved->sum('pengeluaran_lain');
        $totalBiayaOperasional = $totalUangMakan + $totalPengeluaranLain;

        // 4. Perhitungan Laba / Rugi Bersih
        $totalPengeluaran = $totalBiayaBahan + $totalBiayaOperasional;
        $labaBersih = $totalOmzet - $totalPengeluaran;

        // 5. Rekap Performa Penjualan Per Lapak
        $performaLapaks = Lapak::withCount(['transaksis as total_transaksi' => function ($q) use ($start, $end) {
            $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
        }])
            ->withSum(['transaksis as total_omzet' => function ($q) use ($start, $end) {
                $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
            }], 'total_harga')
            ->get();

        return view('livewire.management.dashboard-laba-rugi', [
            'totalOmzet' => $totalOmzet,
            'omzetTunai' => $omzetTunai,
            'omzetQris' => $omzetQris,
            'totalBiayaBahan' => $totalBiayaBahan,
            'totalBiayaOperasional' => $totalBiayaOperasional,
            'totalUangMakan' => $totalUangMakan,
            'totalPengeluaranLain' => $totalPengeluaranLain,
            'totalPengeluaran' => $totalPengeluaran,
            'labaBersih' => $labaBersih,
            'performaLapaks' => $performaLapaks,
        ])->layout('layouts.app');
    }
}
