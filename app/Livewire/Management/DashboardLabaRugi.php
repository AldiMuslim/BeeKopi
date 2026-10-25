<?php

namespace App\Livewire\Management;

use App\Models\Lapak;
use App\Models\LaporanLapak;
use App\Models\RiwayatBahanBaku;
use App\Models\Transaksi;
use Carbon\Carbon;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanLabaRugiExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardLabaRugi extends Component
{
    public $periode = 'bulan_ini';
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

    public function exportPdf()
    {
        $start = Carbon::parse($this->tanggal_mulai)->startOfDay();
        $end = Carbon::parse($this->tanggal_selesai)->endOfDay();

        $totalOmzet = Transaksi::where('status', 'selesai')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $omzetTunai = Transaksi::where('status', 'selesai')->where('metode_pembayaran', 'tunai')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $omzetQris = Transaksi::where('status', 'selesai')->where('metode_pembayaran', 'qris')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $totalBiayaBahan = RiwayatBahanBaku::where('jenis', 'masuk')->whereBetween('created_at', [$start, $end])->sum('total_biaya');

        $laporansApproved = LaporanLapak::where('status', 'approved')->whereBetween('tanggal', [$this->tanggal_mulai, $this->tanggal_selesai])->get();
        $totalUangMakan = $laporansApproved->sum('uang_makan');
        $totalPengeluaranLain = $laporansApproved->sum('pengeluaran_lain');
        $totalBiayaOperasional = $totalUangMakan + $totalPengeluaranLain;
        $totalPengeluaran = $totalBiayaBahan + $totalBiayaOperasional;
        $labaBersih = $totalOmzet - $totalPengeluaran;

        $performaLapaks = Lapak::withCount(['transaksis as total_transaksi' => function ($q) use ($start, $end) {
            $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
        }])
            ->withSum(['transaksis as total_omzet' => function ($q) use ($start, $end) {
                $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
            }], 'total_harga')->get();

        $data = [
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
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
        ];

        $pdf = Pdf::loadView('pdf.laporan-laba-rugi', $data);
        return response()->streamDownload(fn() => print($pdf->output()), 'Laporan_Laba_Rugi_BeeKopi_' . $this->tanggal_mulai . '_sd_' . $this->tanggal_selesai . '.pdf');
    }

    public function render()
    {
        $start = Carbon::parse($this->tanggal_mulai)->startOfDay();
        $end = Carbon::parse($this->tanggal_selesai)->endOfDay();

        $totalOmzet = Transaksi::where('status', 'selesai')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $omzetTunai = Transaksi::where('status', 'selesai')->where('metode_pembayaran', 'tunai')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $omzetQris = Transaksi::where('status', 'selesai')->where('metode_pembayaran', 'qris')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $totalBiayaBahan = RiwayatBahanBaku::where('jenis', 'masuk')->whereBetween('created_at', [$start, $end])->sum('total_biaya');

        $laporansApproved = LaporanLapak::where('status', 'approved')->whereBetween('tanggal', [$this->tanggal_mulai, $this->tanggal_selesai])->get();
        $totalUangMakan = $laporansApproved->sum('uang_makan');
        $totalPengeluaranLain = $laporansApproved->sum('pengeluaran_lain');
        $totalBiayaOperasional = $totalUangMakan + $totalPengeluaranLain;
        $totalPengeluaran = $totalBiayaBahan + $totalBiayaOperasional;
        $labaBersih = $totalOmzet - $totalPengeluaran;

        $performaLapaks = Lapak::withCount(['transaksis as total_transaksi' => function ($q) use ($start, $end) {
            $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
        }])
            ->withSum(['transaksis as total_omzet' => function ($q) use ($start, $end) {
                $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
            }], 'total_harga')->get();

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

    public function exportExcel()
    {
        $start = Carbon::parse($this->tanggal_mulai)->startOfDay();
        $end = Carbon::parse($this->tanggal_selesai)->endOfDay();

        $totalOmzet = Transaksi::where('status', 'selesai')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $omzetTunai = Transaksi::where('status', 'selesai')->where('metode_pembayaran', 'tunai')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $omzetQris = Transaksi::where('status', 'selesai')->where('metode_pembayaran', 'qris')->whereBetween('created_at', [$start, $end])->sum('total_harga');
        $totalBiayaBahan = RiwayatBahanBaku::where('jenis', 'masuk')->whereBetween('created_at', [$start, $end])->sum('total_biaya');

        $laporansApproved = LaporanLapak::where('status', 'approved')->whereBetween('tanggal', [$this->tanggal_mulai, $this->tanggal_selesai])->get();
        $totalUangMakan = $laporansApproved->sum('uang_makan');
        $totalPengeluaranLain = $laporansApproved->sum('pengeluaran_lain');
        $totalBiayaOperasional = $totalUangMakan + $totalPengeluaranLain;
        $totalPengeluaran = $totalBiayaBahan + $totalBiayaOperasional;
        $labaBersih = $totalOmzet - $totalPengeluaran;

        $performaLapaks = Lapak::withSum(['transaksis as total_omzet' => function ($q) use ($start, $end) {
            $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
        }], 'total_harga')->get();

        $data = [
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'totalOmzet' => $totalOmzet,
            'omzetTunai' => $omzetTunai,
            'omzetQris' => $omzetQris,
            'totalBiayaBahan' => $totalBiayaBahan,
            'totalBiayaOperasional' => $totalBiayaOperasional,
            'totalUangMakan' => $totalUangMakan,
            'totalPengeluaranLain' => $totalPengeluaranLain,
            'labaBersih' => $labaBersih,
            'performaLapaks' => $performaLapaks,
        ];

        return Excel::download(new LaporanLabaRugiExport($data), 'Laporan_Laba_Rugi_BeeKopi_' . $this->tanggal_mulai . '_sd_' . $this->tanggal_selesai . '.xlsx');
    }
}
