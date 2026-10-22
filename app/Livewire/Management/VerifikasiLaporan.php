<?php

namespace App\Livewire\Management;

use App\Models\LaporanLapak;
use Livewire\Component;
use Livewire\WithPagination;

class VerifikasiLaporan extends Component
{
    use WithPagination;

    public $filterStatus = 'pending'; // 'pending', 'approved', 'rejected'
    public $selectedLaporan = null;
    public $showModalDetail = false;
    public $catatan_management = '';

    public function filterTab($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function lihatDetail($id)
    {
        $this->selectedLaporan = LaporanLapak::with(['lapak', 'user'])->findOrFail($id);
        $this->catatan_management = $this->selectedLaporan->catatan_management ?? '';
        $this->showModalDetail = true;
    }

    public function approve($id)
    {
        $laporan = LaporanLapak::findOrFail($id);
        $laporan->update([
            'status' => 'approved',
            'catatan_management' => $this->catatan_management ?: 'Laporan verified & disetujui.',
        ]);

        session()->flash('message', 'Laporan penutupan shift berhasil disetujui!');
        $this->showModalDetail = false;
        $this->reset(['selectedLaporan', 'catatan_management']);
    }

    public function reject($id)
    {
        $this->validate([
            'catatan_management' => 'required|string|min:5',
        ], [
            'catatan_management.required' => 'Wajib mengisi alasan penolakan laporan.',
        ]);

        $laporan = LaporanLapak::findOrFail($id);
        $laporan->update([
            'status' => 'rejected',
            'catatan_management' => $this->catatan_management,
        ]);

        session()->flash('message', 'Laporan penutupan shift ditolak.');
        $this->showModalDetail = false;
        $this->reset(['selectedLaporan', 'catatan_management']);
    }

    public function render()
    {
        $laporans = LaporanLapak::with(['lapak', 'user'])
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.management.verifikasi-laporan', [
            'laporans' => $laporans,
        ])->layout('layouts.app');
    }
}