<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Resep;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KelolaProduk extends Component
{
    public $search = '';
    public $filterKategori = '';
    public $showModalProduk = false;
    public $showModalResep = false;

    public $produk_id, $nama_produk, $kategori = 'Coffee', $harga = 0, $stok_pusat = 0;
    public $selectedProduk, $bahan_baku_id, $jumlah_takaran;

    public function openModalProduk()
    {
        $this->resetInputProduk();
        $this->showModalProduk = true;
    }

    public function editProduk($id)
    {
        $produk = Produk::findOrFail($id);
        $this->produk_id = $produk->id;
        $this->nama_produk = $produk->nama_produk;
        $this->kategori = $produk->kategori;
        $this->harga = $produk->harga;
        $this->stok_pusat = $produk->stok_pusat;
        $this->showModalProduk = true;
    }

    public function simpanProduk()
    {
        $this->validate([
            'nama_produk' => 'required|string|max:255',
            'kategori' => 'required|string',
            'harga' => 'required|numeric|min:0',
            'stok_pusat' => 'required|numeric|min:0',
        ]);

        $isEdit = $this->produk_id ? true : false;

        $produk = Produk::updateOrCreate(
            ['id' => $this->produk_id],
            [
                'nama_produk' => $this->nama_produk,
                'kategori' => $this->kategori,
                'harga' => $this->harga,
                'stok_pusat' => $this->stok_pusat,
            ]
        );

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => $isEdit ? 'Update Produk' : 'Tambah Produk',
            'keterangan' => ($isEdit ? 'Mengubah' : 'Menambahkan') . ' produk ' . $produk->nama_produk,
        ]);

        session()->flash('message', 'Data produk berhasil disimpan.');
        $this->showModalProduk = false;
        $this->resetInputProduk();
    }

    public function openModalResep($produkId)
    {
        $this->selectedProduk = Produk::with('reseps.bahanBaku')->findOrFail($produkId);
        $this->showModalResep = true;
    }

    public function simpanResep()
    {
        $this->validate([
            'bahan_baku_id' => 'required|exists:bahan_bakus,id',
            'jumlah_takaran' => 'required|numeric|min:0.01',
        ]);

        Resep::updateOrCreate(
            [
                'produk_id' => $this->selectedProduk->id,
                'bahan_baku_id' => $this->bahan_baku_id,
            ],
            [
                'jumlah_takaran' => $this->jumlah_takaran,
            ]
        );

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => 'Set Resep Produk',
            'keterangan' => 'Mengatur resep produk: ' . $this->selectedProduk->nama_produk,
        ]);

        $this->selectedProduk->load('reseps.bahanBaku');
        $this->reset(['bahan_baku_id', 'jumlah_takaran']);
    }

    public function hapusResep($resepId)
    {
        Resep::destroy($resepId);
        $this->selectedProduk->load('reseps.bahanBaku');
    }

    private function resetInputProduk()
    {
        $this->reset(['produk_id', 'nama_produk', 'kategori', 'harga', 'stok_pusat']);
    }

    public function render()
    {
        $query = Produk::with('reseps.bahanBaku');

        if ($this->search) {
            $query->where('nama_produk', 'like', '%' . $this->search . '%');
        }

        if ($this->filterKategori) {
            $query->where('kategori', $this->filterKategori);
        }

        return view('livewire.admin.kelola-produk', [
            'produks' => $query->get(),
            'allBahanBaku' => BahanBaku::all(),
        ])->layout('layouts.app');
    }
}