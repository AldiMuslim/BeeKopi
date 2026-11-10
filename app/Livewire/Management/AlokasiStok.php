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

    // Form Properties
    public $lapak_id = '';
    public $catatan = '';
    public $search_produk = '';
    public $jumlah_cup = []; // Array [produk_id => jumlah_cup]

    // Table Filter Properties
    public $filter_status = '';
    public $filter_lapak = '';
    public $search_distribusi = '';

    // Modal State
    public $selectedDistribusi = null;

    public function mount()
    {
        $this->initJumlahCup();
    }

    public function initJumlahCup()
    {
        $produks = Produk::where('status', 'aktif')->get();
        foreach ($produks as $p) {
            if (!isset($this->jumlah_cup[$p->id])) {
                $this->jumlah_cup[$p->id] = null;
            }
        }
    }

    public function getTotalCupDikirimProperty()
    {
        return array_sum(array_map(function ($val) {
            return is_numeric($val) && (int)$val > 0 ? (int)$val : 0;
        }, $this->jumlah_cup));
    }

    public function filterStatusTab($status)
    {
        $this->filter_status = $status;
        $this->resetPage();
    }

    public function resetFilterTable()
    {
        $this->reset(['filter_status', 'filter_lapak', 'search_distribusi']);
        $this->resetPage();
    }

    public function bukaDetail($id)
    {
        $this->selectedDistribusi = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])
            ->find($id);
    }

    public function tutupDetail()
    {
        $this->selectedDistribusi = null;
    }

    public function kirimStok()
    {
        $this->validate([
            'lapak_id' => 'required|exists:lapaks,id',
        ], [], [
            'lapak_id' => 'Lapak Tujuan',
        ]);

        $itemsDikirim = array_filter($this->jumlah_cup, function ($qty) {
            return is_numeric($qty) && (int)$qty > 0;
        });

        if (empty($itemsDikirim)) {
            session()->flash('error', 'Masukkan minimal 1 jumlah cup produk yang akan dikirim!');
            return;
        }

        try {
            DB::transaction(function () use ($itemsDikirim) {
                // Check Lock Database stok_pusat
                foreach ($itemsDikirim as $produkId => $qty) {
                    $produk = Produk::where('id', $produkId)->lockForUpdate()->firstOrFail();
                    if ($produk->stok_pusat < $qty) {
                        throw new \Exception("Stok Gudang Pusat untuk {$produk->nama_produk} tidak mencukupi! (Sisa Stok: {$produk->stok_pusat} cup)");
                    }
                }

                $kodeDistribusi = 'DST-' . now()->format('YmdHis') . '-' . rand(100, 999);
                $distribusi = DistribusiStok::create([
                    'kode_distribusi' => $kodeDistribusi,
                    'lapak_id'        => $this->lapak_id,
                    'user_id'         => Auth::id(),
                    'tanggal'         => now()->toDateString(),
                    'catatan'         => $this->catatan,
                    'status'          => 'pending',
                ]);

                foreach ($itemsDikirim as $produkId => $qty) {
                    DistribusiStokDetail::create([
                        'distribusi_stok_id' => $distribusi->id,
                        'produk_id'          => $produkId,
                        'jumlah_cup'         => $qty,
                    ]);

                    Produk::where('id', $produkId)->decrement('stok_pusat', $qty);
                }
            });

            session()->flash('message', 'Alokasi pengiriman stok berhasil diterbitkan ke armada lapak!');
            $this->reset(['lapak_id', 'catatan', 'search_produk']);
            $this->initJumlahCup();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function batalkanPengiriman($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $distribusi = DistribusiStok::with('details')
                    ->where('id', $id)
                    ->where('status', 'pending')
                    ->firstOrFail();

                foreach ($distribusi->details as $detail) {
                    Produk::where('id', $detail->produk_id)->increment('stok_pusat', $detail->jumlah_cup);
                }

                $distribusi->delete();
            });

            if ($this->selectedDistribusi && $this->selectedDistribusi->id == $id) {
                $this->tutupDetail();
            }

            session()->flash('message', 'Pengiriman stok berhasil dibatalkan dan stok dikembalikan ke Gudang Pusat.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membatalkan pengiriman: ' . $e->getMessage());
        }
    }

    public function prosesReturStokDitolak($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $distribusi = DistribusiStok::with('details')
                    ->where('id', $id)
                    ->where('status', 'ditolak')
                    ->firstOrFail();

                foreach ($distribusi->details as $detail) {
                    Produk::where('id', $detail->produk_id)->increment('stok_pusat', $detail->jumlah_cup);
                }

                $distribusi->update([
                    'status'  => 'retur_selesai',
                    'catatan' => $distribusi->catatan . ' (Stok ditolak telah diretur kembali ke Gudang Pusat)',
                ]);
            });

            if ($this->selectedDistribusi && $this->selectedDistribusi->id == $id) {
                $this->bukaDetail($id);
            }

            session()->flash('message', 'Stok yang ditolak Sales berhasil dikembalikan (restok) ke Gudang Pusat!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses retur stok: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Live Filter Produk Form
        $produks = Produk::where('status', 'aktif')
            ->when($this->search_produk, function ($q) {
                $q->where('nama_produk', 'like', '%' . $this->search_produk . '%')
                    ->orWhere('kategori', 'like', '%' . $this->search_produk . '%');
            })
            ->get();

        // Counter Badges Status
        $countTransit  = DistribusiStok::where('status', 'pending')->count();
        $countDiterima = DistribusiStok::where('status', 'diterima')->count();
        $countDitolak  = DistribusiStok::whereIn('status', ['ditolak', 'retur_selesai'])->count();

        // Query Histori Pengiriman
        $distribusis = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])
            ->when($this->filter_status, function ($q) {
                if ($this->filter_status === 'ditolak') {
                    $q->whereIn('status', ['ditolak', 'retur_selesai']);
                } else {
                    $q->where('status', $this->filter_status);
                }
            })
            ->when($this->filter_lapak, fn($q) => $q->where('lapak_id', $this->filter_lapak))
            ->when($this->search_distribusi, fn($q) => $q->where('kode_distribusi', 'like', '%' . $this->search_distribusi . '%'))
            ->latest()
            ->paginate(10);

        return view('livewire.management.alokasi-stok', [
            'lapaks'        => Lapak::all(),
            'produks'       => $produks,
            'distribusis'   => $distribusis,
            'countTransit'  => $countTransit,
            'countDiterima' => $countDiterima,
            'countDitolak'  => $countDitolak,
        ])->layout('layouts.app');
    }
}
