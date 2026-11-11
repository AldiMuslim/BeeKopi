<?php

namespace App\Livewire\Produksi;

use App\Models\AuditLog;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Resep;
use App\Models\RiwayatBahanBaku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class KelolaBahanBaku extends Component
{
    use WithPagination;

    // Filter & Search Master Table
    public $search = '';
    public $filter_stok = 'semua'; // 'semua', 'aman', 'menipis'

    // State Form Master Bahan Baku
    public $showModalBahan = false;
    public $bahan_id = null;
    public $nama_bahan = '';
    public $kategori = 'Bahan Basah';
    public $stok_sekarang = 0;
    public $stok_minimum = 0;
    public $satuan = 'gram';
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

    /**
     * Konversi nilai antar satuan untuk sinkronisasi HPP Resep
     */
    private function konversiNilaiStok($jumlah, $fromSatuan, $toSatuan): float
    {
        $from = strtolower(trim($fromSatuan ?? ''));
        $to   = strtolower(trim($toSatuan ?? ''));

        if ($from === $to || empty($from) || empty($to)) {
            return (float) $jumlah;
        }
        if ($from === 'kg' && in_array($to, ['gram', 'gr', 'g'])) {
            return (float) $jumlah * 1000;
        }
        if (in_array($from, ['gram', 'gr', 'g']) && $to === 'kg') {
            return (float) $jumlah / 1000;
        }
        if (in_array($from, ['liter', 'l']) && $to === 'ml') {
            return (float) $jumlah * 1000;
        }
        if ($from === 'ml' && in_array($to, ['liter', 'l'])) {
            return (float) $jumlah / 1000;
        }

        return (float) $jumlah;
    }

    /**
     * Sinkronisasi ulang nilai HPP pada semua produk yang menggunakan bahan baku ini
     */
    private function sinkronisasiHppProdukTerkait($bahanBakuId)
    {
        if (!Schema::hasColumn('produks', 'hpp')) {
            return;
        }

        $produks = Produk::with('reseps.bahanBaku')
            ->whereHas('reseps', fn($q) => $q->where('bahan_baku_id', $bahanBakuId))
            ->get();

        foreach ($produks as $produk) {
            $totalHpp = 0;
            foreach ($produk->reseps as $rsp) {
                $bahan = $rsp->bahanBaku;
                if (!$bahan) continue;

                $hargaBahan  = (float) ($bahan->harga_satuan ?? 0);
                $takaran     = (float) ($rsp->takaran_per_cup ?? $rsp->jumlah_takaran ?? 0);
                $satuanResep = $rsp->satuan ?? $bahan->satuan;

                if ($hargaBahan > 0 && $takaran > 0) {
                    $takaranGudang = $this->konversiNilaiStok($takaran, $satuanResep, $bahan->satuan);
                    $totalHpp += ($takaranGudang * $hargaBahan);
                }
            }

            $produk->update(['hpp' => round($totalHpp)]);
        }
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

    public function openModalBahan()
    {
        $this->reset(['bahan_id', 'nama_bahan', 'stok_sekarang', 'stok_minimum', 'harga_satuan']);
        $this->kategori = 'Bahan Basah';
        $this->satuan = 'gram';
        $this->resetValidation();
        $this->showModalBahan = true;
    }

    public function editBahan($id)
    {
        $this->resetValidation();
        $bahan = BahanBaku::findOrFail($id);
        $this->bahan_id = $bahan->id;
        $this->nama_bahan = $bahan->nama_bahan;
        $this->kategori = $bahan->kategori ?? 'Bahan Basah';
        $this->stok_sekarang = $bahan->stok_sekarang;
        $this->stok_minimum = $bahan->stok_minimum;
        $this->satuan = $bahan->satuan;
        $this->harga_satuan = $bahan->harga_satuan;

        $this->showModalBahan = true;
    }

    public function simpanBahan()
    {
        $this->validate([
            'nama_bahan'    => 'required|string|max:255|unique:bahan_bakus,nama_bahan,' . $this->bahan_id,
            'kategori'      => 'required|string|max:255',
            'stok_sekarang' => 'required|numeric|min:0',
            'stok_minimum'  => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:50',
            'harga_satuan'  => 'required|numeric|min:0',
        ]);

        $isEdit = (bool) $this->bahan_id;

        DB::transaction(function () use ($isEdit) {
            $data = [
                'nama_bahan'   => $this->nama_bahan,
                'kategori'     => $this->kategori,
                'stok_minimum' => $this->stok_minimum,
                'satuan'       => $this->satuan,
                'harga_satuan' => $this->harga_satuan,
            ];

            // Jika tambah bahan baru, set stok awal (dan catat riwayat stok awal jika > 0)
            if (!$isEdit) {
                $data['stok_sekarang'] = $this->stok_sekarang;
            }

            $bahan = BahanBaku::updateOrCreate(['id' => $this->bahan_id], $data);

            if (!$isEdit && (float) $this->stok_sekarang > 0) {
                RiwayatBahanBaku::create([
                    'bahan_baku_id' => $bahan->id,
                    'user_id'       => auth()->id(),
                    'jenis'         => 'masuk',
                    'jumlah'        => (float) $this->stok_sekarang,
                    'satuan'        => $bahan->satuan,
                    'total_biaya'   => (float) $this->stok_sekarang * (float) $this->harga_satuan,
                    'keterangan'    => 'Stok Awal Pendaftaran Bahan Baru',
                ]);
            }

            // Sinkronisasi HPP Produk yang menggunakan bahan ini
            $this->sinkronisasiHppProdukTerkait($bahan->id);

            $this->catatAudit(
                $isEdit ? 'Update Master Bahan Gudang' : 'Tambah Bahan Baku Baru',
                ($isEdit ? 'Memperbarui' : 'Menambahkan') . " master bahan baku: {$this->nama_bahan} (Harga: Rp " . number_format((float) $this->harga_satuan, 0, ',', '.') . "/{$this->satuan})"
            );
        });

        session()->flash('message', $isEdit ? 'Master bahan baku & HPP resep berhasil diperbarui!' : 'Bahan baku baru berhasil ditambahkan!');
        $this->showModalBahan = false;
    }

    public function hapusBahan($id)
    {
        $bahan = BahanBaku::findOrFail($id);

        // 1. Cek apakah masih digunakan di Resep Produk
        if (Schema::hasTable('reseps') && Resep::where('bahan_baku_id', $id)->exists()) {
            session()->flash('error', "Gagal! Bahan '{$bahan->nama_bahan}' masih digunakan aktif dalam komposisi Resep Produk.");
            return;
        }

        // 2. Cek apakah sudah memiliki histori transaksi stok
        $hasLogs = RiwayatBahanBaku::where('bahan_baku_id', $id)->exists();
        if ($hasLogs) {
            session()->flash('error', "Bahan '{$bahan->nama_bahan}' memiliki histori riwayat transaksi stok dan tidak dapat dihapus permanen demi integritas audit.");
            return;
        }

        $nama = $bahan->nama_bahan;
        $bahan->delete();

        $this->catatAudit('Hapus Master Bahan Baku', "Menghapus master bahan baku: {$nama}");

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
        $this->resetValidation();
        if ($val === 'belanja' && $this->selectedBahan) {
            $this->harga_beli_satuan = $this->selectedBahan->harga_satuan;
        }
    }

    public function updateStok()
    {
        $rules = [
            'jenis_penyesuaian' => 'required|in:belanja,pemakaian,rusak',
            'jumlah_jumlah'     => 'required|numeric|min:0.01',
            'keterangan'        => $this->jenis_penyesuaian === 'rusak'
                ? 'required|string|max:255'
                : 'nullable|string|max:255',
        ];

        if ($this->jenis_penyesuaian === 'belanja') {
            $rules['harga_beli_satuan'] = 'required|numeric|min:0';
        }

        $this->validate($rules, [
            'keterangan.required' => 'Alasan wajib diisi untuk pencatatan bahan rusak / tumpah / kadaluarsa (waste).',
        ]);

        try {
            DB::transaction(function () {
                $bahan = BahanBaku::where('id', $this->selectedBahan->id)->lockForUpdate()->firstOrFail();
                $jumlah = (float) $this->jumlah_jumlah;

                if ($this->jenis_penyesuaian === 'belanja') {
                    $hargaBeli = (float) $this->harga_beli_satuan;
                    $totalBiaya = $jumlah * $hargaBeli;

                    // 1. Tambah Stok & Update Harga Satuan Master jika berubah
                    $bahan->increment('stok_sekarang', $jumlah);
                    if ((float) $bahan->harga_satuan !== $hargaBeli && $hargaBeli > 0) {
                        $bahan->update(['harga_satuan' => $hargaBeli]);
                        $this->sinkronisasiHppProdukTerkait($bahan->id);
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

                    $this->catatAudit(
                        'Belanja Bahan Baku Gudang',
                        "Input belanja bahan: {$bahan->nama_bahan} (+{$jumlah} {$bahan->satuan} @ Rp " . number_format($hargaBeli, 0, ',', '.') . ", Total: Rp " . number_format($totalBiaya, 0, ',', '.') . ")"
                    );

                    session()->flash('message', "Berhasil menambah stok {$bahan->nama_bahan} (+{$jumlah} {$bahan->satuan})!");
                } else {
                    // Penyesuaian Pemakaian / Rusak (- Out)
                    if ((float) $bahan->stok_sekarang < $jumlah) {
                        throw new \Exception("Stok {$bahan->nama_bahan} tidak mencukupi untuk dikurangi! (Tersedia: {$bahan->stok_sekarang} {$bahan->satuan})");
                    }

                    // 1. Potong Stok Bahan
                    $bahan->decrement('stok_sekarang', $jumlah);

                    $defaultKeterangan = $this->jenis_penyesuaian === 'rusak'
                        ? 'Bahan Rusak / Waste'
                        : 'Pemakaian Manual Produksi';

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

                    $judulAudit = $this->jenis_penyesuaian === 'rusak'
                        ? 'Pencatatan Bahan Rusak (Waste)'
                        : 'Pemakaian Manual Bahan Baku';

                    $this->catatAudit(
                        $judulAudit,
                        "{$bahan->nama_bahan} (-{$jumlah} {$bahan->satuan}) - Catatan: " . ($this->keterangan ?: $defaultKeterangan)
                    );

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
        $totalBahan   = BahanBaku::count();
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
            ->when($this->filter_stok === 'aman', function ($q) {
                $q->whereColumn('stok_sekarang', '>', 'stok_minimum');
            })
            ->latest()
            ->paginate(10);

        // Log Transaksi (Limited 25 terbaru)
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
