<?php

namespace App\Livewire\Produksi;

use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Resep;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\RiwayatBahanBaku;

class RacikKopi extends Component
{
    public $produk_id = '';
    public $jumlah_cup = 1;
    public $previewBahan = [];
    public $stokCukup = true;

    public $showModalVarian = false;
    public $isEditMode = false;
    public $selected_produk_id = null;

    public $nama_produk = '';
    public $kategori = 'Kopi';
    public $harga_jual = '';
    public $hpp = 0;

    public $komposisi = [];

    public function mount()
    {
        $this->initKomposisi();
    }

    public function initKomposisi()
    {
        $this->komposisi = [
            ['bahan_baku_id' => '', 'takaran_per_cup' => '', 'satuan' => 'gram']
        ];
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['produk_id', 'jumlah_cup'])) {
            $this->hitungPreviewBahan();
        }
    }

    /**
     * Konversi nilai antar satuan (kg <-> gram, liter <-> ml, pcs/buah/set)
     */
    public function konversiNilaiStok($jumlah, $fromSatuan, $toSatuan)
    {
        $from = strtolower(trim($fromSatuan));
        $to = strtolower(trim($toSatuan));

        if ($from === $to) {
            return $jumlah;
        }

        // Massa: kg -> gram
        if ($from === 'kg' && in_array($to, ['gram', 'gr', 'g'])) {
            return $jumlah * 1000;
        }
        // Massa: gram -> kg
        if (in_array($from, ['gram', 'gr', 'g']) && $to === 'kg') {
            return $jumlah / 1000;
        }

        // Volume: liter -> ml
        if (in_array($from, ['liter', 'l']) && $to === 'ml') {
            return $jumlah * 1000;
        }
        // Volume: ml -> liter
        if ($from === 'ml' && in_array($to, ['liter', 'l'])) {
            return $jumlah / 1000;
        }

        // Kemasan: pcs / buah / set
        if (in_array($from, ['pcs', 'buah', 'set']) && in_array($to, ['pcs', 'buah', 'set'])) {
            return $jumlah;
        }

        return $jumlah;
    }

    /**
     * Format tampilan angka dan satuan agar rapi
     */
    public function formatSatuan($jumlah, $satuan)
    {
        $satuanLower = strtolower(trim($satuan));

        if ($satuanLower === 'ml' && $jumlah >= 1000) {
            $val = $jumlah / 1000;
            return ($val == floor($val) ? number_format($val, 0, ',', '.') : number_format($val, 2, ',', '.')) . ' liter';
        }

        if (in_array($satuanLower, ['gram', 'gr', 'g']) && $jumlah >= 1000) {
            $val = $jumlah / 1000;
            return ($val == floor($val) ? number_format($val, 0, ',', '.') : number_format($val, 2, ',', '.')) . ' kg';
        }

        return ($jumlah == floor($jumlah) ? number_format($jumlah, 0, ',', '.') : number_format($jumlah, 2, ',', '.')) . ' ' . $satuan;
    }

    public function hitungPreviewBahan()
    {
        $this->previewBahan = [];
        $this->stokCukup = true;

        if (!$this->produk_id || $this->jumlah_cup < 1) {
            return;
        }

        $reseps = Resep::with('bahanBaku')->where('produk_id', $this->produk_id)->get();

        foreach ($reseps as $resep) {
            $bahan = $resep->bahanBaku;
            if (!$bahan) continue;

            $kebutuhanResep = $resep->takaran_per_cup * $this->jumlah_cup;

            // Samakan satuan stok gudang ke satuan resep untuk perbandingan
            $stokGudangSamaSatuan = $this->konversiNilaiStok(
                $bahan->stok_sekarang,
                $bahan->satuan,
                $resep->satuan
            );

            $cukup = $stokGudangSamaSatuan >= $kebutuhanResep;

            if (!$cukup) {
                $this->stokCukup = false;
            }

            $this->previewBahan[] = [
                'nama_bahan' => $bahan->nama_bahan,
                'kebutuhan_text' => $this->formatSatuan($kebutuhanResep, $resep->satuan),
                'stok_text' => $this->formatSatuan($bahan->stok_sekarang, $bahan->satuan),
                'cukup' => $cukup,
            ];
        }
    }

    public function openModalTambah()
    {
        $this->reset(['nama_produk', 'kategori', 'harga_jual', 'hpp', 'selected_produk_id']);
        $this->isEditMode = false;
        $this->kategori = 'Kopi';
        $this->initKomposisi();
        $this->showModalVarian = true;
    }

    public function openModalEdit($id)
    {
        $produk = Produk::with('reseps')->findOrFail($id);
        $this->selected_produk_id = $produk->id;
        $this->nama_produk = $produk->nama_produk;
        $this->kategori = $produk->kategori;
        $this->harga_jual = $produk->harga_jual;
        $this->hpp = $produk->hpp ?? 0;

        $this->komposisi = [];
        foreach ($produk->reseps as $resep) {
            $this->komposisi[] = [
                'bahan_baku_id' => $resep->bahan_baku_id,
                'takaran_per_cup' => $resep->takaran_per_cup,
                'satuan' => $resep->satuan,
            ];
        }

        if (empty($this->komposisi)) {
            $this->initKomposisi();
        }

        $this->isEditMode = true;
        $this->showModalVarian = true;
    }

    public function tambahRowKomposisi()
    {
        $this->komposisi[] = ['bahan_baku_id' => '', 'takaran_per_cup' => '', 'satuan' => 'gram'];
    }

    public function hapusRowKomposisi($index)
    {
        unset($this->komposisi[$index]);
        $this->komposisi = array_values($this->komposisi);
    }

    public function simpanVarian()
    {
        $this->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori' => 'required|string',
            'harga_jual' => 'required|numeric|min:0',
            'komposisi' => 'required|array|min:1',
            'komposisi.*.bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'komposisi.*.takaran_per_cup' => 'required|numeric|min:0.01',
            'komposisi.*.satuan' => 'required|string',
        ]);

        DB::transaction(function () {
            $produk = Produk::updateOrCreate(
                ['id' => $this->selected_produk_id],
                [
                    'nama_produk' => $this->nama_produk,
                    'kategori' => $this->kategori,
                    'harga_jual' => $this->harga_jual,
                    'hpp' => $this->hpp ?? 0,
                    'status' => 'aktif',
                ]
            );

            Resep::where('produk_id', $produk->id)->delete();

            foreach ($this->komposisi as $item) {
                Resep::create([
                    'produk_id' => $produk->id,
                    'bahan_baku_id' => $item['bahan_baku_id'],
                    'takaran_per_cup' => $item['takaran_per_cup'],
                    'satuan' => $item['satuan'],
                ]);
            }
        });

        session()->flash('message', $this->isEditMode ? 'Varian & resep berhasil diperbarui!' : 'Varian produk & resep baru berhasil ditambahkan!');
        $this->showModalVarian = false;
    }

    public function prosesRacik()
{
    $this->validate([
        'produk_id' => 'required|exists:produks,id',
        'jumlah_cup' => 'required|integer|min:1',
    ]);

    $this->hitungPreviewBahan();

    if (!$this->stokCukup) {
        session()->flash('error', 'Gagal meracik! Stok beberapa bahan baku tidak mencukupi.');
        return;
    }

    DB::transaction(function () {
        $produk = Produk::findOrFail($this->produk_id);
        $reseps = Resep::with('bahanBaku')->where('produk_id', $this->produk_id)->get();

        foreach ($reseps as $resep) {
            $bahan = $resep->bahanBaku;
            if (!$bahan) continue;

            $kebutuhanResep = $resep->takaran_per_cup * $this->jumlah_cup;

            // Konversi kebutuhan ke satuan yang tersimpan di tabel bahan_bakus
            $potongStokBahan = $this->konversiNilaiStok(
                $kebutuhanResep,
                $resep->satuan,
                $bahan->satuan
            );

            // 1. Potong Stok Bahan Baku Mentah
            BahanBaku::where('id', $bahan->id)->decrement('stok_sekarang', $potongStokBahan);

            // 2. Catat Log Pemakaian Otomatis (Auto-Log Stock Out)
            RiwayatBahanBaku::create([
                'bahan_baku_id' => $bahan->id,
                'user_id' => auth()->id(),
                'jenis' => 'keluar',
                'jumlah' => $potongStokBahan,
                'satuan' => $bahan->satuan,
                'total_biaya' => 0,
                'keterangan' => "Peracikan {$this->jumlah_cup} Cup {$produk->nama_produk}",
            ]);
        }

        // 3. Tambah Stok Produk Siap Jual di Gudang Pusat
        $produk->increment('stok_pusat', $this->jumlah_cup);
    });

    $produk = Produk::find($this->produk_id);
    session()->flash('message', "Berhasil meracik {$this->jumlah_cup} cup {$produk->nama_produk}! Stok produk bertambah, bahan baku terpotong, dan log pemakaian tercatat.");

    $this->reset(['produk_id', 'jumlah_cup', 'previewBahan']);
    $this->jumlah_cup = 1;
}

    public function render()
    {
        return view('livewire.produksi.racik-kopi', [
            'produks' => Produk::with('reseps.bahanBaku')->where('status', 'aktif')->get(),
            'allBahanBaku' => BahanBaku::orderBy('nama_bahan', 'asc')->get(),
        ])->layout('layouts.app');
    }
}
