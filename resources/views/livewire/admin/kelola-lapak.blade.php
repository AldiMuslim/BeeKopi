<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Armada Lapak</h1>
                <p class="text-sm text-gray-500">Atur titik lokasi cabang dan status operasional armada jualan.</p>
            </div>
            <button wire:click="openModalLapak" class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-medium text-sm rounded-lg shadow-sm transition">
                + Tambah Lapak Baru
            </button>
        </div>

        @if (session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($lapaks as $l)
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between space-y-3">
                    <div>
                        <div class="flex justify-between items-start">
                            <h3 class="font-bold text-gray-800 text-base">{{ $l->nama_lapak }}</h3>
                            @if ($l->status === 'aktif')
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 font-bold rounded-full text-xs">Aktif</span>
                            @elseif($l->status === 'libur')
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-700 font-bold rounded-full text-xs">Libur</span>
                            @else
                                <span class="px-2.5 py-1 bg-rose-100 text-rose-700 font-bold rounded-full text-xs">Perbaikan</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ $l->lokasi ?? 'Lokasi belum diatur' }}</p>
                    </div>

                    <div class="pt-3 border-t border-gray-50 flex justify-between items-center text-xs">
                        <span class="text-gray-400">Staff Sales: <strong class="text-gray-700">{{ $l->users_count }} orang</strong></span>
                        <button wire:click="editLapak({{ $l->id }})" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-md">
                            Edit Lapak
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-gray-400 text-sm">
                    Belum ada armada lapak yang terdaftar.
                </div>
            @endforelse
        </div>

        @if ($showModalLapak)
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-800">{{ $lapak_id ? 'Edit Data Lapak' : 'Tambah Armada Lapak Baru' }}</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Nama Lapak / Cabang</label>
                            <input type="text" wire:model="nama_lapak" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500" placeholder="Contoh: Lapak A - Simpang Empat">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Lokasi / Alamat</label>
                            <textarea wire:model="lokasi" rows="2" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"></textarea>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Status Operasional</label>
                            <select wire:model="status" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                <option value="aktif">Aktif</option>
                                <option value="libur">Libur</option>
                                <option value="perbaikan">Dalam Perbaikan</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button wire:click="$set('showModalLapak', false)" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">Batal</button>
                        <button wire:click="simpanLapak" class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">Simpan Lapak</button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>