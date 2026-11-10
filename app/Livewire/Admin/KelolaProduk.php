<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Resep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class KelolaProduk extends Component
{
    // Filter & Search State
    public $search = '';
    public $filterKategori = '';
    public $filterStatus = '';

    // Modal Visibility State
    public $showModalProduk = false;
    public $showModalResep = false;

    // Form Produk State
    public $produk_id = null;
    public $nama_produk = '';
    public $kategori = 'Kopi';
    public $harga = 0;
    public $stok_pusat = 0;
    public $status = 'aktif';

    // Form Resep State
    public $selectedProduk = null;
    public $bahan_baku_id = '';
    public $jumlah_takaran = '';
    public $satuan_resep = 'gram';

    /**
     * Konversi nilai antar satuan (kg <-> gram, liter <-> ml, pcs/buah/set)
     */
    public function konversiNilaiStok($jumlah, $fromSatuan, $toSatuan)
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
     * Ambil nilai harga jual produk (mendukung kolom harga_jual maupun harga)
     */
    public function getHargaProduk($produk)
    {
        return (float) ($produk->harga_jual ?? $produk->harga ?? 0);
    }

    /**
     * Ambil nilai takaran resep (mendukung kolom takaran_per_cup maupun jumlah_takaran)
     */
    public function getTakaranResep($resep)
    {
        return (float) ($resep->takaran_per_cup ?? $resep->jumlah_takaran ?? 0);
    }

    /**
     * Ambil satuan resep (fallback ke satuan bahan baku jika kolom satuan tidak ada di reseps)
     */
    public function getSatuanResep($resep)
    {
        return $resep->satuan ?? ($resep->bahanBaku->satuan ?? 'gram');
    }

    /**
     * Hitung estimasi HPP per 1 cup berdasarkan komposisi resep
     */
    public function hitungHppProduk($produk)
    {
        $totalHpp = 0;

        foreach ($produk->reseps as $rsp) {
            $bahan = $rsp->bahanBaku;
            if (!$bahan) continue;

            $hargaSatuanBahan = (float) ($bahan->harga_satuan ?? $bahan->harga_beli ?? $bahan->harga ?? 0);
            $takaran = $this->getTakaranResep($rsp);
            $satuanResep = $this->getSatuanResep($rsp);

            if ($hargaSatuanBahan > 0 && $takaran > 0) {
                $takaranDalamSatuanGudang = $this->konversiNilaiStok(
                    $takaran,
                    $satuanResep,
                    $bahan->satuan
                );
                $totalHpp += ($takaranDalamSatuanGudang * $hargaSatuanBahan);
            }
        }

        return round($totalHpp);
    }

    /**
     * Simpan log ke tabel audit_logs secara aman sesuai kolom yang tersedia
     */
    private function catatLog($aktivitas, $keterangan = null)
    {
        $data = [
            'user_id'   => Auth::id(),
            'aktivitas' => $keterangan && !Schema::hasColumn('audit_logs', 'keterangan')
                ? "{$aktivitas}: {$keterangan}"
                : $aktivitas,
        ];

        if ($keterangan && Schema::hasColumn('audit_logs', 'keterangan')) {
            $data['keterangan'] = $keterangan;
        }

        AuditLog::create($data);
    }

    public function openModalProduk()
    {
        $this->resetInputProduk();
        $this->resetValidation();
        $this->showModalProduk = true;
    }

    public function editProduk($id)
    {
        $this->resetValidation();
        $produk = Produk::findOrFail($id);

        $this->produk_id   = $produk->id;
        $this->nama_produk = $produk->nama_produk;
        $this->kategori    = $produk->kategori ?? 'Kopi';
        $this->harga       = $this->getHargaProduk($produk);
        $this->stok_pusat  = $produk->stok_pusat ?? 0;
        $this->status      = $produk->status ?? 'aktif';

        $this->showModalProduk = true;
    }

    public function simpanProduk()
    {
        $this->validate([
            'nama_produk' => 'required|string|max:255|unique:produks,nama_produk,' . $this->produk_id,
            'kategori'    => 'required|string',
            'harga'       => 'required|numeric|min:0',
            'stok_pusat'  => 'required|integer|min:0',
        ]);

        $isEdit = (bool) $this->produk_id;
        $stokLama = 0;

        if ($isEdit) {
            $produkLama = Produk::find($this->produk_id);
            $stokLama = $produkLama ? (int) $produkLama->stok_pusat : 0;
        }

        $data = [
            'nama_produk' => $this->nama_produk,
            'kategori'    => $this->kategori,
            'stok_pusat'  => $this->stok_pusat,
        ];

        // Dukungan skema kolom harga_jual maupun harga
        if (Schema::hasColumn('produks', 'harga_jual')) {
            $data['harga_jual'] = $this->harga;
        }
        if (Schema::hasColumn('produks', 'harga')) {
            $data['harga'] = $this->harga;
        }
        if (Schema::hasColumn('produks', 'status')) {
            $data['status'] = $this->status;
        }

        $produk = Produk::updateOrCreate(['id' => $this->produk_id], $data);

        // Catat detail jika terjadi koreksi stok pusat manual oleh Admin
        if ($isEdit && $stokLama !== (int) $this->stok_pusat) {
            $this->catatLog(
                'Koreksi Stok Produk Pusat',
                "Mengubah produk {$produk->nama_produk} (Stok Pusat dikoreksi dari {$stokLama} cup menjadi {$this->stok_pusat} cup)"
            );
        } else {
            $this->catatLog(
                $isEdit ? 'Update Produk' : 'Tambah Produk Baru',
                ($isEdit ? 'Memperbarui' : 'Menambahkan') . " menu produk {$produk->nama_produk}"
            );
        }

        session()->flash('message', $isEdit ? 'Data produk berhasil diperbarui!' : 'Produk baru berhasil ditambahkan!');
        $this->showModalProduk = false;
        $this->resetInputProduk();
    }

    public function toggleStatus($id)
    {
        if (!Schema::hasColumn('produks', 'status')) {
            session()->flash('error', 'Kolom status belum tersedia pada tabel produks.');
            return;
        }

        $produk = Produk::findOrFail($id);
        $statusBaru = ($produk->status === 'nonaktif') ? 'aktif' : 'nonaktif';
        $produk->update(['status' => $statusBaru]);

        $this->catatLog(
            'Ubah Status Produk',
            "Mengubah status menu {$produk->nama_produk} menjadi {$statusBaru}"
        );

        session()->flash('message', "Status menu '{$produk->nama_produk}' berhasil diubah menjadi " . strtoupper($statusBaru) . ".");
    }

    public function duplikatProduk($id)
    {
        $produkAsli = Produk::with('reseps')->findOrFail($id);

        DB::transaction(function () use ($produkAsli) {
            $namaBaru = $produkAsli->nama_produk . ' (Copy)';
            $counter = 1;
            while (Produk::where('nama_produk', $namaBaru)->exists()) {
                $counter++;
                $namaBaru = $produkAsli->nama_produk . " (Copy {$counter})";
            }

            $dataProduk = [
                'nama_produk' => $namaBaru,
                'kategori'    => $produkAsli->kategori,
                'stok_pusat'  => 0,
            ];

            if (Schema::hasColumn('produks', 'harga_jual')) {
                $dataProduk['harga_jual'] = $this->getHargaProduk($produkAsli);
            }
            if (Schema::hasColumn('produks', 'harga')) {
                $dataProduk['harga'] = $this->getHargaProduk($produkAsli);
            }
            if (Schema::hasColumn('produks', 'hpp')) {
                $dataProduk['hpp'] = $produkAsli->hpp ?? 0;
            }
            if (Schema::hasColumn('produks', 'status')) {
                $dataProduk['status'] = 'aktif';
            }

            $produkBaru = Produk::create($dataProduk);

            foreach ($produkAsli->reseps as $rsp) {
                $dataResep = [
                    'produk_id'     => $produkBaru->id,
                    'bahan_baku_id' => $rsp->bahan_baku_id,
                ];

                if (Schema::hasColumn('reseps', 'takaran_per_cup')) {
                    $dataResep['takaran_per_cup'] = $this->getTakaranResep($rsp);
                }
                if (Schema::hasColumn('reseps', 'jumlah_takaran')) {
                    $dataResep['jumlah_takaran'] = $this->getTakaranResep($rsp);
                }
                if (Schema::hasColumn('reseps', 'satuan')) {
                    $dataResep['satuan'] = $this->getSatuanResep($rsp);
                }

                Resep::create($dataResep);
            }

            $this->catatLog('Duplikat Produk & Resep', "Menduplikat menu {$produkAsli->nama_produk} menjadi {$namaBaru}");
        });

        session()->flash('message', "Menu '{$produkAsli->nama_produk}' beserta komposisi resepnya berhasil diduplikat!");
    }

    public function openModalResep($produkId)
    {
        $this->resetValidation();
        $this->reset(['bahan_baku_id', 'jumlah_takaran']);
        $this->satuan_resep = 'gram';
        $this->selectedProduk = Produk::with('reseps.bahanBaku')->findOrFail($produkId);
        $this->showModalResep = true;
    }

    public function updatedBahanBakuId($value)
    {
        if ($value) {
            $bahan = BahanBaku::find($value);
            if ($bahan) {
                $satuanGudang = strtolower(trim($bahan->satuan));
                if ($satuanGudang === 'kg') $this->satuan_resep = 'gram';
                elseif (in_array($satuanGudang, ['liter', 'l'])) $this->satuan_resep = 'ml';
                else $this->satuan_resep = $bahan->satuan;
            }
        }
    }

    public function simpanResep()
    {
        $this->validate([
            'bahan_baku_id'  => 'required|exists:bahan_bakus,id',
            'jumlah_takaran' => 'required|numeric|min:0.01',
            'satuan_resep'   => 'required|string',
        ]);

        $dataUpdate = [];
        if (Schema::hasColumn('reseps', 'takaran_per_cup')) {
            $dataUpdate['takaran_per_cup'] = $this->jumlah_takaran;
        }
        if (Schema::hasColumn('reseps', 'jumlah_takaran')) {
            $dataUpdate['jumlah_takaran'] = $this->jumlah_takaran;
        }
        if (Schema::hasColumn('reseps', 'satuan')) {
            $dataUpdate['satuan'] = $this->satuan_resep;
        }

        Resep::updateOrCreate(
            [
                'produk_id'     => $this->selectedProduk->id,
                'bahan_baku_id' => $this->bahan_baku_id,
            ],
            $dataUpdate
        );

        $this->selectedProduk->load('reseps.bahanBaku');

        // Sinkronisasi ulang HPP Produk
        $hppBaru = $this->hitungHppProduk($this->selectedProduk);
        if (Schema::hasColumn('produks', 'hpp')) {
            $this->selectedProduk->update(['hpp' => $hppBaru]);
        }

        $this->catatLog(
            'Set Resep Produk',
            "Memperbarui komposisi resep produk: {$this->selectedProduk->nama_produk} (Estimasi HPP: Rp " . number_format($hppBaru, 0, ',', '.') . ")"
        );

        $this->reset(['bahan_baku_id', 'jumlah_takaran']);
        session()->flash('resep_message', 'Komposisi bahan berhasil disimpan dan HPP diperbarui!');
    }

    public function hapusResep($resepId)
    {
        Resep::destroy($resepId);
        $this->selectedProduk->load('reseps.bahanBaku');

        $hppBaru = $this->hitungHppProduk($this->selectedProduk);
        if (Schema::hasColumn('produks', 'hpp')) {
            $this->selectedProduk->update(['hpp' => $hppBaru]);
        }

        $this->catatLog(
            'Hapus Item Resep',
            "Menghapus bahan dari resep produk: {$this->selectedProduk->nama_produk}"
        );
    }

    private function resetInputProduk()
    {
        $this->reset(['produk_id', 'nama_produk', 'harga', 'stok_pusat']);
        $this->kategori = 'Kopi';
        $this->status = 'aktif';
    }

    public function render()
    {
        $query = Produk::with('reseps.bahanBaku');

        if ($this->search) {
            $query->where('nama_produk', 'like', '%' . $this->search . '%');
        }

        if ($this->filterKategori) {
            if ($this->filterKategori === 'Kopi') {
                $query->whereIn('kategori', ['Kopi', 'Coffee']);
            } elseif ($this->filterKategori === 'Non-Kopi') {
                $query->whereIn('kategori', ['Non-Kopi', 'Non-Coffee']);
            } else {
                $query->where('kategori', $this->filterKategori);
            }
        }

        if ($this->filterStatus && Schema::hasColumn('produks', 'status')) {
            $query->where('status', $this->filterStatus);
        }

        $produks = $query->orderBy('nama_produk', 'asc')->get();

        // Hitung Ringkasan Metrik Atas
        $allProduks = Produk::with('reseps')->get();
        $totalMenu = $allProduks->count();
        $menuTanpaResep = $allProduks->filter(fn($p) => $p->reseps->isEmpty())->count();
        $totalStokPusat = $allProduks->sum('stok_pusat');
        $estimasiValuasi = $allProduks->sum(fn($p) => ($p->stok_pusat ?? 0) * $this->getHargaProduk($p));

        return view('livewire.admin.kelola-produk', [
            'produks'         => $produks,
            'allBahanBaku'    => BahanBaku::orderBy('nama_bahan', 'asc')->get(),
            'totalMenu'       => $totalMenu,
            'menuTanpaResep'  => $menuTanpaResep,
            'totalStokPusat'  => $totalStokPusat,
            'estimasiValuasi' => $estimasiValuasi,
        ])->layout('layouts.app');
    }
}
