<?php

namespace App\Livewire\Produksi;

use App\Models\AuditLog;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Resep;
use App\Models\RiwayatBahanBaku;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RacikKopi extends Component
{
    // Form Execution State
    public $produk_id = '';
    public $jumlah_cup = 1;
    public $previewBahan = [];
    public $stokCukup = true;
    public $maxBatchCapacity = 0;

    // Filter & Search
    public $searchVarian = '';

    // Modal Varian & Resep State
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
        $to   = strtolower(trim($toSatuan));

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

        // Kemasan / Unit: pcs / buah / set
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
        $this->maxBatchCapacity = 0;

        if (!$this->produk_id || $this->jumlah_cup < 1) {
            return;
        }

        $reseps = Resep::with('bahanBaku')->where('produk_id', $this->produk_id)->get();

        if ($reseps->isEmpty()) {
            $this->stokCukup = false;
            return;
        }

        $capacities = [];

        foreach ($reseps as $resep) {
            $bahan = $resep->bahanBaku;
            if (!$bahan) continue;

            $kebutuhanResep = (float)$resep->takaran_per_cup * (int)$this->jumlah_cup;

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

            // Hitung kapasitas racik maksimal dari stok bahan ini
            if ((float)$resep->takaran_per_cup > 0) {
                $maxForThisIngredient = floor($stokGudangSamaSatuan / (float)$resep->takaran_per_cup);
                $capacities[] = max(0, $maxForThisIngredient);
            }

            $this->previewBahan[] = [
                'nama_bahan'     => $bahan->nama_bahan,
                'kebutuhan_text' => $this->formatSatuan($kebutuhanResep, $resep->satuan),
                'stok_text'      => $this->formatSatuan($bahan->stok_sekarang, $bahan->satuan),
                'cukup'          => $cukup,
            ];
        }

        $this->maxBatchCapacity = !empty($capacities) ? min($capacities) : 0;
    }

    public function openModalTambah()
    {
        $this->reset(['nama_produk', 'kategori', 'harga_jual', 'hpp', 'selected_produk_id']);
        $this->resetValidation();
        $this->isEditMode = false;
        $this->kategori = 'Kopi';
        $this->initKomposisi();
        $this->showModalVarian = true;
    }

    public function openModalEdit($id)
    {
        $this->resetValidation();
        $produk = Produk::with('reseps')->findOrFail($id);
        $this->selected_produk_id = $produk->id;
        $this->nama_produk = $produk->nama_produk;
        $this->kategori = $produk->kategori;
        $this->harga_jual = $produk->harga_jual;
        $this->hpp = $produk->hpp ?? 0;

        $this->komposisi = [];
        foreach ($produk->reseps as $resep) {
            $this->komposisi[] = [
                'bahan_baku_id'   => $resep->bahan_baku_id,
                'takaran_per_cup' => $resep->takaran_per_cup,
                'satuan'          => $resep->satuan,
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
            'nama_produk'                => 'required|string|max:255',
            'kategori'                   => 'required|string',
            'harga_jual'                 => 'required|numeric|min:0',
            'komposisi'                  => 'required|array|min:1',
            'komposisi.*.bahan_baku_id'   => 'required|exists:bahan_bakus,id',
            'komposisi.*.takaran_per_cup' => 'required|numeric|min:0.01',
            'komposisi.*.satuan'          => 'required|string',
        ]);

        DB::transaction(function () {
            // Kalkulasi HPP otomatis dari harga satuan bahan baku
            $calculatedHpp = 0;
            foreach ($this->komposisi as $item) {
                $bahan = BahanBaku::find($item['bahan_baku_id']);
                if ($bahan && $bahan->harga_satuan > 0) {
                    $takaranDalamSatuanBahan = $this->konversiNilaiStok(
                        (float) $item['takaran_per_cup'],
                        $item['satuan'],
                        $bahan->satuan
                    );
                    $calculatedHpp += ($takaranDalamSatuanBahan * (float)$bahan->harga_satuan);
                }
            }

            $produk = Produk::updateOrCreate(
                ['id' => $this->selected_produk_id],
                [
                    'nama_produk' => $this->nama_produk,
                    'kategori'    => $this->kategori,
                    'harga_jual'  => $this->harga_jual,
                    'hpp'         => $calculatedHpp,
                    'status'      => 'aktif',
                ]
            );

            Resep::where('produk_id', $produk->id)->delete();

            foreach ($this->komposisi as $item) {
                Resep::create([
                    'produk_id'       => $produk->id,
                    'bahan_baku_id'   => $item['bahan_baku_id'],
                    'takaran_per_cup' => $item['takaran_per_cup'],
                    'satuan'          => $item['satuan'],
                ]);
            }

            AuditLog::create([
                'user_id'   => auth()->id(),
                'aktivitas' => $this->isEditMode
                    ? "Memperbarui resep varian: {$produk->nama_produk} (HPP: Rp " . number_format($calculatedHpp, 0, ',', '.') . ")"
                    : "Menambah varian resep baru: {$produk->nama_produk} (HPP: Rp " . number_format($calculatedHpp, 0, ',', '.') . ")",
            ]);
        });

        session()->flash('message', $this->isEditMode ? 'Varian & resep berhasil diperbarui!' : 'Varian produk & resep baru berhasil ditambahkan!');
        $this->showModalVarian = false;
    }

    public function prosesRacik()
    {
        $this->validate([
            'produk_id'  => 'required|exists:produks,id',
            'jumlah_cup' => 'required|integer|min:1',
        ]);

        $reseps = Resep::where('produk_id', $this->produk_id)->get();
        if ($reseps->isEmpty()) {
            session()->flash('error', 'Gagal meracik! Varian ini belum memiliki resep komposisi bahan baku.');
            return;
        }

        $this->hitungPreviewBahan();

        if (!$this->stokCukup) {
            session()->flash('error', 'Gagal meracik! Stok beberapa bahan baku mentah di gudang tidak mencukupi.');
            return;
        }

        try {
            DB::transaction(function () {
                $produk = Produk::where('id', $this->produk_id)->lockForUpdate()->firstOrFail();
                $reseps = Resep::with('bahanBaku')->where('produk_id', $this->produk_id)->get();

                foreach ($reseps as $resep) {
                    $bahan = BahanBaku::where('id', $resep->bahan_baku_id)->lockForUpdate()->first();
                    if (!$bahan) continue;

                    $kebutuhanResep = (float)$resep->takaran_per_cup * (int)$this->jumlah_cup;

                    // Konversi kebutuhan ke satuan yang tersimpan di tabel bahan_bakus
                    $potongStokBahan = $this->konversiNilaiStok(
                        $kebutuhanResep,
                        $resep->satuan,
                        $bahan->satuan
                    );

                    if ($bahan->stok_sekarang < $potongStokBahan) {
                        throw new \Exception("Stok bahan {$bahan->nama_bahan} mendadak berkurang dan tidak cukup!");
                    }

                    // 1. Potong Stok Bahan Baku Mentah
                    $bahan->decrement('stok_sekarang', $potongStokBahan);

                    // 2. Catat Log Pemakaian Otomatis (Auto-Log Stock Out)
                    RiwayatBahanBaku::create([
                        'bahan_baku_id' => $bahan->id,
                        'user_id'       => auth()->id(),
                        'jenis'         => 'keluar',
                        'jumlah'        => $potongStokBahan,
                        'satuan'        => $bahan->satuan,
                        'total_biaya'   => 0,
                        'keterangan'    => "Peracikan Batch {$this->jumlah_cup} Cup {$produk->nama_produk}",
                    ]);
                }

                // 3. Tambah Stok Produk Siap Jual di Gudang Pusat
                $produk->increment('stok_pusat', $this->jumlah_cup);

                AuditLog::create([
                    'user_id'   => auth()->id(),
                    'aktivitas' => "Meracik batch produk: {$this->jumlah_cup} Cup {$produk->nama_produk}",
                ]);
            });

            $produk = Produk::find($this->produk_id);
            session()->flash('message', "Berhasil meracik {$this->jumlah_cup} cup {$produk->nama_produk}! Stok produk pusat bertambah, bahan baku terpotong, dan log pemakaian tercatat.");

            $this->reset(['produk_id', 'jumlah_cup', 'previewBahan', 'maxBatchCapacity']);
            $this->jumlah_cup = 1;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        // Query Varian Produk
        $produks = Produk::with('reseps.bahanBaku')
            ->where('status', 'aktif')
            ->when($this->searchVarian, function ($q) {
                $q->where('nama_produk', 'like', '%' . $this->searchVarian . '%')
                    ->orWhere('kategori', 'like', '%' . $this->searchVarian . '%');
            })
            ->get();

        // Histori Batch Peracikan (Filter Log Pemakaian khusus Peracikan)
        $historiPeracikan = RiwayatBahanBaku::with(['bahanBaku', 'user'])
            ->where('jenis', 'keluar')
            ->where('keterangan', 'like', 'Peracikan Batch%')
            ->latest()
            ->take(20)
            ->get();

        return view('livewire.produksi.racik-kopi', [
            'produks'          => $produks,
            'allBahanBaku'     => BahanBaku::orderBy('nama_bahan', 'asc')->get(),
            'historiPeracikan' => $historiPeracikan,
        ])->layout('layouts.app');
    }
}
