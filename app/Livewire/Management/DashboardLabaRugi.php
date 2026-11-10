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

    // State Modal Drill-Down
    public $showModalDetail = false;
    public $modalType = ''; // 'omzet', 'bahan', 'operasional', 'laba'
    public $modalTitle = '';

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
        } elseif ($this->periode === 'bulan_ini') {
            $this->tanggal_mulai = now()->startOfMonth()->format('Y-m-d');
            $this->tanggal_selesai = now()->endOfMonth()->format('Y-m-d');
        } elseif ($this->periode === 'tahun_ini') {
            $this->tanggal_mulai = now()->startOfYear()->format('Y-m-d');
            $this->tanggal_selesai = now()->endOfYear()->format('Y-m-d');
        }
    }

    /**
     * Centralized Financial Calculation (DRY Rule)
     */
    private function getLabaRugiData()
    {
        $start = Carbon::parse($this->tanggal_mulai)->startOfDay();
        $end = Carbon::parse($this->tanggal_selesai)->endOfDay();

        // Query Transaksi Kasir Selesai
        $transaksis = Transaksi::with(['lapak', 'user'])
            ->where('status', 'selesai')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalOmzet = (float) $transaksis->sum('total_harga');

        // Akumulasi Omzet Tunai dan QRIS (Termasuk Split Payment)
        $omzetTunai = (float) ($transaksis->where('metode_pembayaran', 'tunai')->sum('total_harga')
            + $transaksis->where('metode_pembayaran', 'split')->sum('bayar_tunai'));

        $omzetQris = (float) ($transaksis->where('metode_pembayaran', 'qris')->sum('total_harga')
            + $transaksis->where('metode_pembayaran', 'split')->sum('bayar_qris'));

        // 1. Belanja Bahan Baku (Gudang/Produksi)
        $riwayatBahan = RiwayatBahanBaku::with(['bahanBaku', 'user'])
            ->where('jenis', 'masuk')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalBiayaBahan = (float) $riwayatBahan->sum('total_biaya');

        // 2. Operasional Lapak (Laporan Approved)
        $laporansApproved = LaporanLapak::with(['lapak', 'user'])
            ->where('status', 'approved')
            ->whereBetween('tanggal', [$this->tanggal_mulai, $this->tanggal_selesai])
            ->get();

        $totalUangMakan = (float) $laporansApproved->sum('uang_makan');
        $totalPengeluaranLain = (float) $laporansApproved->sum('pengeluaran_lain');
        $totalBiayaOperasional = $totalUangMakan + $totalPengeluaranLain;

        // 3. Rekap Total Pengeluaran & Laba Bersih
        $totalPengeluaran = $totalBiayaBahan + $totalBiayaOperasional;
        $labaBersih = $totalOmzet - $totalPengeluaran;
        $profitMargin = $totalOmzet > 0 ? round(($labaBersih / $totalOmzet) * 100, 1) : 0;

        // 4. Performa Kontribusi Per Lapak Cabang
        $performaLapaks = Lapak::withCount(['transaksis as total_transaksi' => function ($q) use ($start, $end) {
            $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
        }])
            ->withSum(['transaksis as total_omzet' => function ($q) use ($start, $end) {
                $q->where('status', 'selesai')->whereBetween('created_at', [$start, $end]);
            }], 'total_harga')
            ->get();

        return [
            'start' => $start,
            'end' => $end,
            'transaksis' => $transaksis,
            'totalOmzet' => $totalOmzet,
            'omzetTunai' => $omzetTunai,
            'omzetQris' => $omzetQris,
            'riwayatBahan' => $riwayatBahan,
            'totalBiayaBahan' => $totalBiayaBahan,
            'laporansApproved' => $laporansApproved,
            'totalBiayaOperasional' => $totalBiayaOperasional,
            'totalUangMakan' => $totalUangMakan,
            'totalPengeluaranLain' => $totalPengeluaranLain,
            'totalPengeluaran' => $totalPengeluaran,
            'labaBersih' => $labaBersih,
            'profitMargin' => $profitMargin,
            'performaLapaks' => $performaLapaks,
        ];
    }

    public function openModal($type)
    {
        $this->modalType = $type;
        $this->showModalDetail = true;

        if ($type === 'omzet') {
            $this->modalTitle = 'Rincian Omzet Penjualan & Riwayat Transaksi';
        } elseif ($type === 'bahan') {
            $this->modalTitle = 'Rincian Belanja Bahan Baku (Gudang/Produksi)';
        } elseif ($type === 'operasional') {
            $this->modalTitle = 'Rincian Pengeluaran Operasional Lapak Cabang';
        } elseif ($type === 'laba') {
            $this->modalTitle = 'Lembar Analisis Laba Bersih & Profit Margin';
        }
    }

    public function closeModal()
    {
        $this->showModalDetail = false;
        $this->modalType = '';
        $this->modalTitle = '';
    }

    public function exportPdf()
    {
        $data = $this->getLabaRugiData();
        $pdfData = array_merge($data, [
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
        ]);

        $pdf = Pdf::loadView('pdf.laporan-laba-rugi', $pdfData);
        return response()->streamDownload(
            fn() => print($pdf->output()),
            'Laporan_Laba_Rugi_BeeKopi_' . $this->tanggal_mulai . '_sd_' . $this->tanggal_selesai . '.pdf'
        );
    }

    public function exportExcel()
    {
        $data = $this->getLabaRugiData();
        $excelData = array_merge($data, [
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
        ]);

        return Excel::download(
            new LaporanLabaRugiExport($excelData),
            'Laporan_Laba_Rugi_BeeKopi_' . $this->tanggal_mulai . '_sd_' . $this->tanggal_selesai . '.xlsx'
        );
    }

    public function render()
    {
        $financials = $this->getLabaRugiData();

        return view('livewire.management.dashboard-laba-rugi', array_merge($financials, [
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
        ]))->layout('layouts.app');
    }
}
