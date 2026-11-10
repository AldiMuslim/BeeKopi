<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Manajemen Armada Lapak</h1>
                    <p class="text-xs text-gray-500">Atur titik lokasi cabang, pemetaan sales, dan status operasional
                        armada jualan Bee Kopi.</p>
                </div>
                <button wire:click="openModalLapak"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    + Tambah Lapak Baru
                </button>
            </div>

            <!-- Flash Alert -->
            @if (session()->has('message'))
                <div
                    class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl font-semibold flex justify-between items-center shadow-sm">
                    <span>{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.remove()"
                        class="text-emerald-900 font-bold">&times;</button>
                </div>
            @endif

            <!-- Filter & Search Bar -->
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="w-full sm:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama lapak atau lokasi..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div class="flex items-center gap-1.5 text-xs w-full sm:w-auto">
                    <span class="text-gray-400 font-medium mr-1">Status:</span>
                    <button wire:click="$set('statusFilter', '')"
                        class="px-2.5 py-1 rounded-lg font-bold transition {{ $statusFilter === '' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua
                    </button>
                    <button wire:click="$set('statusFilter', 'aktif')"
                        class="px-2.5 py-1 rounded-lg font-bold transition {{ $statusFilter === 'aktif' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Aktif
                    </button>
                    <button wire:click="$set('statusFilter', 'libur')"
                        class="px-2.5 py-1 rounded-lg font-bold transition {{ $statusFilter === 'libur' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Libur
                    </button>
                    <button wire:click="$set('statusFilter', 'perbaikan')"
                        class="px-2.5 py-1 rounded-lg font-bold transition {{ $statusFilter === 'perbaikan' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Perbaikan
                    </button>
                </div>
            </div>

            <!-- Grid Card Lapak -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($lapaks as $l)
                    <div
                        class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-base">{{ $l->nama_lapak }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">📍
                                        {{ $l->lokasi ?? 'Lokasi alamat belum diatur' }}</p>
                                </div>
                                <div>
                                    @if ($l->status === 'aktif')
                                        <span
                                            class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-extrabold rounded-full text-[10px]">Aktif</span>
                                    @elseif ($l->status === 'libur')
                                        <span
                                            class="px-2.5 py-1 bg-amber-100 text-amber-800 font-extrabold rounded-full text-[10px]">Libur</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 bg-rose-100 text-rose-800 font-extrabold rounded-full text-[10px]">Perbaikan</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Daftar Staf Sales Terhubung -->
                            <div class="p-3 bg-gray-50 rounded-xl space-y-1 text-xs">
                                <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block">Tim
                                    Sales Ditautkan:</span>
                                @if ($l->users->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($l->users as $userSales)
                                            <span
                                                class="px-2 py-0.5 bg-white border border-gray-200 text-gray-700 rounded-md text-[11px] font-semibold">
                                                👤 {{ $userSales->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-rose-500 italic text-[11px]">Belum ada staf sales yang terhubung.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Quick Action & Edit Button -->
                        <div class="pt-3 border-t border-gray-100 flex justify-between items-center text-xs">
                            <!-- Quick Status Toggle -->
                            <div class="flex items-center gap-1">
                                @if ($l->status !== 'aktif')
                                    <button wire:click="toggleQuickStatus({{ $l->id }}, 'aktif')"
                                        class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold border border-emerald-200 rounded-lg text-[10px] transition">
                                        Set Aktif
                                    </button>
                                @endif
                                @if ($l->status !== 'libur')
                                    <button wire:click="toggleQuickStatus({{ $l->id }}, 'libur')"
                                        class="px-2 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold border border-amber-200 rounded-lg text-[10px] transition">
                                        Set Libur
                                    </button>
                                @endif
                                @if ($l->status !== 'perbaikan')
                                    <button wire:click="toggleQuickStatus({{ $l->id }}, 'perbaikan')"
                                        class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold border border-rose-200 rounded-lg text-[10px] transition">
                                        Set Perbaikan
                                    </button>
                                @endif
                            </div>

                            <button wire:click="editLapak({{ $l->id }})"
                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-[11px] transition">
                                Edit Lapak
                            </button>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-full py-12 text-center text-gray-400 italic text-xs bg-white rounded-2xl border border-gray-100">
                        Belum ada data armada lapak yang sesuai.
                    </div>
                @endforelse
            </div>

            <!-- MODAL FORM LAPAK -->
            @if ($showModalLapak)
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 animate-in fade-in zoom-in duration-150 text-xs">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <h3 class="text-base font-bold text-gray-800">
                                {{ $lapak_id ? 'Edit Data Lapak' : 'Tambah Armada Lapak Baru' }}
                            </h3>
                            <button wire:click="$set('showModalLapak', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <form wire:submit.prevent="simpanLapak" class="space-y-3">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Nama Lapak / Cabang:</label>
                                <input type="text" wire:model="nama_lapak"
                                    placeholder="Contoh: Lapak A - Simpang Empat"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-800">
                                @error('nama_lapak')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Lokasi / Alamat Lengkap:</label>
                                <textarea wire:model="lokasi" rows="3" placeholder="Masukkan alamat ringkas atau patokan posisi jualan..."
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white"></textarea>
                                @error('lokasi')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Status Operasional:</label>
                                <select wire:model="status"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-800">
                                    <option value="aktif">Aktif</option>
                                    <option value="libur">Libur</option>
                                    <option value="perbaikan">Dalam Perbaikan</option>
                                </select>
                                @error('status')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button type="button" wire:click="$set('showModalLapak', false)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">Batal</button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-amber-600 text-white rounded-xl font-bold hover:bg-amber-700 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="simpanLapak">Simpan Lapak</span>
                                    <span wire:loading wire:target="simpanLapak">Menyimpan...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
