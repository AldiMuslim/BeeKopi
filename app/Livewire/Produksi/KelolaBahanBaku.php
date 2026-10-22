<?php

namespace App\Livewire\Produksi;

use App\Models\BahanBaku;
use App\Models\RiwayatBahanBaku;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class KelolaBahanBaku extends Component
{
    use WithPagination;

    public $search = '';

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
    public $selected_bahan_id = null;
    public $jenis_penyesuaian = 'belanja'; // 'belanja', 'pemakaian', atau 'rusak'
    public $jumlah_jumlah = '';
    public $keterangan = '';

    public function openModalBahan()
    {
        $this->reset(['bahan_id', 'nama_bahan', 'kategori', 'stok_sekarang', 'stok_minimum', 'satuan', 'harga_satuan']);
        $this->showModalBahan = true;
    }

    public function editBahan($id)
    {
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
            'nama_bahan' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'stok_sekarang' => 'required|numeric|min:0',
            'stok_minimum' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
        ]);

        BahanBaku::updateOrCreate(
            ['id' => $this->bahan_id],
            [
                'nama_bahan' => $this->nama_bahan,
                'kategori' => $this->kategori,
                'stok_sekarang' => $this->stok_sekarang,
                'stok_minimum' => $this->stok_minimum,
                'satuan' => $this->satuan,
                'harga_satuan' => $this->harga_satuan,
            ]
        );

        session()->flash('message', $this->bahan_id ? 'Bahan baku berhasil diperbarui!' : 'Bahan baku baru berhasil ditambahkan!');
        $this->showModalBahan = false;
    }

    public function openModalStok($id)
    {
        $this->selected_bahan_id = $id;
        $this->jenis_penyesuaian = 'belanja';
        $this->jumlah_jumlah = '';
        $this->keterangan = '';
        $this->showModalStok = true;
    }

    public function updateStok()
    {
        $this->validate([
            'selected_bahan_id' => 'required|exists:bahan_bakus,id',
            'jenis_penyesuaian' => 'required|in:belanja,pemakaian,rusak',
            'jumlah_jumlah' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::transaction(function () {
                $bahan = BahanBaku::findOrFail($this->selected_bahan_id);
                $jumlah = (float) $this->jumlah_jumlah;

                if ($this->jenis_penyesuaian === 'belanja') {
                    // 1. Tambah Stok Bahan Baku
                    $bahan->increment('stok_sekarang', $jumlah);

                    // 2. Simpan Catatan Otomatis ke Log Belanja (+ In / 'masuk')
                    RiwayatBahanBaku::create([
                        'bahan_baku_id' => $bahan->id,
                        'user_id' => auth()->id(),
                        'jenis' => 'masuk',
                        'jumlah' => $jumlah,
                        'satuan' => $bahan->satuan,
                        'total_biaya' => $jumlah * $bahan->harga_satuan,
                        'keterangan' => $this->keterangan ?: 'Restok Belanja Harian',
                    ]);

                    session()->flash('message', "Berhasil menambah stok {$bahan->nama_bahan} (+{$jumlah} {$bahan->satuan}) & tercatat di Log Belanja!");

                } else {
                    // Penyesuaian Pemakaian / Rusak (- Out / 'keluar')
                    if ($bahan->stok_sekarang < $jumlah) {
                        throw new \Exception("Stok {$bahan->nama_bahan} tidak mencukupi untuk dikurangi!");
                    }

                    // 1. Potong Stok Bahan Baku
                    $bahan->decrement('stok_sekarang', $jumlah);

                    // 2. Simpan Catatan Otomatis ke Log Pemakaian (- Out / 'keluar')
                    RiwayatBahanBaku::create([
                        'bahan_baku_id' => $bahan->id,
                        'user_id' => auth()->id(),
                        'jenis' => 'keluar',
                        'jumlah' => $jumlah,
                        'satuan' => $bahan->satuan,
                        'total_biaya' => 0,
                        'keterangan' => $this->keterangan ?: ($this->jenis_penyesuaian === 'rusak' ? 'Bahan Rusak / Waste' : 'Pemakaian Manual'),
                    ]);

                    session()->flash('message', "Berhasil mengurangi stok {$bahan->nama_bahan} (-{$jumlah} {$bahan->satuan}) & tercatat di Log Pemakaian!");
                }
            });

            $this->showModalStok = false;

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.produksi.kelola-bahan-baku', [
            'bahanBakus' => BahanBaku::where('nama_bahan', 'like', "%{$this->search}%")
                ->orWhere('kategori', 'like', "%{$this->search}%")
                ->latest()
                ->paginate(10),
            'logPemakaian' => RiwayatBahanBaku::with(['bahanBaku', 'user'])
                ->where('jenis', 'keluar')
                ->latest()
                ->take(20)
                ->get(),
            'logBelanja' => RiwayatBahanBaku::with(['bahanBaku', 'user'])
                ->where('jenis', 'masuk')
                ->latest()
                ->take(20)
                ->get(),
        ])->layout('layouts.app');
    }
}