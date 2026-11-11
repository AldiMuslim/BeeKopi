<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Lapak;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class KelolaLapak extends Component
{
    // Form State
    public $lapak_id = null;
    public $nama_lapak = '';
    public $lokasi = '';
    public $status = 'aktif';
    public array $selectedSales = []; // Menyimpan ID staf sales yang ditautkan ke lapak ini

    // Filter & Search State
    public $search = '';
    public $statusFilter = '';

    // Modal State
    public $showModalLapak = false;

    private function catatLog($aktivitas, $keterangan = null)
    {
        $data = [
            'user_id'   => auth()->id(),
            'aktivitas' => $keterangan && !Schema::hasColumn('audit_logs', 'keterangan')
                ? "{$aktivitas}: {$keterangan}"
                : $aktivitas,
        ];

        if ($keterangan && Schema::hasColumn('audit_logs', 'keterangan')) {
            $data['keterangan'] = $keterangan;
        }

        AuditLog::create($data);
    }

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
        $this->selectedSales = [];
    }

    public function editLapak($id)
    {
        $this->resetValidation();
        $lapak = Lapak::with('users')->findOrFail($id);

        $this->lapak_id      = $lapak->id;
        $this->nama_lapak    = $lapak->nama_lapak;
        $this->lokasi        = $lapak->lokasi;
        $this->status        = $lapak->status;
        $this->selectedSales = $lapak->users->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $this->showModalLapak = true;
    }

    public function simpanLapak()
    {
        $this->validate([
            'nama_lapak'      => 'required|string|max:255|unique:lapaks,nama_lapak,' . $this->lapak_id,
            'lokasi'          => 'nullable|string|max:500',
            'status'          => 'required|in:aktif,libur,perbaikan',
            'selectedSales'   => 'nullable|array',
            'selectedSales.*' => 'exists:users,id',
        ]);

        $isNew = !$this->lapak_id;

        DB::transaction(function () use ($isNew) {
            $lapak = Lapak::updateOrCreate(
                ['id' => $this->lapak_id],
                [
                    'nama_lapak' => $this->nama_lapak,
                    'lokasi'     => $this->lokasi,
                    'status'     => $this->status,
                ]
            );

            // 1. Lepaskan user lama yang sebelumnya berada di lapak ini tetapi tidak lagi dicentang
            User::where('lapak_id', $lapak->id)
                ->whereNotIn('id', $this->selectedSales)
                ->update(['lapak_id' => null]);

            // 2. Tautkan staf sales yang dicentang ke lapak ini
            if (!empty($this->selectedSales)) {
                User::whereIn('id', $this->selectedSales)
                    ->update(['lapak_id' => $lapak->id]);
            }

            $jumlahSales = count($this->selectedSales);
            $this->catatLog(
                $isNew ? 'Tambah Armada Lapak' : 'Update Armada Lapak',
                ($isNew ? 'Menambah' : 'Memperbarui') . " lapak {$lapak->nama_lapak} (Status: {$lapak->status}, Tim Sales: {$jumlahSales} orang)"
            );
        });

        session()->flash('message', $isNew ? 'Armada lapak baru beserta pemetaan sales berhasil ditambahkan!' : 'Data armada lapak & tim sales berhasil diperbarui!');
        $this->showModalLapak = false;
        $this->resetForm();
    }

    public function toggleQuickStatus($id, $nextStatus)
    {
        $lapak = Lapak::findOrFail($id);
        $oldStatus = $lapak->status;
        $lapak->update(['status' => $nextStatus]);

        $this->catatLog(
            'Ubah Status Lapak',
            "Mengubah status operasional lapak {$lapak->nama_lapak} dari {$oldStatus} menjadi {$nextStatus}"
        );

        session()->flash('message', "Status operasional '{$lapak->nama_lapak}' berhasil diubah menjadi " . strtoupper($nextStatus) . ".");
    }

    public function hapusLapak($id)
    {
        $lapak = Lapak::withCount(['transaksis'])->findOrFail($id);

        if ($lapak->transaksis_count > 0) {
            session()->flash('error', "Gagal menghapus! Lapak '{$lapak->nama_lapak}' sudah memiliki {$lapak->transaksis_count} riwayat transaksi penjualan. Ubah statusnya menjadi Libur/Perbaikan.");
            return;
        }

        try {
            DB::transaction(function () use ($lapak) {
                $nama = $lapak->nama_lapak;
                // Lepaskan tautan staf sales terlebih dahulu agar tidak melanggar foreign key
                User::where('lapak_id', $lapak->id)->update(['lapak_id' => null]);
                $lapak->delete();

                $this->catatLog('Hapus Armada Lapak', "Menghapus armada lapak: {$nama}");
            });

            session()->flash('message', "Armada lapak '{$lapak->nama_lapak}' berhasil dihapus.");
        } catch (QueryException $e) {
            session()->flash('error', "Lapak '{$lapak->nama_lapak}' tidak dapat dihapus karena masih terhubung dengan data stok/distribusi laporan.");
        }
    }

    public function render()
    {
        $lapaks = Lapak::with(['users' => function ($q) {
            if (Schema::hasColumn('users', 'status')) {
                $q->where('status', 'aktif');
            }
        }])
            ->withCount('users')
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('nama_lapak', 'like', '%' . $this->search . '%')
                        ->orWhere('lokasi', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('nama_lapak', 'asc')
            ->get();

        // Ambil daftar akun dengan role Sales/Barista untuk opsi penautan di Modal
        $salesUsers = User::with(['role', 'lapak'])
            ->whereHas('role', function ($q) {
                $q->where('nama', 'like', '%sales%')
                    ->orWhere('label', 'like', '%sales%')
                    ->orWhere('label', 'like', '%barista%');
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.admin.kelola-lapak', [
            'lapaks'     => $lapaks,
            'salesUsers' => $salesUsers,
        ])->layout('layouts.app');
    }
}
