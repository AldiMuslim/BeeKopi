<?php

namespace App\Livewire\Management;

use App\Models\Lapak;
use App\Models\LaporanLapak;
use Livewire\Component;
use Livewire\WithPagination;

class VerifikasiLaporan extends Component
{
    use WithPagination;

    public $filterStatus = 'pending'; // 'pending', 'approved', 'rejected'
    public $filterLapak = '';
    public $tanggal_mulai = '';
    public $tanggal_selesai = '';

    public $selectedLaporan = null;
    public $showModalDetail = false;
    public $catatan_management = '';

    public function filterTab($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->reset(['filterLapak', 'tanggal_mulai', 'tanggal_selesai']);
        $this->resetPage();
    }

    public function lihatDetail($id)
    {
        $this->selectedLaporan = LaporanLapak::with(['lapak', 'user'])->findOrFail($id);
        $this->catatan_management = $this->selectedLaporan->catatan_management ?? '';
        $this->showModalDetail = true;
    }

    public function closeModal()
    {
        $this->showModalDetail = false;
        $this->reset(['selectedLaporan', 'catatan_management']);
    }

    public function approve($id)
    {
        $laporan = LaporanLapak::findOrFail($id);

        if ($laporan->status !== 'pending') {
            session()->flash('error', 'Laporan ini sudah diverifikasi sebelumnya.');
            return;
        }

        $laporan->update([
            'status' => 'approved',
            'catatan_management' => $this->catatan_management ?: 'Laporan verified & setoran disetujui.',
        ]);

        session()->flash('message', 'Laporan penutupan shift berhasil disetujui!');
        $this->closeModal();
    }

    public function reject($id)
    {
        $laporan = LaporanLapak::findOrFail($id);

        if ($laporan->status !== 'pending') {
            session()->flash('error', 'Laporan ini sudah diverifikasi sebelumnya.');
            return;
        }

        $this->validate([
            'catatan_management' => 'required|string|min:5',
        ], [
            'catatan_management.required' => 'Wajib mengisi alasan penolakan laporan.',
            'catatan_management.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $laporan->update([
            'status' => 'rejected',
            'catatan_management' => $this->catatan_management,
        ]);

        session()->flash('message', 'Laporan penutupan shift telah ditolak.');
        $this->closeModal();
    }

    public function render()
    {
        // Base Query untuk Rekap Tab Counter
        $baseQuery = LaporanLapak::query()
            ->when($this->filterLapak, fn($q) => $q->where('lapak_id', $this->filterLapak))
            ->when($this->tanggal_mulai && $this->tanggal_selesai, function ($q) {
                $q->whereBetween('tanggal', [$this->tanggal_mulai, $this->tanggal_selesai]);
            });

        // Badge Counter Real-Time
        $countPending  = (clone $baseQuery)->where('status', 'pending')->count();
        $countApproved = (clone $baseQuery)->where('status', 'approved')->count();
        $countRejected = (clone $baseQuery)->where('status', 'rejected')->count();

        // Data Laporan Terfilter dengan Paginasi
        $laporans = (clone $baseQuery)
            ->with(['lapak', 'user'])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest('tanggal')
            ->paginate(10);

        return view('livewire.management.verifikasi-laporan', [
            'laporans'      => $laporans,
            'lapaks'        => Lapak::all(),
            'countPending'  => $countPending,
            'countApproved' => $countApproved,
            'countRejected' => $countRejected,
        ])->layout('layouts.app');
    }
}
