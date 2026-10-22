<?php

namespace App\Livewire\Admin;

use App\Models\Lapak;
use Livewire\Component;

class KelolaLapak extends Component
{
    public $lapak_id;
    public $nama_lapak;
    public $lokasi;
    public $status = 'aktif';

    public $showModalLapak = false;

    protected $rules = [
        'nama_lapak' => 'required|string|max:255',
        'lokasi' => 'nullable|string',
        'status' => 'required|in:aktif,libur,perbaikan',
    ];

    public function openModalLapak()
    {
        $this->resetForm();
        $this->showModalLapak = true;
    }

    public function editLapak($id)
    {
        $lapak = Lapak::findOrFail($id);
        $this->lapak_id = $lapak->id;
        $this->nama_lapak = $lapak->nama_lapak;
        $this->lokasi = $lapak->lokasi;
        $this->status = $lapak->status;

        $this->showModalLapak = true;
    }

    public function simpanLapak()
    {
        $this->validate();

        Lapak::updateOrCreate(
            ['id' => $this->lapak_id],
            [
                'nama_lapak' => $this->nama_lapak,
                'lokasi' => $this->lokasi,
                'status' => $this->status,
            ]
        );

        session()->flash('message', 'Data armada lapak berhasil diperbarui.');
        $this->showModalLapak = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->lapak_id = null;
        $this->nama_lapak = '';
        $this->lokasi = '';
        $this->status = 'aktif';
    }

    public function render()
    {
        return view('livewire.admin.kelola-lapak', [
            'lapaks' => Lapak::withCount('users')->get(),
        ])->layout('layouts.app');
    }
}