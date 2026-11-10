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

    public $catatan_penerimaan = [];
    public $catatan_penolakan = [];

    public function mount()
    {
        if (!Auth::user()->lapak_id) {
            session()->flash('error', 'Akun Anda belum dipetakan ke armada lapak manapun.');
        }
    }

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

                // 2. Perbarui status distribusi & simpan catatan penerimaan
                $distribusi->update([
                    'status' => 'diterima',
                    'penerima_id' => Auth::id(),
                    'catatan_penerimaan' => $this->catatan_penerimaan[$id] ?? null,
                ]);
            });

            unset($this->catatan_penerimaan[$id], $this->catatan_penolakan[$id]);
            session()->flash('message', 'Pengiriman stok berhasil diterima dan ditambahkan ke stok lapak!');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses penerimaan stok: ' . $e->getMessage());
        }
    }

    public function tolakStok($id)
    {
        $lapakId = Auth::user()->lapak_id;

        $alasan = trim($this->catatan_penolakan[$id] ?? '');
        if (empty($alasan)) {
            session()->flash('error', 'Harap isi alasan penolakan terlebih dahulu sebelum menolak kiriman stok.');
            return;
        }

        try {
            DB::transaction(function () use ($id, $lapakId, $alasan) {
                $distribusi = DistribusiStok::with('details')
                    ->where('id', $id)
                    ->where('lapak_id', $lapakId)
                    ->where('status', 'pending')
                    ->firstOrFail();

                // Kembalikan stok ke Gudang Pusat jika ditolak
                foreach ($distribusi->details as $detail) {
                    Produk::where('id', $detail->produk_id)->increment('stok_pusat', $detail->jumlah_cup);
                }

                // Simpan status ditolak beserta alasan penolakan sales
                $distribusi->update([
                    'status' => 'ditolak',
                    'penerima_id' => Auth::id(),
                    'catatan_penolakan' => $alasan,
                ]);
            });

            unset($this->catatan_penerimaan[$id], $this->catatan_penolakan[$id]);
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
