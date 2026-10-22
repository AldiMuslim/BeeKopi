<?php

namespace App\Livewire\Sales;

use App\Models\DistribusiStok;
use App\Models\Produk;
use App\Models\StokLapak;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PenerimaanStok extends Component
{
    use WithPagination;

    public function terimaStok($id)
    {
        $lapakId = Auth::user()->lapak_id;

        try {
            DB::transaction(function () use ($id, $lapakId) {
                $distribusi = DistribusiStok::with('details')
                    ->where('id', $id)
                    ->where('lapak_id', $lapakId)
                    ->where('status', 'pending')
                    ->firstOrFail();

                // 1. Tambah stok di tabel stok_lapaks
                foreach ($distribusi->details as $detail) {
                    $stokLapak = StokLapak::firstOrCreate(
                        [
                            'lapak_id' => $lapakId,
                            'produk_id' => $detail->produk_id,
                        ],
                        [
                            'jumlah_stok' => 0,
                            'stok_minimum' => 5,
                        ]
                    );

                    $stokLapak->increment('jumlah_stok', $detail->jumlah_cup);
                }

                // 2. Perbarui status distribusi
                $distribusi->update([
                    'status' => 'diterima',
                    'penerima_id' => Auth::id(),
                ]);
            });

            session()->flash('message', 'Pengiriman stok berhasil diterima dan ditambahkan ke stok lapak!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses penerimaan stok: ' . $e->getMessage());
        }
    }

    public function tolakStok($id)
    {
        $lapakId = Auth::user()->lapak_id;

        try {
            DB::transaction(function () use ($id, $lapakId) {
                $distribusi = DistribusiStok::with('details')
                    ->where('id', $id)
                    ->where('lapak_id', $lapakId)
                    ->where('status', 'pending')
                    ->firstOrFail();

                // Kembalikan stok ke Gudang Pusat jika ditolak
                foreach ($distribusi->details as $detail) {
                    Produk::where('id', $detail->produk_id)->increment('stok_pusat', $detail->jumlah_cup);
                }

                $distribusi->update([
                    'status' => 'ditolak',
                    'penerima_id' => Auth::id(),
                ]);
            });

            session()->flash('message', 'Pengiriman stok ditolak dan dikembalikan ke Gudang Pusat.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menolak pengiriman stok: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $lapakId = Auth::user()->lapak_id;

        $pengirimanPending = DistribusiStok::with(['pengirim', 'details.produk'])
            ->where('lapak_id', $lapakId)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $riwayatPenerimaan = DistribusiStok::with(['pengirim', 'penerima', 'details.produk'])
            ->where('lapak_id', $lapakId)
            ->whereIn('status', ['diterima', 'ditolak'])
            ->latest()
            ->paginate(10);

        return view('livewire.sales.penerimaan-stok', [
            'pengirimanPending' => $pengirimanPending,
            'riwayatPenerimaan' => $riwayatPenerimaan,
        ])->layout('layouts.app');
    }
}