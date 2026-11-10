<?php

namespace App\Livewire\Produksi;

use App\Models\AuditLog;
use App\Models\DistribusiStok;
use App\Models\DistribusiStokDetail;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PermintaanRestockLapak extends Component
{
    use WithPagination;

    // State Filter & Search
    public $search = '';
    public $statusFilter = '';
    public $kategoriFilter = '';
    public $tabFilter = 'semua'; // 'semua', 'pending', 'dalam_transit', 'selesai'

    // State Modal
    public $showModalDetail = false;
    public $showSuratJalan = false;
    public $selectedRequest = null;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function setTabFilter($tab)
    {
        $this->tabFilter = $tab;
        $this->resetPage();
    }

    public function openModalDetail($id)
    {
        $this->selectedRequest = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])->findOrFail($id);
        $this->showModalDetail = true;
    }

    public function openSuratJalan($id)
    {
        $this->selectedRequest = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])->findOrFail($id);
        $this->showSuratJalan = true;
    }

    /**
     * Konfirmasi Penyiapan Fisik Barang & Dispatch dari Gudang (Pending -> Dalam Transit)
     */
    public function konfirmasiSiapKirim($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $distribusi = DistribusiStok::with('details.produk')->where('id', $id)->lockForUpdate()->firstOrFail();

                if ($distribusi->status !== 'pending') {
                    throw new \Exception("Pengiriman #{$distribusi->kode_distribusi} sudah diproses sebelumnya.");
                }

                // Validasi Kecukupan Stok Pusat
                foreach ($distribusi->details as $detail) {
                    $qty = $detail->jumlah_cup ?? ($detail->jumlah ?? 0);
                    $produk = Produk::where('id', $detail->produk_id)->lockForUpdate()->first();

                    if (!$produk || $produk->stok_pusat < $qty) {
                        $sisa = $produk ? $produk->stok_pusat : 0;
                        throw new \Exception("Gagal Dispatch! Stok {$produk->nama_produk} di Gudang Pusat tidak mencukupi. (Dibutuhkan: {$qty} cup, Tersedia: {$sisa} cup)");
                    }
                }

                // Potong Stok Pusat & Update Status
                foreach ($distribusi->details as $detail) {
                    $qty = $detail->jumlah_cup ?? ($detail->jumlah ?? 0);
                    Produk::where('id', $detail->produk_id)->decrement('stok_pusat', $qty);
                }

                $distribusi->update([
                    'status' => 'dalam_transit',
                    'pengirim_id' => auth()->id(), // Catat operator gudang yang melepas fisik barang
                ]);

                AuditLog::create([
                    'user_id'   => auth()->id(),
                    'aktivitas' => "Gudang mengonfirmasi & melepaskan stok pengiriman #{$distribusi->kode_distribusi} ke Lapak {$distribusi->lapak->nama_lapak}",
                ]);
            });

            session()->flash('message', "Pengiriman #{$this->selectedRequest->kode_distribusi} berhasil dikonfirmasi dan status diperbarui menjadi Dalam Transit!");
            $this->showModalDetail = false;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        // Dashboard Metrics
        $totalStokPusat   = Produk::where('status', 'aktif')->sum('stok_pusat');
        $totalVarian      = Produk::where('status', 'aktif')->count();
        $stokKritisCount  = Produk::where('status', 'aktif')->where('stok_pusat', '<', 10)->count();
        $distribusiHariIni = DistribusiStokDetail::whereHas('distribusiStok', function ($q) {
            $q->whereDate('tanggal', now());
        })->sum('jumlah_cup');

        // Rincian Grid Varian Gudang Pusat
        $varians = Produk::where('status', 'aktif')
            ->when($this->kategoriFilter, fn($q) => $q->where('kategori', $this->kategoriFilter))
            ->orderBy('stok_pusat', 'asc')
            ->get();

        // Query Tabel Distribusi Stok
        $requests = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])
            ->when($this->search, function ($q) {
                $q->where('kode_distribusi', 'like', "%{$this->search}%")
                    ->orWhereHas('lapak', fn($sub) => $sub->where('nama_lapak', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->tabFilter === 'pending', fn($q) => $q->where('status', 'pending'))
            ->when($this->tabFilter === 'dalam_transit', fn($q) => $q->where('status', 'dalam_transit'))
            ->when($this->tabFilter === 'selesai', fn($q) => $q->whereIn('status', ['diterima', 'ditolak']))
            ->latest()
            ->paginate(10);

        return view('livewire.produksi.permintaan-restock-lapak', [
            'totalStokPusat'    => $totalStokPusat,
            'totalVarian'       => $totalVarian,
            'stokKritisCount'   => $stokKritisCount,
            'distribusiHariIni' => $distribusiHariIni,
            'varians'           => $varians,
            'requests'          => $requests,
        ])->layout('layouts.app');
    }
}
