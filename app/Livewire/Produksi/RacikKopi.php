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

        // Otomatis sesuaikan satuan resep saat bahan baku dipilih di modal
        if (preg_match('/^komposisi\.(\d+)\.bahan_baku_id$/', $propertyName, $matches)) {
            $index = $matches[1];
            $bahanId = $this->komposisi[$index]['bahan_baku_id'] ?? null;
            if ($bahanId) {
                $bahan = BahanBaku::find($bahanId);
                if ($bahan) {
                    $satuanGudang = strtolower(trim($bahan->satuan));
                    if ($satuanGudang === 'kg') {
                        $this->komposisi[$index]['satuan'] = 'gram';
                    } elseif (in_array($satuanGudang, ['liter', 'l'])) {
                        $this->komposisi[$index]['satuan'] = 'ml';
                    } elseif (in_array($satuanGudang, ['gram', 'ml', 'pcs', 'set'])) {
                        $this->komposisi[$index]['satuan'] = $satuanGudang;
                    }
                }
            }
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

    /**
     * Konversi nilai antar satuan (kg <-> gram, liter <-> ml, pcs/buah/set)
     */
    public function konversiNilaiStok($jumlah, $fromSatuan, $toSatuan): float
    {
        $from = strtolower(trim($fromSatuan ?? ''));
        $to   = strtolower(trim($toSatuan ?? ''));

        if ($from === $to || empty($from) || empty($to)) {
            return (float) $jumlah;
        }

        // Massa: kg -> gram
        if ($from === 'kg' && in_array($to, ['gram', 'gr', 'g'])) {
            return (float) $jumlah * 1000;
        }
        // Massa: gram -> kg
        if (in_array($from, ['gram', 'gr', 'g']) && $to === 'kg') {
            return (float) $jumlah / 1000;
        }

        // Volume: liter -> ml
        if (in_array($from, ['liter', 'l']) && $to === 'ml') {
            return (float) $jumlah * 1000;
        }
        // Volume: ml -> liter
        if ($from === 'ml' && in_array($to, ['liter', 'l'])) {
            return (float) $jumlah / 1000;
        }

        // Kemasan / Unit: pcs / buah / set
        if (in_array($from, ['pcs', 'buah', 'set']) && in_array($to, ['pcs', 'buah', 'set'])) {
            return (float) $jumlah;
        }

        return (float) $jumlah;
    }

    /**
     * Format tampilan angka dan satuan agar rapi
     */
    public function formatSatuan($jumlah, $satuan): string
    {
        $jumlah = (float) $jumlah;
        $satuanLower = strtolower(trim($satuan ?? ''));

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

    /**
     * Hitung estimasi HPP secara langsung di dalam Modal saat menyusun resep
     */
    public function getEstimasiHppModalProperty(): float
    {
        $total = 0;
        foreach ($this->komposisi as $item) {
            if (empty($item['bahan_baku_id']) || (float) ($item['takaran_per_cup'] ?? 0) <= 0) {
                continue;
            }
            $bahan = BahanBaku::find($item['bahan_baku_id']);
            if ($bahan && (float) $bahan->harga_satuan > 0) {
                $takaranSatuanBahan = $this->konversiNilaiStok(
                    (float) $item['takaran_per_cup'],
                    $item['satuan'] ?? 'gram',
                    $bahan->satuan
                );
                $total += ($takaranSatuanBahan * (float) $bahan->harga_satuan);
            }
        }
        return round($total);
    }

    public function pilihUntukRacik($produkId)
    {
        $this->produk_id = $produkId;
        if ((int) $this->jumlah_cup < 1) {
            $this->jumlah_cup = 1;
        }
        $this->hitungPreviewBahan();
    }

    public function gunakanKapasitasMaks()
    {
        if ($this->maxBatchCapacity > 0) {
            $this->jumlah_cup = $this->maxBatchCapacity;
            $this->hitungPreviewBahan();
        }
    }

    public function hitungPreviewBahan()
    {
        $this->previewBahan = [];
        $this->stokCukup = true;
        $this->maxBatchCapacity = 0;

        if (!$this->produk_id || (int) $this->jumlah_cup < 1) {
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

            $takaranPerCup = (float) ($resep->takaran_per_cup ?? $resep->jumlah_takaran ?? 0);
            $satuanResep   = $resep->satuan ?? $bahan->satuan;
            $kebutuhanResep = $takaranPerCup * (int) $this->jumlah_cup;

            // Samakan satuan stok gudang ke satuan resep untuk perbandingan
            $stokGudangSamaSatuan = $this->konversiNilaiStok(
                (float) $bahan->stok_sekarang,
                $bahan->satuan,
                $satuanResep
            );

            $cukup = $stokGudangSamaSatuan >= $kebutuhanResep;

            if (!$cukup) {
                $this->stokCukup = false;
            }

            // Hitung kapasitas racik maksimal dari stok bahan ini
            if ($takaranPerCup > 0) {
                $maxForThisIngredient = (int) floor($stokGudangSamaSatuan / $takaranPerCup);
                $capacities[] = max(0, $maxForThisIngredient);
            }

            $this->previewBahan[] = [
                'nama_bahan'     => $bahan->nama_bahan,
                'kebutuhan_text' => $this->formatSatuan($kebutuhanResep, $satuanResep),
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
        $this->kategori = $produk->kategori ?? 'Kopi';
        $this->harga_jual = $produk->harga_jual ?? $produk->harga ?? 0;
        $this->hpp = $produk->hpp ?? 0;

        $this->komposisi = [];
        foreach ($produk->reseps as $resep) {
            $this->komposisi[] = [
                'bahan_baku_id'   => $resep->bahan_baku_id,
                'takaran_per_cup' => $resep->takaran_per_cup ?? $resep->jumlah_takaran ?? 0,
                'satuan'          => $resep->satuan ?? 'gram',
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
            'nama_produk'                 => 'required|string|max:255|unique:produks,nama_produk,' . $this->selected_produk_id,
            'kategori'                    => 'required|string',
            'harga_jual'                  => 'required|numeric|min:0',
            'komposisi'                   => 'required|array|min:1',
            'komposisi.*.bahan_baku_id'   => 'required|distinct|exists:bahan_bakus,id',
            'komposisi.*.takaran_per_cup' => 'required|numeric|min:0.01',
            'komposisi.*.satuan'          => 'required|string',
        ], [
            'komposisi.*.bahan_baku_id.distinct' => 'Bahan baku tidak boleh dipilih ganda dalam satu resep.',
        ]);

        DB::transaction(function () {
            // Kalkulasi HPP otomatis dari harga satuan bahan baku
            $calculatedHpp = 0;
            foreach ($this->komposisi as $item) {
                $bahan = BahanBaku::find($item['bahan_baku_id']);
                if ($bahan && (float) $bahan->harga_satuan > 0) {
                    $takaranDalamSatuanBahan = $this->konversiNilaiStok(
                        (float) $item['takaran_per_cup'],
                        $item['satuan'],
                        $bahan->satuan
                    );
                    $calculatedHpp += ($takaranDalamSatuanBahan * (float) $bahan->harga_satuan);
                }
            }

            $calculatedHpp = round($calculatedHpp);

            $dataProduk = [
                'nama_produk' => $this->nama_produk,
                'kategori'    => $this->kategori,
                'harga_jual'  => $this->harga_jual,
                'hpp'         => $calculatedHpp,
                'status'      => 'aktif',
            ];

            if (Schema::hasColumn('produks', 'harga')) {
                $dataProduk['harga'] = $this->harga_jual;
            }

            $produk = Produk::updateOrCreate(
                ['id' => $this->selected_produk_id],
                $dataProduk
            );

            Resep::where('produk_id', $produk->id)->delete();

            foreach ($this->komposisi as $item) {
                $dataResep = [
                    'produk_id'       => $produk->id,
                    'bahan_baku_id'   => $item['bahan_baku_id'],
                    'takaran_per_cup' => $item['takaran_per_cup'],
                    'satuan'          => $item['satuan'],
                ];
                if (Schema::hasColumn('reseps', 'jumlah_takaran')) {
                    $dataResep['jumlah_takaran'] = $item['takaran_per_cup'];
                }
                Resep::create($dataResep);
            }

            $this->catatAudit(
                $this->isEditMode ? 'Update Resep Varian' : 'Tambah Varian & Resep Baru',
                ($this->isEditMode ? 'Memperbarui' : 'Menambahkan') . " resep varian: {$produk->nama_produk} (Estimasi HPP: Rp " . number_format($calculatedHpp, 0, ',', '.') . ")"
            );
        });

        // Refresh preview jika produk yang diedit sedang terpilih di form peracikan
        if ((int) $this->produk_id === (int) $this->selected_produk_id) {
            $this->hitungPreviewBahan();
        }

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
            $namaProduk = '';
            $jumlahDiracik = (int) $this->jumlah_cup;

            DB::transaction(function () use (&$namaProduk, $jumlahDiracik) {
                $produk = Produk::where('id', $this->produk_id)->lockForUpdate()->firstOrFail();
                $namaProduk = $produk->nama_produk;
                $reseps = Resep::with('bahanBaku')->where('produk_id', $this->produk_id)->get();

                foreach ($reseps as $resep) {
                    $bahan = BahanBaku::where('id', $resep->bahan_baku_id)->lockForUpdate()->first();
                    if (!$bahan) continue;

                    $takaranPerCup = (float) ($resep->takaran_per_cup ?? $resep->jumlah_takaran ?? 0);
                    $satuanResep   = $resep->satuan ?? $bahan->satuan;
                    $kebutuhanResep = $takaranPerCup * $jumlahDiracik;

                    // Konversi kebutuhan ke satuan yang tersimpan di tabel bahan_bakus
                    $potongStokBahan = $this->konversiNilaiStok(
                        $kebutuhanResep,
                        $satuanResep,
                        $bahan->satuan
                    );

                    if ((float) $bahan->stok_sekarang < $potongStokBahan) {
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
                        'keterangan'    => "Peracikan Batch {$jumlahDiracik} Cup {$produk->nama_produk}",
                    ]);
                }

                // 3. Tambah Stok Produk Siap Jual di Gudang Pusat
                $produk->increment('stok_pusat', $jumlahDiracik);

                $this->catatAudit(
                    'Racik Batch Kopi',
                    "Meracik batch produk: {$jumlahDiracik} Cup {$produk->nama_produk} (Stok Pusat kini: {$produk->stok_pusat} cup)"
                );
            });

            session()->flash('message', "Berhasil meracik {$jumlahDiracik} cup {$namaProduk}! Stok produk pusat bertambah, bahan baku terpotong, dan log pemakaian tercatat.");

            $this->reset(['produk_id', 'jumlah_cup', 'previewBahan', 'maxBatchCapacity']);
            $this->jumlah_cup = 1;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        // Query Varian Produk dengan Grouped Closure Search agar filter status aktif tetap terjaga
        $produks = Produk::with('reseps.bahanBaku')
            ->when(Schema::hasColumn('produks', 'status'), fn($q) => $q->where('status', 'aktif'))
            ->when($this->searchVarian, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nama_produk', 'like', '%' . $this->searchVarian . '%')
                        ->orWhere('kategori', 'like', '%' . $this->searchVarian . '%');
                });
            })
            ->orderBy('nama_produk', 'asc')
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
