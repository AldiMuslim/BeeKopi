<?php

namespace App\Livewire\Sales;

use App\Models\LaporanLapak;
use App\Models\Produk;
use App\Models\StokLapak;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LaporanPenutupan extends Component
{
    public $tanggal;
    public $isLocked = false;

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
    public $terjual_per_produk = []; // Rekap terjual per produk [produk_id => total_cup]

    // Pengeluaran & Jatah Minum Staff
    public $uang_makan = 20000;
    public $jatah_minum_cup = 1;
    public $jatah_minum_produk_id = null;
    public $nominal_jatah_minum = 0;

    // Dynamic Item List Pengeluaran Lain
    public $item_pengeluaran = [];
    public $nama_item_input = '';
    public $nominal_item_input = '';

    public function mount()
    {
        $this->tanggal = now()->format('Y-m-d');
        $lapakId = Auth::user()->lapak_id;

        if (!$lapakId) {
            session()->flash('error', 'Akun Anda belum dipetakan ke armada lapak manapun.');
            return;
        }

        $stokLapaks = StokLapak::where('lapak_id', $lapakId)->get();
        foreach ($stokLapaks as $stok) {
            $this->sisa_fisik_per_produk[$stok->produk_id] = $stok->jumlah_stok;
            $this->rusak_per_produk[$stok->produk_id] = 0;
        }

        if ($stokLapaks->isNotEmpty()) {
            $firstStok = $stokLapaks->first();
            $this->jatah_minum_produk_id = $firstStok->produk_id;
            $this->updatedJatahMinumProdukId($firstStok->produk_id);
        }

        $this->hitungOmzetSistem();
        $this->cekLaporanTerkirim();
    }

    public function cekLaporanTerkirim()
    {
        $lapakId = Auth::user()->lapak_id;
        $laporan = LaporanLapak::where('lapak_id', $lapakId)
            ->where('tanggal', $this->tanggal)
            ->first();

        if ($laporan) {
            $this->isLocked = true;
            $this->uang_makan = $laporan->uang_makan;
            $this->jatah_minum_cup = $laporan->jatah_minum_cup;
            $this->alasan_stok_rusak = $laporan->alasan_stok_rusak;

            if ($laporan->keterangan_pengeluaran) {
                $decoded = json_decode($laporan->keterangan_pengeluaran, true);
                if (is_array($decoded)) {
                    $this->item_pengeluaran = array_values(array_filter($decoded, function ($item) {
                        return !str_contains($item['nama'] ?? '', 'Jatah Minum Staff');
                    }));
                }
            }
        }
    }

    public function updatedJatahMinumProdukId($value)
    {
        if ($value) {
            $produk = Produk::find($value);
            $this->nominal_jatah_minum = $produk ? (float) $produk->harga_jual : 0;
        } else {
            $this->nominal_jatah_minum = 0;
        }
    }

    public function tambahPengeluaran()
    {
        if ($this->isLocked) return;

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
        if ($this->isLocked) return;

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

    public function getStokSistemTotalProperty()
    {
        $lapakId = Auth::user()->lapak_id;
        return (int) StokLapak::where('lapak_id', $lapakId)->sum('jumlah_stok');
    }

    public function hitungOmzetSistem()
    {
        $lapakId = Auth::user()->lapak_id;

        $transaksiHariIni = Transaksi::where('lapak_id', $lapakId)
            ->whereDate('created_at', $this->tanggal)
            ->where('status', 'selesai')
            ->get();

        $this->omzet_tunai = $transaksiHariIni->where('metode_pembayaran', 'tunai')->sum('total_harga')
            + $transaksiHariIni->where('metode_pembayaran', 'split')->sum('bayar_tunai');

        $this->omzet_qris = $transaksiHariIni->where('metode_pembayaran', 'qris')->sum('total_harga')
            + $transaksiHariIni->where('metode_pembayaran', 'split')->sum('bayar_qris');

        $transaksiIds = $transaksiHariIni->pluck('id');

        $this->total_cup_sistem = TransaksiDetail::whereIn('transaksi_id', $transaksiIds)->sum('jumlah');

        $transaksiTunaiIds = $transaksiHariIni->whereIn('metode_pembayaran', ['tunai', 'split'])->pluck('id');
        $transaksiQrisIds = $transaksiHariIni->whereIn('metode_pembayaran', ['qris', 'split'])->pluck('id');

        $this->total_cup_tunai = TransaksiDetail::whereIn('transaksi_id', $transaksiTunaiIds)->sum('jumlah');
        $this->total_cup_qris = TransaksiDetail::whereIn('transaksi_id', $transaksiQrisIds)->sum('jumlah');

        $details = TransaksiDetail::whereIn('transaksi_id', $transaksiIds)
            ->selectRaw('produk_id, SUM(jumlah) as total_cup')
            ->groupBy('produk_id')
            ->pluck('total_cup', 'produk_id')
            ->toArray();

        $this->terjual_per_produk = $details;

        $this->rincian_produk = TransaksiDetail::with('produk')
            ->whereIn('transaksi_id', $transaksiIds)
            ->selectRaw('produk_id, SUM(jumlah) as total_cup, SUM(subtotal) as total_nominal')
            ->groupBy('produk_id')
            ->get();
    }

    public function getSelisihCupProperty()
    {
        $stokSistem = $this->stokSistemTotal;
        $sisaFisik = $this->sisaStokFisikTotal;
        $stokRusak = $this->stokRusakTotal;

        return $stokSistem - ($sisaFisik + $stokRusak);
    }

    public function getSetoranBersihProperty()
    {
        $totalPengeluaran = (float) $this->uang_makan + (float) $this->nominal_jatah_minum + $this->pengeluaranLainTotal;
        $setoran = (float) $this->omzet_tunai - $totalPengeluaran;

        return $setoran > 0 ? $setoran : 0;
    }

    public function kirimLaporan()
    {
        if ($this->isLocked) {
            session()->flash('error', 'Laporan penutupan shift hari ini sudah dikirim dan telah dikunci.');
            return;
        }

        $this->validate([
            'sisa_fisik_per_produk.*' => 'required|numeric|min:0',
            'rusak_per_produk.*' => 'required|numeric|min:0',
            'jatah_minum_produk_id' => 'required|exists:produks,id',
        ], [
            'jatah_minum_produk_id.required' => 'Silakan pilih varian minuman untuk jatah minum staff.',
        ]);

        $lapakId = Auth::user()->lapak_id;

        $laporanAda = LaporanLapak::where('lapak_id', $lapakId)
            ->where('tanggal', $this->tanggal)
            ->first();

        if ($laporanAda) {
            $this->isLocked = true;
            session()->flash('error', 'Laporan penutupan untuk hari ini sudah pernah dikirim.');
            return;
        }

        $sisaFisikTotal = $this->sisaStokFisikTotal;
        $stokRusakTotal = $this->stokRusakTotal;
        $stokSistemTotal = $this->stokSistemTotal;

        $totalStokAwal = $stokSistemTotal + $this->total_cup_sistem;
        $totalTerjualFisik = max(0, $totalStokAwal - $sisaFisikTotal - $stokRusakTotal);

        LaporanLapak::create([
            'lapak_id' => $lapakId,
            'user_id' => Auth::id(),
            'tanggal' => $this->tanggal,
            'stok_awal' => $totalStokAwal,
            'sisa_stok_fisik' => $sisaFisikTotal,
            'total_terjual_sistem' => $this->total_cup_sistem,
            'total_terjual_fisik' => $totalTerjualFisik,
            'stok_rusak_cup' => $stokRusakTotal,
            'alasan_stok_rusak' => $this->alasan_stok_rusak,
            'omzet_tunai' => $this->omzet_tunai,
            'omzet_qris' => $this->omzet_qris,
            'uang_makan' => $this->uang_makan,
            'jatah_minum_cup' => $this->jatah_minum_cup,
            'pengeluaran_lain' => $this->pengeluaranLainTotal + $this->nominal_jatah_minum,
            'keterangan_pengeluaran' => json_encode(array_merge($this->item_pengeluaran, [
                [
                    'nama' => 'Jatah Minum Staff (' . (Produk::find($this->jatah_minum_produk_id)->nama_produk ?? 'Varian') . ')',
                    'nominal' => $this->nominal_jatah_minum,
                ]
            ])),
            'setoran_bersih' => $this->setoranBersih,
            'status' => 'pending',
        ]);

        $this->isLocked = true;
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
