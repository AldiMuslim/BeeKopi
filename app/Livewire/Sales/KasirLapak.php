<?php

namespace App\Livewire\Sales;

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
    public $search = '';

    public function mount()
    {
        if (!Auth::user()->lapak_id) {
            session()->flash('error', 'Akun Anda belum dipetakan ke armada lapak manapun.');
        }
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

        DB::beginTransaction();
        try {
            // Check stok fisik akhir sekali lagi
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

            // Buat Transaksi
            $transaksi = Transaksi::create([
                'nomor_transaksi' => 'TRX-' . time() . '-' . rand(100, 999),
                'lapak_id' => $lapakId,
                'user_id' => Auth::id(),
                'total_harga' => $this->totalProses,
                'metode_pembayaran' => $this->metode_pembayaran,
                'status' => 'selesai',
            ]);

            // Buat Detail Transaksi & Potong Stok Lapak
            foreach ($this->cart as $item) {
                TransaksiDetail::create([
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $item['produk_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga_jual'],
                    'subtotal' => $item['subtotal'],
                ]);

                StokLapak::where('lapak_id', $lapakId)
                    ->where('produk_id', $item['produk_id'])
                    ->decrement('jumlah_stok', $item['jumlah']);
            }

            DB::commit();

            $this->cart = [];
            session()->flash('message', 'Transaksi berhasil dicatat dan stok terpotong!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $lapakId = Auth::user()->lapak_id;

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
            'stokLapaks' => $stokLapaks,
            'riwayatTransaksi' => $riwayatTransaksi,
        ])->layout('layouts.app');
    }
}
