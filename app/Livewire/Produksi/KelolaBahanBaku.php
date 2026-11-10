<?php

namespace App\Livewire\Produksi;

use App\Models\AuditLog;
use App\Models\BahanBaku;
use App\Models\RiwayatBahanBaku;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class KelolaBahanBaku extends Component
{
    use WithPagination;

    // Filter & Search Master Table
    public $search = '';
    public $filter_stok = 'semua'; // 'semua', 'menipis'

    // State Form Master Bahan Baku
    public $showModalBahan = false;
    public $bahan_id = null;
    public $nama_bahan = '';
    public $kategori = '';
    public $stok_sekarang = 0;
    public $stok_minimum = 0;
    public $satuan = '';
    public $harga_satuan = 0;

    // State Form Update Stok & Log
    public $showModalStok = false;
    public $selectedBahan = null;
    public $jenis_penyesuaian = 'belanja'; // 'belanja', 'pemakaian', atau 'rusak'
    public $jumlah_jumlah = '';
    public $harga_beli_satuan = 0; // Dynamic purchase price
    public $keterangan = '';

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function filterStokTab($status)
    {
        $this->filter_stok = $status;
        $this->resetPage();
    }

    public function openModalBahan()
    {
        $this->reset(['bahan_id', 'nama_bahan', 'kategori', 'stok_sekarang', 'stok_minimum', 'satuan', 'harga_satuan']);
        $this->resetValidation();
        $this->showModalBahan = true;
    }

    public function editBahan($id)
    {
        $this->resetValidation();
        $bahan = BahanBaku::findOrFail($id);
        $this->bahan_id = $bahan->id;
        $this->nama_bahan = $bahan->nama_bahan;
        $this->kategori = $bahan->kategori;
        $this->stok_sekarang = $bahan->stok_sekarang;
        $this->stok_minimum = $bahan->stok_minimum;
        $this->satuan = $bahan->satuan;
        $this->harga_satuan = $bahan->harga_satuan;

        $this->showModalBahan = true;
    }

    public function simpanBahan()
    {
        $this->validate([
            'nama_bahan'    => 'required|string|max:255',
            'kategori'      => 'required|string|max:255',
            'stok_sekarang' => 'required|numeric|min:0',
            'stok_minimum'  => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:50',
            'harga_satuan'  => 'required|numeric|min:0',
        ]);

        BahanBaku::updateOrCreate(
            ['id' => $this->bahan_id],
            [
                'nama_bahan'    => $this->nama_bahan,
                'kategori'      => $this->kategori,
                'stok_sekarang' => $this->stok_sekarang,
                'stok_minimum'  => $this->stok_minimum,
                'satuan'        => $this->satuan,
                'harga_satuan'  => $this->harga_satuan,
            ]
        );

        AuditLog::create([
            'user_id'   => auth()->id(),
            'aktivitas' => $this->bahan_id
                ? "Memperbarui master bahan baku: {$this->nama_bahan}"
                : "Menambah master bahan baku baru: {$this->nama_bahan}",
        ]);

        session()->flash('message', $this->bahan_id ? 'Bahan baku berhasil diperbarui!' : 'Bahan baku baru berhasil ditambahkan!');
        $this->showModalBahan = false;
    }

    public function hapusBahan($id)
    {
        $bahan = BahanBaku::findOrFail($id);

        // Check Integrity Limit
        $hasLogs = RiwayatBahanBaku::where('bahan_baku_id', $id)->exists();
        if ($hasLogs) {
            session()->flash('error', "Bahan '{$bahan->nama_bahan}' memiliki histori riwayat transaksi stok dan tidak dapat dihapus permanen demi integritas audit.");
            return;
        }

        $nama = $bahan->nama_bahan;
        $bahan->delete();

        AuditLog::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Menghapus master bahan baku: {$nama}",
        ]);

        session()->flash('message', "Bahan baku '{$nama}' telah dihapus.");
    }

    public function openModalStok($id)
    {
        $this->resetValidation();
        $this->selectedBahan = BahanBaku::findOrFail($id);
        $this->jenis_penyesuaian = 'belanja';
        $this->jumlah_jumlah = '';
        $this->harga_beli_satuan = $this->selectedBahan->harga_satuan;
        $this->keterangan = '';
        $this->showModalStok = true;
    }

    public function updatedJenisPenyesuaian($val)
    {
        if ($val === 'belanja' && $this->selectedBahan) {
            $this->harga_beli_satuan = $this->selectedBahan->harga_satuan;
        }
    }

    public function updateStok()
    {
        $rules = [
            'jenis_penyesuaian' => 'required|in:belanja,pemakaian,rusak',
            'jumlah_jumlah'     => 'required|numeric|min:0.01',
            'keterangan'        => 'nullable|string|max:255',
        ];

        if ($this->jenis_penyesuaian === 'belanja') {
            $rules['harga_beli_satuan'] = 'required|numeric|min:0';
        }

        $this->validate($rules);

        try {
            DB::transaction(function () {
                $bahan = BahanBaku::where('id', $this->selectedBahan->id)->lockForUpdate()->firstOrFail();
                $jumlah = (float) $this->jumlah_jumlah;

                if ($this->jenis_penyesuaian === 'belanja') {
                    $hargaBeli = (float) $this->harga_beli_satuan;
                    $totalBiaya = $jumlah * $hargaBeli;

                    // 1. Tambah Stok & Update Harga Satuan Master jika berubah
                    $bahan->increment('stok_sekarang', $jumlah);
                    if ($bahan->harga_satuan != $hargaBeli) {
                        $bahan->update(['harga_satuan' => $hargaBeli]);
                    }

                    // 2. Simpan Log Belanja (+ In)
                    RiwayatBahanBaku::create([
                        'bahan_baku_id' => $bahan->id,
                        'user_id'       => auth()->id(),
                        'jenis'         => 'masuk',
                        'jumlah'        => $jumlah,
                        'satuan'        => $bahan->satuan,
                        'total_biaya'   => $totalBiaya,
                        'keterangan'    => $this->keterangan ?: 'Restok Belanja Harian',
                    ]);

                    AuditLog::create([
                        'user_id'   => auth()->id(),
                        'aktivitas' => "Input belanja bahan: {$bahan->nama_bahan} (+{$jumlah} {$bahan->satuan} @ Rp " . number_format($hargaBeli, 0, ',', '.') . ")",
                    ]);

                    session()->flash('message', "Berhasil menambah stok {$bahan->nama_bahan} (+{$jumlah} {$bahan->satuan})!");
                } else {
                    // Penyesuaian Pemakaian / Rusak (- Out)
                    if ($bahan->stok_sekarang < $jumlah) {
                        throw new \Exception("Stok {$bahan->nama_bahan} tidak mencukupi untuk dikurangi! (Tersedia: {$bahan->stok_sekarang} {$bahan->satuan})");
                    }

                    // 1. Potong Stok Bahan
                    $bahan->decrement('stok_sekarang', $jumlah);

                    $defaultKeterangan = $this->jenis_penyesuaian === 'rusak' ? 'Bahan Rusak / Waste' : 'Pemakaian Manual Produksi';

                    // 2. Simpan Log Pemakaian (- Out)
                    RiwayatBahanBaku::create([
                        'bahan_baku_id' => $bahan->id,
                        'user_id'       => auth()->id(),
                        'jenis'         => 'keluar',
                        'jumlah'        => $jumlah,
                        'satuan'        => $bahan->satuan,
                        'total_biaya'   => 0,
                        'keterangan'    => $this->keterangan ?: $defaultKeterangan,
                    ]);

                    AuditLog::create([
                        'user_id'   => auth()->id(),
                        'aktivitas' => "Pencatatan {$this->jenis_penyesuaian}: {$bahan->nama_bahan} (-{$jumlah} {$bahan->satuan})",
                    ]);

                    session()->flash('message', "Berhasil mengurangi stok {$bahan->nama_bahan} (-{$jumlah} {$bahan->satuan})!");
                }
            });

            $this->showModalStok = false;
            $this->selectedBahan = null;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        // Counter Ringkasan Stok
        $totalBahan  = BahanBaku::count();
        $countMenipis = BahanBaku::whereColumn('stok_sekarang', '<=', 'stok_minimum')->count();
        $countAman    = $totalBahan - $countMenipis;

        // Query Bahan Baku
        $bahanBakus = BahanBaku::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nama_bahan', 'like', "%{$this->search}%")
                        ->orWhere('kategori', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filter_stok === 'menipis', function ($q) {
                $q->whereColumn('stok_sekarang', '<=', 'stok_minimum');
            })
            ->latest()
            ->paginate(10);

        // Log Transaksi (Paginated / Limited)
        $logPemakaian = RiwayatBahanBaku::with(['bahanBaku', 'user'])
            ->where('jenis', 'keluar')
            ->latest()
            ->take(25)
            ->get();

        $logBelanja = RiwayatBahanBaku::with(['bahanBaku', 'user'])
            ->where('jenis', 'masuk')
            ->latest()
            ->take(25)
            ->get();

        return view('livewire.produksi.kelola-bahan-baku', [
            'bahanBakus'   => $bahanBakus,
            'logPemakaian' => $logPemakaian,
            'logBelanja'   => $logBelanja,
            'totalBahan'   => $totalBahan,
            'countMenipis' => $countMenipis,
            'countAman'    => $countAman,
        ])->layout('layouts.app');
    }
}
