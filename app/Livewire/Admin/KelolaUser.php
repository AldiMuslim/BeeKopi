<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Lapak;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class KelolaUser extends Component
{
    use WithPagination;

    // State Form
    public $user_id = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $role_id = '';
    public $lapak_id = '';
    public $status = 'aktif'; // 'aktif' atau 'nonaktif'

    // State Filter & Search
    public $search = '';
    public $roleFilter = '';

    // State Modal
    public $showModalUser = false;

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function openModalUser()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->showModalUser = true;
    }

    public function resetForm()
    {
        $this->user_id = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role_id = Role::first()?->id ?? '';
        $this->lapak_id = '';
        $this->status = 'aktif';
    }

    public function editUser($id)
    {
        $this->resetValidation();
        $user = User::findOrFail($id);
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->lapak_id = $user->lapak_id ?? '';
        $this->status = $user->status ?? 'aktif';
        $this->password = '';

        $this->showModalUser = true;
    }

    public function simpanUser()
    {
        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $this->user_id,
            'role_id'  => 'required|exists:roles,id',
            'lapak_id' => 'nullable|exists:lapaks,id',
            'status'   => 'required|in:aktif,nonaktif',
        ];

        if (!$this->user_id || !empty($this->password)) {
            $rules['password'] = 'required|min:6';
        }

        $this->validate($rules);

        // Self-Protection Guard: Mencegah Admin menonaktifkan akunnya sendiri
        if ((int) $this->user_id === (int) auth()->id() && $this->status === 'nonaktif') {
            session()->flash('error', 'Gagal! Anda tidak dapat menonaktifkan akun Admin yang sedang Anda gunakan.');
            return;
        }

        $data = [
            'name'     => $this->name,
            'email'    => $this->email,
            'role_id'  => (int) $this->role_id,
            'lapak_id' => $this->lapak_id ?: null,
            'status'   => $this->status,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $isNew = !$this->user_id;
        $user = User::updateOrCreate(['id' => $this->user_id], $data);

        AuditLog::create([
            'user_id'   => auth()->id(),
            'aktivitas' => $isNew
                ? "Menambah pengguna baru: {$user->name} ({$user->email})"
                : "Memperbarui akun pengguna: {$user->name}",
        ]);

        session()->flash('message', $isNew ? 'Akun pengguna baru berhasil ditambahkan!' : 'Data akun pengguna berhasil diperbarui!');
        $this->showModalUser = false;
        $this->resetForm();
    }

    public function toggleStatus($id)
    {
        // Self-Protection Guard
        if ((int) $id === (int) auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
            return;
        }

        $user = User::findOrFail($id);
        $statusBaru = ($user->status === 'nonaktif') ? 'aktif' : 'nonaktif';
        $user->update(['status' => $statusBaru]);

        AuditLog::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Mengubah status akun {$user->name} menjadi {$statusBaru}",
        ]);

        session()->flash('message', "Status akun '{$user->name}' berhasil diubah menjadi " . strtoupper($statusBaru) . ".");
    }

    public function resetPasswordDefault($id)
    {
        $user = User::findOrFail($id);
        $defaultPass = 'Beekopi123!';
        $user->update(['password' => Hash::make($defaultPass)]);

        AuditLog::create([
            'user_id'   => auth()->id(),
            'aktivitas' => "Mereset kata sandi akun {$user->name} ke kata sandi standar",
        ]);

        session()->flash('message', "Kata sandi '{$user->name}' berhasil direset menjadi: {$defaultPass}");
    }

    public function hapusUser($id)
    {
        // Self-Protection Guard: Mencegah Admin menghapus akunnya sendiri
        if ((int) $id === (int) auth()->id()) {
            session()->flash('error', 'Gagal! Anda tidak dapat menghapus akun Admin yang sedang Anda gunakan.');
            return;
        }

        $user = User::findOrFail($id);
        $namaUser = $user->name;
        $emailUser = $user->email;

        try {
            $user->delete();

            AuditLog::create([
                'user_id'   => auth()->id(),
                'aktivitas' => "Menghapus permanen akun pengguna: {$namaUser} ({$emailUser})",
            ]);

            session()->flash('message', "Akun pengguna '{$namaUser}' berhasil dihapus permanen.");
        } catch (QueryException $e) {
            session()->flash(
                'error',
                "Akun '{$namaUser}' tidak dapat dihapus permanen karena sudah memiliki riwayat transaksi/laporan di sistem. Silakan gunakan tombol 'Nonaktifkan'."
            );
        }
    }

    public function render()
    {
        // Query User dengan Grouped Closure Search
        $users = User::with(['role', 'lapak'])
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->where('role_id', $this->roleFilter);
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.kelola-user', [
            'users'  => $users,
            'roles'  => Role::all(),
            'lapaks' => Lapak::orderBy('nama_lapak', 'asc')->get(),
        ])->layout('layouts.app');
    }
}
