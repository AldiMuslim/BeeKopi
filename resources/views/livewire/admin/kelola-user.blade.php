<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
                <p class="text-sm text-gray-500">Kelola akun, hak akses peran, dan pemetaan ke armada lapak.</p>
            </div>
            <button wire:click="openModalUser" class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-medium text-sm rounded-lg shadow-sm transition">
                + Tambah Pengguna
            </button>
        </div>

        @if (session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <input type="text" wire:model.live="search" placeholder="Cari nama atau email pengguna..." class="w-full sm:w-80 px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500">
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Hak Akses (Role)</th>
                            <th class="py-3 px-4">Armada Lapak</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse ($users as $u)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $u->name }}</td>
                                <td class="py-3 px-4 text-xs text-gray-600">{{ $u->email }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-semibold rounded-md text-xs">
                                        {{ $u->role->label ?? 'Tanpa Role' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs font-medium text-gray-600">
                                    {{ $u->lapak->nama_lapak ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button wire:click="editUser({{ $u->id }})" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs font-medium">
                                        Edit / Reset Pass
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 text-sm">Belum ada data akun pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        </div>

        @if ($showModalUser)
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-800">{{ $user_id ? 'Edit Akun Pengguna' : 'Tambah Akun Baru' }}</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Alamat Email</label>
                            <input type="email" wire:model="email" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Kata Sandi {{ $user_id ? '(Isi jika ingin ubah)' : '' }}</label>
                            <input type="password" wire:model="password" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Peran Akses (Role)</label>
                            <select wire:model="role_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                @foreach ($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Pemetaan Lapak (Khusus Sales)</label>
                            <select wire:model="lapak_id" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                <option value="">-- Tidak Terhubung ke Lapak --</option>
                                @foreach ($lapaks as $l)
                                    <option value="{{ $l->id }}">{{ $l->nama_lapak }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button wire:click="$set('showModalUser', false)" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">Batal</button>
                        <button wire:click="simpanUser" class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">Simpan Akun</button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>