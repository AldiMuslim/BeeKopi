<?php

namespace App\Livewire\Produksi;

use App\Models\DistribusiStok;
use App\Models\DistribusiStokDetail;
use App\Models\Produk;
use Livewire\Component;
use Livewire\WithPagination;

class PermintaanRestockLapak extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $kategoriFilter = '';

    // State Modal Detail
    public $showModalDetail = false;
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

    public function openModalDetail($id)
    {
        $this->selectedRequest = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])->findOrFail($id);
        $this->showModalDetail = true;
    }

    public function render()
    {
        // Metric Dashboard Produksi
        $totalStokPusat = Produk::where('status', 'aktif')->sum('stok_pusat');
        $totalVarian = Produk::where('status', 'aktif')->count();
        $stokKritisCount = Produk::where('status', 'aktif')->where('stok_pusat', '<', 10)->count();
        $distribusiHariIni = DistribusiStokDetail::whereHas('distribusiStok', function ($q) {
            $q->whereDate('tanggal', now());
        })->sum('jumlah_cup');

        // List Rincian Varian Produk Siap Jual di Gudang Pusat
        $varians = Produk::where('status', 'aktif')
            ->when($this->kategoriFilter, function ($query) {
                $query->where('kategori', $this->kategoriFilter);
            })
            ->orderBy('stok_pusat', 'asc')
            ->get();

        $requests = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])
            ->when($this->search, function ($query) {
                $query->where('kode_distribusi', 'like', "%{$this->search}%")
                    ->orWhereHas('lapak', fn($q) => $q->where('nama_lapak', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.produksi.permintaan-restock-lapak', [
            'totalStokPusat' => $totalStokPusat,
            'totalVarian' => $totalVarian,
            'stokKritisCount' => $stokKritisCount,
            'distribusiHariIni' => $distribusiHariIni,
            'varians' => $varians,
            'requests' => $requests,
        ])->layout('layouts.app');
    }
}
