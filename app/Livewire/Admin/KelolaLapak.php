<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Lapak;
use Livewire\Component;

class KelolaLapak extends Component
{
    // Form State
    public $lapak_id = null;
    public $nama_lapak = '';
    public $lokasi = '';
    public $status = 'aktif';

    // Filter & Search State
    public $search = '';
    public $statusFilter = '';

    // Modal State
    public $showModalLapak = false;

    public function openModalLapak()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->showModalLapak = true;
    }

    public function resetForm()
    {
        $this->lapak_id = null;
        $this->nama_lapak = '';
        $this->lokasi = '';
        $this->status = 'aktif';
    }

    public function editLapak($id)
    {
        $this->resetValidation();
        $lapak = Lapak::findOrFail($id);
        $this->lapak_id = $lapak->id;
        $this->nama_lapak = $lapak->nama_lapak;
        $this->lokasi = $lapak->lokasi;
        $this->status = $lapak->status;

        $this->showModalLapak = true;
    }

    public function simpanLapak()
    {
        $this->validate([
            'nama_lapak' => 'required|string|max:255|unique:lapaks,nama_lapak,' . $this->lapak_id,
            'lokasi'     => 'nullable|string|max:500',
            'status'     => 'required|in:aktif,libur,perbaikan',
        ]);

        $isNew = !$this->lapak_id;
        $lapak = Lapak::updateOrCreate(
            ['id' => $this->lapak_id],
            [
                'nama_lapak' => $this->nama_lapak,
                'lokasi'     => $this->lokasi,
                'status'     => $this->status,
            ]
        );

        AuditLog::create([
            'user_id'   => auth()->id(),
            'aktivitas' => $isNew
                ? "Menambah armada lapak baru: {$lapak->nama_lapak}"
                : "Memperbarui data lapak: {$lapak->nama_lapak} (Status: {$lapak->status})",
        ]);

        session()->flash('message', $isNew ? 'Armada lapak baru berhasil ditambahkan!' : 'Data armada lapak berhasil diperbarui!');
        $this->showModalLapak = false;
        $this->resetForm();
    }

    public function toggleQuickStatus($id, $nextStatus)
    {
        $lapak = Lapak::findOrFail($id);
        $oldStatus = $lapak->status;
        $lapak->update(['status' => $nextStatus]);

        AuditLog::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Mengubah status operasional lapak {$lapak->nama_lapak} dari {$oldStatus} menjadi {$nextStatus}",
        ]);

        session()->flash('message', "Status operasional '{$lapak->nama_lapak}' berhasil diubah menjadi " . strtoupper($nextStatus) . ".");
    }

    public function render()
    {
        $lapaks = Lapak::with(['users' => function ($q) {
            $q->where('status', 'aktif');
        }])
            ->withCount('users')
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('nama_lapak', 'like', '\%' . $this->search . '%')
                        ->orWhere('lokasi', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('nama_lapak', 'asc')
            ->get();

        return view('livewire.admin.kelola-lapak', [
            'lapaks' => $lapaks,
        ])->layout('layouts.app');
    }
}
