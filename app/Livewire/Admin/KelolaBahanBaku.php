<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\BahanBaku;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class KelolaBahanBaku extends Component
{
    public $showModalBahan = false;
    public $bahan_baku_id, $nama_bahan, $stok = 0, $satuan = 'gram', $stok_minimum = 0;

    public function openModalBahan()
    {
        $this->resetInput();
        $this->showModalBahan = true;
    }

    public function editBahan($id)
    {
        $bahan = BahanBaku::findOrFail($id);
        $this->bahan_baku_id = $bahan->id;
        $this->nama_bahan = $bahan->nama_bahan;
        $this->stok = $bahan->stok;
        $this->satuan = $bahan->satuan;
        $this->stok_minimum = $bahan->stok_minimum;
        $this->showModalBahan = true;
    }

    public function simpanBahan()
    {
        $this->validate([
            'nama_bahan' => 'required|string|max:255',
            'stok' => 'required|numeric|min:0',
            'satuan' => 'required|string',
            'stok_minimum' => 'required|numeric|min:0',
        ]);

        $isEdit = $this->bahan_baku_id ? true : false;

        $bahan = BahanBaku::updateOrCreate(
            ['id' => $this->bahan_baku_id],
            [
                'nama_bahan' => $this->nama_bahan,
                'stok' => $this->stok,
                'satuan' => $this->satuan,
                'stok_minimum' => $this->stok_minimum,
            ]
        );

        AuditLog::create([
            'user_id' => Auth::id(),
            'aktivitas' => $isEdit ? 'Update Bahan Baku' : 'Tambah Bahan Baku',
            'keterangan' => ($isEdit ? 'Mengubah' : 'Menambahkan') . ' bahan baku ' . $bahan->nama_bahan,
        ]);

        session()->flash('message', 'Master bahan baku berhasil disimpan.');
        $this->showModalBahan = false;
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->reset(['bahan_baku_id', 'nama_bahan', 'stok', 'satuan', 'stok_minimum']);
    }

    public function render()
    {
        return view('livewire.admin.kelola-bahan-baku', [
            'bahanBakus' => BahanBaku::all(),
        ])->layout('layouts.app');
    }
}
