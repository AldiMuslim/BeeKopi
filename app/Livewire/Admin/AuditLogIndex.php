<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogIndex extends Component
{
    use WithPagination;

    // Filter & Search State
    public $search = '';
    public $roleFilter = '';
    public $userFilter = '';
    public $periodeFilter = 'semua'; // 'semua', 'hari_ini', '7_hari', 'bulan_ini'
    public $tanggalSpesifik = '';
    public $levelFilter = ''; // '', 'kritis'

    // Modal Inspector State
    public $showModalDetail = false;
    public $selectedLog = null;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingPeriodeFilter()
    {
        $this->tanggalSpesifik = '';
        $this->resetPage();
    }

    public function updatingTanggalSpesifik()
    {
        if ($this->tanggalSpesifik) {
            $this->periodeFilter = 'semua';
        }
        $this->resetPage();
    }

    public function filterByUser($userId)
    {
        $this->userFilter = $userId;
        $this->resetPage();
    }

    public function toggleLevelKritis()
    {
        $this->levelFilter = ($this->levelFilter === 'kritis') ? '' : 'kritis';
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'roleFilter', 'userFilter', 'tanggalSpesifik', 'levelFilter']);
        $this->periodeFilter = 'semua';
        $this->resetPage();
    }

    /**
     * Smart Parser untuk menyeragamkan tampilan kolom aktivitas & keterangan dari berbagai modul
     */
    public function analisaLog($log): array
    {
        $rawAktivitas  = trim($log->aktivitas ?? '');
        $rawKeterangan = trim($log->keterangan ?? '');
        $gabunganTeks  = strtolower($rawAktivitas . ' ' . $rawKeterangan);

        // 1. Deteksi Modul Asal
        $modul = 'Sistem Umum';
        $modulColor = 'bg-gray-100 text-gray-700 border-gray-200';

        if (str_contains($gabunganTeks, 'racik') || str_contains($gabunganTeks, 'resep') || str_contains($gabunganTeks, 'varian') || str_contains($gabunganTeks, 'hpp')) {
            $modul = 'Produksi & Resep';
            $modulColor = 'bg-amber-50 text-amber-800 border-amber-200';
        } elseif (str_contains($gabunganTeks, 'bahan baku') || str_contains($gabunganTeks, 'belanja') || str_contains($gabunganTeks, 'stok gudang')) {
            $modul = 'Inventori Bahan';
            $modulColor = 'bg-orange-50 text-orange-800 border-orange-200';
        } elseif (str_contains($gabunganTeks, 'distribusi') || str_contains($gabunganTeks, 'pengiriman') || str_contains($gabunganTeks, 'dispatch') || str_contains($gabunganTeks, 'restock')) {
            $modul = 'Distribusi Lapak';
            $modulColor = 'bg-blue-50 text-blue-800 border-blue-200';
        } elseif (str_contains($gabunganTeks, 'pengguna') || str_contains($gabunganTeks, 'akun') || str_contains($gabunganTeks, 'sandi') || str_contains($gabunganTeks, 'user')) {
            $modul = 'Manajemen User';
            $modulColor = 'bg-purple-50 text-purple-800 border-purple-200';
        } elseif (str_contains($gabunganTeks, 'lapak') || str_contains($gabunganTeks, 'armada')) {
            $modul = 'Armada Lapak';
            $modulColor = 'bg-indigo-50 text-indigo-800 border-indigo-200';
        } elseif (str_contains($gabunganTeks, 'produk') || str_contains($gabunganTeks, 'menu')) {
            $modul = 'Katalog Produk';
            $modulColor = 'bg-teal-50 text-teal-800 border-teal-200';
        }

        // 2. Deteksi Tingkat Risiko / Klasifikasi Operasi
        $level = 'Pembaruan';
        $badgeColor = 'bg-blue-100 text-blue-800 border-blue-200';

        $kataKritis = ['reset', 'sandi', 'nonaktif', 'hapus', 'koreksi', 'tolak', 'batal', 'rusak', 'selisih', 'bersihkan'];
        $kataTransaksi = ['meracik', 'tambah', 'menambah', 'konfirmasi', 'dispatch', 'belanja', 'diterima', 'duplikat'];

        foreach ($kataKritis as $kw) {
            if (str_contains($gabunganTeks, $kw)) {
                $level = 'Kritis / Sensitif';
                $badgeColor = 'bg-rose-100 text-rose-800 border-rose-200';
                break;
            }
        }

        if ($level !== 'Kritis / Sensitif') {
            foreach ($kataTransaksi as $kw) {
                if (str_contains($gabunganTeks, $kw)) {
                    $level = 'Eksekusi / Input';
                    $badgeColor = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                    break;
                }
            }
        }

        // 3. Pemisahan Judul Aksi & Rincian Keterangan
        if (!empty($rawKeterangan)) {
            $judul = $rawAktivitas;
            $rincian = $rawKeterangan;
        } elseif (str_contains($rawAktivitas, ':')) {
            $parts = explode(':', $rawAktivitas, 2);
            $judul = trim($parts[0]);
            $rincian = trim($parts[1]);
        } else {
            $judul = $modul;
            $rincian = $rawAktivitas;
        }

        return [
            'modul'       => $modul,
            'modul_color' => $modulColor,
            'level'       => $level,
            'badge_color' => $badgeColor,
            'judul'       => $judul,
            'rincian'     => $rincian,
        ];
    }

    /**
     * Query Utama dengan Dukungan Multi-Filter
     */
    private function buildFilteredQuery()
    {
        $hasKeterangan = Schema::hasColumn('audit_logs', 'keterangan');

        return AuditLog::with(['user.role', 'user.lapak'])
            ->when($this->search, function ($query) use ($hasKeterangan) {
                $query->where(function ($sub) use ($hasKeterangan) {
                    $sub->where('aktivitas', 'like', '%' . $this->search . '%');
                    if ($hasKeterangan) {
                        $sub->orWhere('keterangan', 'like', '%' . $this->search . '%');
                    }
                    $sub->orWhereHas('user', function ($u) {
                        $u->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('user', fn($u) => $u->where('role_id', $this->roleFilter));
            })
            ->when($this->userFilter, function ($query) {
                $query->where('user_id', $this->userFilter);
            })
            ->when($this->periodeFilter === 'hari_ini', fn($q) => $q->whereDate('created_at', now()))
            ->when($this->periodeFilter === '7_hari', fn($q) => $q->where('created_at', '>=', now()->subDays(7)))
            ->when($this->periodeFilter === 'bulan_ini', fn($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->when($this->tanggalSpesifik, fn($q) => $q->whereDate('created_at', $this->tanggalSpesifik))
            ->when($this->levelFilter === 'kritis', function ($query) use ($hasKeterangan) {
                $query->where(function ($sub) use ($hasKeterangan) {
                    $keywords = ['reset', 'sandi', 'nonaktif', 'hapus', 'koreksi', 'tolak', 'batal', 'rusak', 'selisih', 'bersihkan'];
                    foreach ($keywords as $kw) {
                        $sub->orWhere('aktivitas', 'like', "%{$kw}%");
                        if ($hasKeterangan) {
                            $sub->orWhere('keterangan', 'like', "%{$kw}%");
                        }
                    }
                });
            })
            ->latest();
    }

    public function openDetail($id)
    {
        $this->selectedLog = AuditLog::with(['user.role', 'user.lapak'])->findOrFail($id);
        $this->showModalDetail = true;
    }

    /**
     * Unduh Rekaman Audit Log ke CSV / Excel sesuai Filter Aktif
     */
    public function exportCsv()
    {
        $logs = $this->buildFilteredQuery()->take(1000)->get();
        $filename = 'audit_log_beekopi_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            // Tambahkan BOM UTF-8 agar langsung terbaca rapi di Microsoft Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['ID Log', 'Waktu Presisi', 'Nama Pengguna', 'Email', 'Role', 'Lapak', 'Modul', 'Klasifikasi', 'Aktivitas', 'Rincian Keterangan']);

            foreach ($logs as $log) {
                $parsed = $this->analisaLog($log);
                fputcsv($handle, [
                    $log->id,
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
                    $log->user->name ?? 'Sistem',
                    $log->user->email ?? '-',
                    $log->user->role->label ?? ($log->user->role->name ?? '-'),
                    $log->user->lapak->nama_lapak ?? '-',
                    $parsed['modul'],
                    $parsed['level'],
                    $parsed['judul'],
                    $parsed['rincian'],
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    /**
     * Bersihkan Log Lawas (Usia di atas 90 Hari) untuk menjaga performa tabel
     */
    public function bersihkanLogLama()
    {
        $batasTanggal = now()->subDays(90);
        $jumlahTerhapus = AuditLog::where('created_at', '<', $batasTanggal)->count();

        if ($jumlahTerhapus === 0) {
            session()->flash('error', 'Tidak ditemukan rekaman audit log yang berusia lebih dari 90 hari.');
            return;
        }

        AuditLog::where('created_at', '<', $batasTanggal)->delete();

        $dataLog = [
            'user_id'   => Auth::id(),
            'aktivitas' => "Pembersihan Berkala Audit Log: Menghapus {$jumlahTerhapus} rekaman log berusia di atas 90 hari",
        ];
        if (Schema::hasColumn('audit_logs', 'keterangan')) {
            $dataLog['aktivitas']  = 'Pembersihan Berkala Audit Log';
            $dataLog['keterangan'] = "Menghapus {$jumlahTerhapus} rekaman log lawas yang berusia di atas 90 hari";
        }
        AuditLog::create($dataLog);

        session()->flash('message', "Berhasil membersihkan {$jumlahTerhapus} rekaman audit log yang berusia lebih dari 90 hari!");
        $this->resetPage();
    }

    public function render()
    {
        $hasKeterangan = Schema::hasColumn('audit_logs', 'keterangan');

        // Metrik Statistik Atas
        $totalLogHariIni = AuditLog::whereDate('created_at', now())->count();
        $operatorAktifHariIni = AuditLog::whereDate('created_at', now())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
        $totalSemuaLog = AuditLog::count();

        // Hitung Aksi Kritis / Sensitif
        $aksiKritisCount = AuditLog::where(function ($sub) use ($hasKeterangan) {
            $keywords = ['reset', 'sandi', 'nonaktif', 'hapus', 'koreksi', 'tolak', 'batal', 'rusak', 'selisih', 'bersihkan'];
            foreach ($keywords as $kw) {
                $sub->orWhere('aktivitas', 'like', "%{$kw}%");
                if ($hasKeterangan) {
                    $sub->orWhere('keterangan', 'like', "%{$kw}%");
                }
            }
        })->count();

        $logs = $this->buildFilteredQuery()->paginate(20);
        $selectedUserObj = $this->userFilter ? User::find($this->userFilter) : null;

        return view('livewire.admin.audit-log', [
            'logs'                 => $logs,
            'roles'                => Role::all(),
            'totalLogHariIni'      => $totalLogHariIni,
            'aksiKritisCount'      => $aksiKritisCount,
            'operatorAktifHariIni' => $operatorAktifHariIni,
            'totalSemuaLog'        => $totalSemuaLog,
            'selectedUserObj'      => $selectedUserObj,
        ])->layout('layouts.app');
    }
}
