<?php

namespace App\Livewire\Management;

use App\Models\DistribusiStok;
use App\Models\DistribusiStokDetail;
use App\Models\Lapak;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AlokasiStok extends Component
{
    use WithPagination;

    public $lapak_id = '';
    public $catatan = '';

    // Array input jumlah cup per produk: [produk_id => jumlah_cup]
    public $jumlah_cup = [];

    public function mount()
    {
        $this->initJumlahCup();
    }

    public function initJumlahCup()
    {
        $produks = Produk::where('status', 'aktif')->get();
        foreach ($produks as $p) {
            $this->jumlah_cup[$p->id] = 0;
        }
    }

    public function kirimStok()
    {
        $this->validate([
            'lapak_id' => 'required|exists:lapaks,id',
        ], [], [
            'lapak_id' => 'Lapak Tujuan',
        ]);

        // Filter produk yang diisi jumlah cup > 0
        $itemsDikirim = array_filter($this->jumlah_cup, function ($qty) {
            return is_numeric($qty) && (int)$qty > 0;
        });

        if (empty($itemsDikirim)) {
            session()->flash('error', 'Masukkan minimal 1 jumlah cup produk yang akan dikirim!');
            return;
        }

        try {
            DB::transaction(function () use ($itemsDikirim) {
                // 1. Validasi stok gudang pusat
                foreach ($itemsDikirim as $produkId => $qty) {
                    $produk = Produk::findOrFail($produkId);
                    if ($produk->stok_pusat < $qty) {
                        throw new \Exception("Stok Gudang Pusat untuk {$produk->nama_produk} tidak mencukupi! (Sisa Stok: {$produk->stok_pusat} cup)");
                    }
                }

                // 2. Buat Tiket Distribusi
                $kodeDistribusi = 'DST-' . now()->format('YmdHis') . '-' . rand(100, 999);
                $distribusi = DistribusiStok::create([
                    'kode_distribusi' => $kodeDistribusi,
                    'lapak_id' => $this->lapak_id,
                    'user_id' => Auth::id(),
                    'tanggal' => now()->toDateString(),
                    'catatan' => $this->catatan,
                    'status' => 'pending',
                ]);

                // 3. Simpan Detail & Potong Stok Gudang Pusat
                foreach ($itemsDikirim as $produkId => $qty) {
                    DistribusiStokDetail::create([
                        'distribusi_stok_id' => $distribusi->id,
                        'produk_id' => $produkId,
                        'jumlah_cup' => $qty,
                    ]);

                    Produk::where('id', $produkId)->decrement('stok_pusat', $qty);
                }
            });

            session()->flash('message', 'Alokasi pengiriman stok berhasil diterbitkan ke armada lapak!');
            $this->reset(['lapak_id', 'catatan']);
            $this->initJumlahCup();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.management.alokasi-stok', [
            'lapaks' => Lapak::all(),
            'produks' => Produk::where('status', 'aktif')->get(),
            'distribusis' => DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])
                ->latest()
                ->paginate(10),
        ])->layout('layouts.app');
    }
}
