<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Master Bahan Baku Pusat</h1>
                <p class="text-sm text-gray-500">Kelola daftar persediaan bahan mentah, satuan takaran, dan ambang batas
                    stok minimum.</p>
            </div>
            <button wire:click="openModalBahan"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-medium text-sm rounded-lg shadow-sm transition">
                + Tambah Bahan Baku
            </button>
        </div>

        @if (session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500">
                            <th class="py-3 px-4">Nama Bahan Baku</th>
                            <th class="py-3 px-4">Stok Saat Ini</th>
                            <th class="py-3 px-4">Satuan</th>
                            <th class="py-3 px-4">Stok Minimum</th>
                            <th class="py-3 px-4">Status Batas</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse ($bahanBakus as $b)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $b->nama_bahan }}</td>
                                <td class="py-3 px-4 font-extrabold text-amber-600">
                                    {{ number_format($b->stok, 0, ',', '.') }}</td>
                                <td class="py-3 px-4 text-xs font-semibold text-gray-500 uppercase">{{ $b->satuan }}
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-600">
                                    {{ number_format($b->stok_minimum, 0, ',', '.') }}</td>
                                <td class="py-3 px-4">
                                    @if ($b->stok <= $b->stok_minimum)
                                        <span
                                            class="px-2.5 py-1 bg-rose-100 text-rose-700 font-bold rounded-full text-xs">Menipis
                                            / Restok</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 bg-emerald-100 text-emerald-700 font-bold rounded-full text-xs">Aman</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button wire:click="editBahan({{ $b->id }})"
                                        class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-xs font-medium">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-400 text-sm">Belum ada data bahan
                                    baku.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($showModalBahan)
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $bahan_baku_id ? 'Edit Bahan Baku' : 'Tambah Bahan Baku Baru' }}</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Nama Bahan Baku</label>
                            <input type="text" wire:model="nama_bahan"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Jumlah Stok</label>
                            <input type="number" step="any" wire:model="stok"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Satuan Takaran</label>
                            <select wire:model="satuan"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                <option value="gram">Gram (gr)</option>
                                <option value="ml">Mililiter (ml)</option>
                                <option value="pcs">Pieces (pcs)</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="liter">Liter (L)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Batas Stok Minimum</label>
                            <input type="number" step="any" wire:model="stok_minimum"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button wire:click="$set('showModalBahan', false)"
                            class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">Batal</button>
                        <button wire:click="simpanBahan"
                            class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">Simpan
                            Data</button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
