<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Manajemen Pengguna</h1>
                    <p class="text-xs text-gray-500">Kelola akun pengguna, hak akses peran, dan pemetaan akun sales ke
                        armada Lapak.</p>
                </div>
                <button wire:click="openModalUser"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    + Tambah Pengguna Baru
                </button>
            </div>

            <!-- Flash Alert Notifications -->
            @if (session()->has('message'))
                <div
                    class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl font-semibold flex justify-between items-center shadow-sm">
                    <span>{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.remove()"
                        class="text-emerald-900 font-bold">&times;</button>
                </div>
            @endif
            @if (session()->has('error'))
                <div
                    class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl font-semibold flex justify-between items-center shadow-sm">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()"
                        class="text-rose-900 font-bold">&times;</button>
                </div>
            @endif

            <!-- Filter & Search Bar -->
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="w-full sm:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama atau email pengguna..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div class="w-full sm:w-56">
                    <select wire:model.live="roleFilter"
                        class="w-full pl-3.5 pr-9 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700 cursor-pointer">
                        <option value="">-- Semua Peran (Role) --</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->label ?? ($r->nama_role ?? $r->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Tabel Master User -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-[11px] font-semibold uppercase text-gray-500 whitespace-nowrap">
                                <th class="py-3 px-4">Nama Lengkap</th>
                                <th class="py-3 px-4">Email</th>
                                <th class="py-3 px-4">Hak Akses (Role)</th>
                                <th class="py-3 px-4">Pemetaan Lapak</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Aktivitas Terakhir</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($users as $u)
                                @php
                                    $roleName = strtolower($u->role->name ?? ($u->role->label ?? ''));
                                    $badgeClass = 'bg-gray-100 text-gray-700';
                                    if (str_contains($roleName, 'admin')) {
                                        $badgeClass = 'bg-purple-100 text-purple-800 border-purple-200';
                                    } elseif (str_contains($roleName, 'management')) {
                                        $badgeClass = 'bg-blue-100 text-blue-800 border-blue-200';
                                    } elseif (
                                        str_contains($roleName, 'produksi') ||
                                        str_contains($roleName, 'gudang')
                                    ) {
                                        $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
                                    } elseif (str_contains($roleName, 'sales') || str_contains($roleName, 'barista')) {
                                        $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $u->name }}
                                        @if ($u->id === auth()->id())
                                            <span
                                                class="text-[9px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-extrabold ml-1">(Anda)</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-600 font-medium whitespace-nowrap">
                                        {{ $u->email }}
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span
                                            class="px-2.5 py-1 font-bold rounded-lg text-[10px] border {{ $badgeClass }}">
                                            {{ $u->role->label ?? ($u->role->name ?? 'Tanpa Role') }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                        @if ($u->lapak)
                                            <span class="text-amber-700 font-bold">📍 {{ $u->lapak->nama_lapak }}</span>
                                        @else
                                            <span class="text-gray-400 italic font-normal">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if (($u->status ?? 'aktif') === 'aktif')
                                            <span
                                                class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Aktif</span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px]">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-gray-500 whitespace-nowrap">
                                        {{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() : 'Belum Pernah' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button wire:click="editUser({{ $u->id }})"
                                                class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition">
                                                Edit
                                            </button>
                                            <button wire:click="resetPasswordDefault({{ $u->id }})"
                                                wire:confirm="Reset kata sandi '{{ $u->name }}' ke sandi standar 'Beekopi123!'?"
                                                class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold rounded-lg transition">
                                                Reset Sandi
                                            </button>
                                            @if ($u->id !== auth()->id())
                                                <button wire:click="toggleStatus({{ $u->id }})"
                                                    class="px-2.5 py-1 {{ ($u->status ?? 'aktif') === 'aktif' ? 'bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200' }} font-bold rounded-lg transition">
                                                    {{ ($u->status ?? 'aktif') === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                                <button wire:click="hapusUser({{ $u->id }})"
                                                    wire:confirm="Yakin ingin menghapus permanen akun '{{ $u->name }}' ({{ $u->email }})?"
                                                    class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg transition">
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 italic">Belum ada akun
                                        pengguna yang sesuai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>

            <!-- MODAL FORM USER -->
            @if ($showModalUser)
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 animate-in fade-in zoom-in duration-150 text-xs">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <h3 class="text-base font-bold text-gray-800">
                                {{ $user_id ? 'Edit Akun Pengguna' : 'Tambah Akun Pengguna Baru' }}
                            </h3>
                            <button wire:click="$set('showModalUser', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <form wire:submit.prevent="simpanUser" class="space-y-3">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Nama Lengkap:</label>
                                <input type="text" wire:model="name" placeholder="Contoh: Ahmad Barista"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                @error('name')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Alamat Email:</label>
                                <input type="email" wire:model="email" placeholder="ahmad@beekopi.com"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                @error('email')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">
                                    Kata Sandi {{ $user_id ? '(Kosongkan jika tidak ingin diubah)' : '' }}:
                                </label>
                                <input type="password" wire:model="password" placeholder="••••••••"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                @error('password')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Peran Akses (Role):</label>
                                    <select wire:model="role_id"
                                        class="w-full pl-3 pr-8 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-800">
                                        <option value="">-- Pilih Peran --</option>
                                        @foreach ($roles as $r)
                                            <option value="{{ $r->id }}">{{ $r->label ?? $r->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Status Akun:</label>
                                    <select wire:model="status"
                                        class="w-full pl-3 pr-8 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-800">
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                    @error('status')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Pemetaan Lapak (Khusus Akun
                                    Sales/Barista):</label>
                                <select wire:model="lapak_id"
                                    class="w-full pl-3 pr-8 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                    <option value="">-- Tidak Terhubung ke Lapak Manapun --</option>
                                    @foreach ($lapaks as $l)
                                        <option value="{{ $l->id }}">{{ $l->nama_lapak }}</option>
                                    @endforeach
                                </select>
                                @error('lapak_id')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button type="button" wire:click="$set('showModalUser', false)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">Batal</button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-amber-600 text-white rounded-xl font-bold hover:bg-amber-700 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="simpanUser">Simpan Akun</span>
                                    <span wire:loading wire:target="simpanUser">Menyimpan...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
