<?php

namespace App\Livewire\Sales;

use App\Models\LaporanLapak;
use App\Models\Produk;
use App\Models\StokLapak;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class KasirLapak extends Component
{
    public $cart = [];
    public $metode_pembayaran = 'tunai';
    public $bayar_tunai = null;
    public $bayar_qris = null;
    public $search = '';

    public function mount()
    {
        if (!Auth::user()->lapak_id) {
            session()->flash('error', 'Akun Anda belum dipetakan ke armada lapak manapun.');
        }
    }

    public function updatedMetodePembayaran($value)
    {
        if ($value !== 'split') {
            $this->bayar_tunai = null;
            $this->bayar_qris = null;
        }
    }

    /**
     * Menentukan tanggal shift transaksi.
     * Jika Laporan Penutupan hari ini sudah terkirim (LOCKED),
     * maka transaksi otomatis dialihkan ke tanggal BESOK.
     */
    private function getTanggalShiftAktif()
    {
        $lapakId = Auth::user()->lapak_id;
        $hariIni = now()->toDateString();

        $laporanHariIniTerkirim = LaporanLapak::where('lapak_id', $lapakId)
            ->where('tanggal', $hariIni)
            ->exists();

        if ($laporanHariIniTerkirim) {
            return [
                'tanggal' => now()->addDay()->toDateString(),
                'created_at' => now()->addDay()->startOfDay()->addHours(6),
                'is_pending_next_shift' => true,
            ];
        }

        return [
            'tanggal' => $hariIni,
            'created_at' => now(),
            'is_pending_next_shift' => false,
        ];
    }

    public function tambahKeKeranjang($produkId)
    {
        $lapakId = Auth::user()->lapak_id;
        $stokLapak = StokLapak::where('lapak_id', $lapakId)
            ->where('produk_id', $produkId)
            ->first();

        if (!$stokLapak || $stokLapak->jumlah_stok <= 0) {
            session()->flash('error', 'Stok produk ini di lapak Anda telah habis!');
            return;
        }

        $jumlahDiKeranjang = $this->cart[$produkId]['jumlah'] ?? 0;

        if ($jumlahDiKeranjang + 1 > $stokLapak->jumlah_stok) {
            session()->flash('error', 'Jumlah melebihi sisa stok yang tersedia!');
            return;
        }

        $produk = Produk::find($produkId);

        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['jumlah']++;
            $this->cart[$produkId]['subtotal'] = $this->cart[$produkId]['jumlah'] * $produk->harga_jual;
        } else {
            $this->cart[$produkId] = [
                'produk_id' => $produk->id,
                'nama_produk' => $produk->nama_produk,
                'harga_jual' => $produk->harga_jual,
                'jumlah' => 1,
                'subtotal' => $produk->harga_jual,
            ];
        }
    }

    public function kurangiDariKeranjang($produkId)
    {
        if (isset($this->cart[$produkId])) {
            $this->cart[$produkId]['jumlah']--;
            if ($this->cart[$produkId]['jumlah'] <= 0) {
                unset($this->cart[$produkId]);
            } else {
                $this->cart[$produkId]['subtotal'] = $this->cart[$produkId]['jumlah'] * $this->cart[$produkId]['harga_jual'];
            }
        }
    }

    public function hapusItem($produkId)
    {
        unset($this->cart[$produkId]);
    }

    public function getTotalProsesProperty()
    {
        return collect($this->cart)->sum('subtotal');
    }

    public function prosesTransaksi()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang belanja masih kosong.');
            return;
        }

        $lapakId = Auth::user()->lapak_id;
        $totalHarga = $this->totalProses;
        $bayarTunai = 0;
        $bayarQris = 0;

        if ($this->metode_pembayaran === 'split') {
            $bayarTunai = (int) $this->bayar_tunai;
            $bayarQris = (int) $this->bayar_qris;

            if (($bayarTunai + $bayarQris) !== (int) $totalHarga) {
                session()->flash('error', 'Jumlah Nominal Tunai + QRIS harus sama persis dengan Total Transaksi (Rp ' . number_format($totalHarga, 0, ',', '.') . ').');
                return;
            }
        } elseif ($this->metode_pembayaran === 'tunai') {
            $bayarTunai = $totalHarga;
            $bayarQris = 0;
        } elseif ($this->metode_pembayaran === 'qris') {
            $bayarTunai = 0;
            $bayarQris = $totalHarga;
        }

        $shiftInfo = $this->getTanggalShiftAktif();

        DB::beginTransaction();
        try {
            foreach ($this->cart as $item) {
                $stok = StokLapak::where('lapak_id', $lapakId)
                    ->where('produk_id', $item['produk_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$stok || $stok->jumlah_stok < $item['jumlah']) {
                    DB::rollBack();
                    session()->flash('error', "Stok untuk {$item['nama_produk']} tidak mencukupi.");
                    return;
                }
            }

            $transaksi = Transaksi::create([
                'nomor_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
                'lapak_id' => $lapakId,
                'user_id' => Auth::id(),
                'total_harga' => $totalHarga,
                'metode_pembayaran' => $this->metode_pembayaran,
                'bayar_tunai' => $bayarTunai,
                'bayar_qris' => $bayarQris,
                'status' => 'selesai',
                'created_at' => $shiftInfo['created_at'],
                'updated_at' => $shiftInfo['created_at'],
            ]);

            foreach ($this->cart as $item) {
                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_jual'],
                    'subtotal' => $item['subtotal'],
                    'created_at' => $shiftInfo['created_at'],
                ]);

                StokLapak::where('lapak_id', $lapakId)
                    ->where('produk_id', $item['produk_id'])
                    ->decrement('jumlah_stok', $item['jumlah']);
            }

            DB::commit();

            $this->cart = [];
            $this->bayar_tunai = null;
            $this->bayar_qris = null;
            $this->metode_pembayaran = 'tunai';

            if ($shiftInfo['is_pending_next_shift']) {
                session()->flash('message', 'Transaksi berhasil! Karena laporan shift hari ini sudah dikirim, penjualan ini otomatis masuk ke Shift Besok (' . \Carbon\Carbon::parse($shiftInfo['tanggal'])->format('d M Y') . ').');
            } else {
                session()->flash('message', 'Transaksi berhasil dicatat dan stok terpotong!');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $lapakId = Auth::user()->lapak_id;

        $baseQuery = Transaksi::where('lapak_id', $lapakId)->where('status', 'selesai');

        $txHariIniIds = (clone $baseQuery)->whereDate('created_at', now()->today())->pluck('id');
        $tx7HariIds   = (clone $baseQuery)->where('created_at', '>=', now()->subDays(7)->startOfDay())->pluck('id');
        $tx15HariIds  = (clone $baseQuery)->where('created_at', '>=', now()->subDays(15)->startOfDay())->pluck('id');
        $tx30HariIds  = (clone $baseQuery)->where('created_at', '>=', now()->subDays(30)->startOfDay())->pluck('id');

        $cupHariIni = TransaksiDetail::whereIn('transaksi_id', $txHariIniIds)->sum('jumlah');
        $cup7Hari   = TransaksiDetail::whereIn('transaksi_id', $tx7HariIds)->sum('jumlah');
        $cup15Hari  = TransaksiDetail::whereIn('transaksi_id', $tx15HariIds)->sum('jumlah');
        $cup30Hari  = TransaksiDetail::whereIn('transaksi_id', $tx30HariIds)->sum('jumlah');

        $stokLapaks = StokLapak::with('produk')
            ->where('lapak_id', $lapakId)
            ->whereHas('produk', function ($q) {
                $q->where('nama_produk', 'like', '%' . $this->search . '%')
                    ->where('status', 'aktif');
            })
            ->get();

        $riwayatTransaksi = Transaksi::with(['details.produk', 'user'])
            ->where('lapak_id', $lapakId)
            ->whereDate('created_at', now()->today())
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.sales.kasir-lapak', [
            'cupHariIni' => $cupHariIni,
            'cup7Hari'   => $cup7Hari,
            'cup15Hari'  => $cup15Hari,
            'cup30Hari'  => $cup30Hari,
            'stokLapaks' => $stokLapaks,
            'riwayatTransaksi' => $riwayatTransaksi,
        ])->layout('layouts.app');
    }
}
