<?php

namespace App\Livewire\Sales;

use App\Models\LaporanLapak;
use App\Models\StokLapak;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class LaporanPenutupan extends Component
{
    use WithFileUploads;

    public $tanggal;

    // Opname fisik per produk
    public $sisa_fisik_per_produk = [];
    public $rusak_per_produk = [];
    public $alasan_stok_rusak = '';

    // Data Omzet Sistem
    public $omzet_tunai = 0;
    public $omzet_qris = 0;
    public $total_cup_sistem = 0;
    public $total_cup_tunai = 0;
    public $total_cup_qris = 0;
    public $rincian_produk = [];

    // Ketentuan Management (Read-Only / Lock)
    public $uang_makan = 20000;
    public $jatah_minum_cup = 1;

    // Dynamic Item List Pengeluaran Lain
    public $item_pengeluaran = [];
    public $nama_item_input = '';
    public $nominal_item_input = '';

    public $foto_qris;

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $lapakId = Auth::user()->lapak_id;

        $stokLapaks = StokLapak::where('lapak_id', $lapakId)->get();
        foreach ($stokLapaks as $stok) {
            $this->sisa_fisik_per_produk[$stok->produk_id] = 0;
            $this->rusak_per_produk[$stok->produk_id] = 0;
        }

        $this->hitungOmzetSistem();
    }

    public function tambahPengeluaran()
    {
        $this->validate([
            'nama_item_input' => 'required|string|max:255',
            'nominal_item_input' => 'required|numeric|min:1',
        ], [], [
            'nama_item_input' => 'Nama pengeluaran',
            'nominal_item_input' => 'Nominal pengeluaran',
        ]);

        $this->item_pengeluaran[] = [
            'nama' => $this->nama_item_input,
            'nominal' => (float) $this->nominal_item_input,
        ];

        $this->reset(['nama_item_input', 'nominal_item_input']);
    }

    public function hapusPengeluaran($index)
    {
        unset($this->item_pengeluaran[$index]);
        $this->item_pengeluaran = array_values($this->item_pengeluaran);
    }

    public function getPengeluaranLainTotalProperty()
    {
        return array_sum(array_column($this->item_pengeluaran, 'nominal'));
    }

    public function getSisaStokFisikTotalProperty()
    {
        return array_sum(array_map('intval', $this->sisa_fisik_per_produk));
    }

    public function getStokRusakTotalProperty()
    {
        return array_sum(array_map('intval', $this->rusak_per_produk));
    }

    public function hitungOmzetSistem()
    {
        $lapakId = Auth::user()->lapak_id;

        $transaksiHariIni = Transaksi::where('lapak_id', $lapakId)
            ->whereDate('created_at', $this->tanggal)
            ->where('status', 'selesai')
            ->get();

        $this->omzet_tunai = $transaksiHariIni->where('metode_pembayaran', 'tunai')->sum('total_harga');
        $this->omzet_qris = $transaksiHariIni->where('metode_pembayaran', 'qris')->sum('total_harga');

        $transaksiIds = $transaksiHariIni->pluck('id');

        // Total Cup Keseluruhan
        $this->total_cup_sistem = TransaksiDetail::whereIn('transaksi_id', $transaksiIds)->sum('jumlah');

        // Rincian Cup per Metode Pembayaran
        $transaksiTunaiIds = $transaksiHariIni->where('metode_pembayaran', 'tunai')->pluck('id');
        $transaksiQrisIds = $transaksiHariIni->where('metode_pembayaran', 'qris')->pluck('id');

        $this->total_cup_tunai = TransaksiDetail::whereIn('transaksi_id', $transaksiTunaiIds)->sum('jumlah');
        $this->total_cup_qris = TransaksiDetail::whereIn('transaksi_id', $transaksiQrisIds)->sum('jumlah');

        // Rincian Menu Terjual
        $this->rincian_produk = TransaksiDetail::with('produk')
            ->whereIn('transaksi_id', $transaksiIds)
            ->selectRaw('produk_id, SUM(jumlah) as total_cup, SUM(subtotal) as total_nominal')
            ->groupBy('produk_id')
            ->get();
    }

    public function getSetoranBersihProperty()
    {
        $totalPengeluaran = (float) $this->uang_makan + $this->pengeluaranLainTotal;
        $setoran = (float) $this->omzet_tunai - $totalPengeluaran;
        return $setoran > 0 ? $setoran : 0;
    }

    public function kirimLaporan()
    {
        $this->validate([
            'sisa_fisik_per_produk.*' => 'required|numeric|min:0',
            'rusak_per_produk.*' => 'required|numeric|min:0',
            'foto_qris' => 'nullable|image|max:2048',
        ]);

        $lapakId = Auth::user()->lapak_id;

        $laporanAda = LaporanLapak::where('lapak_id', $lapakId)
            ->where('tanggal', $this->tanggal)
            ->first();

        if ($laporanAda) {
            session()->flash('error', 'Laporan penutupan untuk hari ini sudah pernah dikirim.');
            return;
        }

        $pathFoto = null;
        if ($this->foto_qris) {
            $pathFoto = $this->foto_qris->store('bukti-qris', 'public');
        }

        $sisaFisikTotal = $this->sisaStokFisikTotal;
        $stokRusakTotal = $this->stokRusakTotal;
        $totalStokAwal = StokLapak::where('lapak_id', $lapakId)->sum('jumlah_stok') + $sisaFisikTotal;

        LaporanLapak::create([
            'lapak_id' => $lapakId,
            'user_id' => Auth::id(),
            'tanggal' => $this->tanggal,
            'stok_awal' => $totalStokAwal,
            'sisa_stok_fisik' => $sisaFisikTotal,
            'total_terjual_sistem' => $this->total_cup_sistem,
            'total_terjual_fisik' => $totalStokAwal - $sisaFisikTotal - $stokRusakTotal,
            'stok_rusak_cup' => $stokRusakTotal,
            'alasan_stok_rusak' => $this->alasan_stok_rusak,
            'omzet_tunai' => $this->omzet_tunai,
            'omzet_qris' => $this->omzet_qris,
            'uang_makan' => $this->uang_makan,
            'jatah_minum_cup' => $this->jatah_minum_cup,
            'pengeluaran_lain' => $this->pengeluaranLainTotal,
            'keterangan_pengeluaran' => json_encode($this->item_pengeluaran),
            'setoran_bersih' => $this->setoranBersih,
            'foto_qris' => $pathFoto,
            'status' => 'pending',
        ]);

        session()->flash('message', 'Laporan penutupan berhasil dikirim ke Management!');
    }

    public function render()
    {
        $lapakId = Auth::user()->lapak_id;
        $stokLapaks = StokLapak::with('produk')->where('lapak_id', $lapakId)->get();

        return view('livewire.sales.laporan-penutupan', [
            'stokLapaks' => $stokLapaks,
        ])->layout('layouts.app');
    }
}