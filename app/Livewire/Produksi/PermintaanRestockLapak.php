<?php

namespace App\Livewire\Produksi;

use App\Models\AuditLog;
use App\Models\DistribusiStok;
use App\Models\DistribusiStokDetail;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class PermintaanRestockLapak extends Component
{
    use WithPagination;

    // State Filter & Search
    public $search = '';
    public $statusFilter = '';
    public $kategoriFilter = '';
    public $onlyKritis = false; // Filter khusus varian < 10 cup
    public $tabFilter = 'semua'; // 'semua', 'pending', 'dalam_transit', 'selesai'

    // State Modal & Form Dispatch
    public $showModalDetail = false;
    public $showSuratJalan = false;
    public $selectedRequest = null;
    public $catatan_gudang = '';

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

    public function toggleFilterKritis()
    {
        $this->onlyKritis = !$this->onlyKritis;
    }

    /**
     * Helper pencatatan Audit Log yang mendukung kolom keterangan jika tersedia
     */
    private function catatAudit($judul, $rincian = null)
    {
        $data = [
            'user_id'   => auth()->id(),
            'aktivitas' => $rincian && !Schema::hasColumn('audit_logs', 'keterangan')
                ? "{$judul}: {$rincian}"
                : $judul,
        ];

        if ($rincian && Schema::hasColumn('audit_logs', 'keterangan')) {
            $data['keterangan'] = $rincian;
        }

        AuditLog::create($data);
    }

    public function openModalDetail($id)
    {
        $this->selectedRequest = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])->findOrFail($id);
        $this->catatan_gudang = $this->selectedRequest->catatan_gudang ?? '';
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
            $kodeDistribusi = '';

            DB::transaction(function () use ($id, &$kodeDistribusi) {
                $distribusi = DistribusiStok::with(['details.produk', 'lapak'])
                    ->where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $kodeDistribusi = $distribusi->kode_distribusi;

                if ($distribusi->status !== 'pending') {
                    throw new \Exception("Pengiriman #{$distribusi->kode_distribusi} sudah diproses sebelumnya.");
                }

                // Validasi Kecukupan Stok Pusat dengan Row Lock
                foreach ($distribusi->details as $detail) {
                    $qty = (int) ($detail->jumlah_cup ?? ($detail->jumlah ?? 0));
                    $produk = Produk::where('id', $detail->produk_id)->lockForUpdate()->first();

                    if (!$produk || $produk->stok_pusat < $qty) {
                        $sisa = $produk ? $produk->stok_pusat : 0;
                        $namaProduk = $produk ? $produk->nama_produk : 'Produk';
                        throw new \Exception("Gagal Dispatch! Stok {$namaProduk} di Gudang Pusat tidak mencukupi. (Dibutuhkan: {$qty} cup, Tersedia: {$sisa} cup)");
                    }
                }

                // Potong Stok Pusat & Update Status
                $totalCupDikirim = 0;
                foreach ($distribusi->details as $detail) {
                    $qty = (int) ($detail->jumlah_cup ?? ($detail->jumlah ?? 0));
                    $totalCupDikirim += $qty;
                    Produk::where('id', $detail->produk_id)->decrement('stok_pusat', $qty);
                }

                $updateData = [
                    'status'      => 'dalam_transit',
                    'pengirim_id' => auth()->id(), // Catat operator gudang yang melepas fisik barang
                ];

                if (Schema::hasColumn('distribusi_stoks', 'catatan_gudang')) {
                    $updateData['catatan_gudang'] = $this->catatan_gudang ?: null;
                }

        

                $namaLapak = $distribusi->lapak->nama_lapak ?? 'Lapak';
                $infoCatatan = $this->catatan_gudang ? " (Catatan Gudang: {$this->catatan_gudang})" : '';

                $this->catatAudit(
                    'Dispatch Pengiriman Stok',
                    "Gudang mengonfirmasi & mengirim #{$distribusi->kode_distribusi} ({$totalCupDikirim} cup) ke {$namaLapak}{$infoCatatan}"
                );
            });

            session()->flash('message', "Pengiriman #{$kodeDistribusi} berhasil dikonfirmasi dan status diperbarui menjadi Dalam Transit!");
            $this->showModalDetail = false;
            $this->catatan_gudang = '';
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $hasStatusProduk = Schema::hasColumn('produks', 'status');
        $colJumlahDetail = Schema::hasColumn('distribusi_stok_details', 'jumlah_cup') ? 'jumlah_cup' : 'jumlah';

        // Dashboard Metrics
        $baseProdukQuery = Produk::query()->when($hasStatusProduk, fn($q) => $q->where('status', 'aktif'));
        $totalStokPusat  = (clone $baseProdukQuery)->sum('stok_pusat');
        $totalVarian     = (clone $baseProdukQuery)->count();
        $stokKritisCount = (clone $baseProdukQuery)->where('stok_pusat', '<', 10)->count();

        $distribusiHariIni = DistribusiStokDetail::whereHas('distribusiStok', function ($q) {
            $q->whereDate('tanggal', now())
                ->whereIn('status', ['dalam_transit', 'diterima']);
        })->sum($colJumlahDetail);

        // Counter Badge per Tab Workflow
        $countPending = DistribusiStok::where('status', 'pending')->count();
        $countTransit = DistribusiStok::where('status', 'dalam_transit')->count();
        $countSelesai = DistribusiStok::whereIn('status', ['diterima', 'ditolak'])->count();

        // Rincian Grid Varian Gudang Pusat
        $varians = Produk::query()
            ->when($hasStatusProduk, fn($q) => $q->where('status', 'aktif'))
            ->when($this->kategoriFilter, fn($q) => $q->where('kategori', $this->kategoriFilter))
            ->when($this->onlyKritis, fn($q) => $q->where('stok_pusat', '<', 10))
            ->orderBy('stok_pusat', 'asc')
            ->get();

        // Query Tabel Distribusi Stok (dengan Grouped Closure Search)
        $requests = DistribusiStok::with(['lapak', 'pengirim', 'penerima', 'details.produk'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('kode_distribusi', 'like', "%{$this->search}%")
                        ->orWhereHas('lapak', fn($l) => $l->where('nama_lapak', 'like', "%{$this->search}%"));
                });
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
            'countPending'      => $countPending,
            'countTransit'      => $countTransit,
            'countSelesai'      => $countSelesai,
            'varians'           => $varians,
            'requests'          => $requests,
        ])->layout('layouts.app');
    }
}
