<?php

namespace App\Livewire\Admin;

use App\Models\Lapak;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class KelolaUser extends Component
{
    use WithPagination;

    public $user_id;
    public $name;
    public $email;
    public $password;
    public $role_id;
    public $lapak_id;

    public $search = '';
    public $showModalUser = false;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModalUser()
    {
        $this->resetForm();
        $this->showModalUser = true;
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $this->user_id = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
        $this->lapak_id = $user->lapak_id;
        $this->password = '';

        $this->showModalUser = true;
    }

    public function simpanUser()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user_id,
            'role_id' => 'required|exists:roles,id',
            'lapak_id' => 'nullable|exists:lapaks,id',
        ];

        if (!$this->user_id || !empty($this->password)) {
            $rules['password'] = 'required|min:6';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
            'lapak_id' => $this->lapak_id ?: null,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(['id' => $this->user_id], $data);

        session()->flash('message', 'Data pengguna berhasil disimpan.');
        $this->showModalUser = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->user_id = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role_id = Role::first()?->id;
        $this->lapak_id = null;
    }

    public function render()
    {
        $users = User::with(['role', 'lapak'])
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.kelola-user', [
            'users' => $users,
            'roles' => Role::all(),
            'lapaks' => Lapak::all(),
        ])->layout('layouts.app');
    }
}