<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6 w-full">

            <!-- Header & Tombol Tambah Varian -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Modul Peracikan & Resep Produk</h1>
                    <p class="text-xs text-gray-500">Kelola komposisi resep per cup dan eksekusi peracikan batch kopi di
                        Gudang Pusat.</p>
                </div>
                <button wire:click="openModalTambah"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center gap-1.5 w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    + Tambah Varian & Resep
                </button>
            </div>

            <!-- Flash Alert -->
            @if (session()->has('message'))
                <div
                    class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg font-medium">
                    {{ session('message') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Section 1: Form Batch Racik Kopi & Preview (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Eksekusi Peracikan Batch</h2>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1 text-xs">Pilih Varian Produk</label>
                        <select wire:model.live="produk_id"
                            class="w-full px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                            <option value="">-- Pilih Varian Produk --</option>
                            @foreach ($produks as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_produk }} (Rp
                                    {{ number_format($p->harga_jual, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1 text-xs">Jumlah Porsi / Batch (Cup)</label>
                        <input type="number" wire:model.live="jumlah_cup" min="1"
                            class="w-full px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                    </div>

                    <button wire:click="prosesRacik" @if (!$produk_id || !$stokCukup) disabled @endif
                        class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 text-white font-bold rounded-lg transition shadow-sm text-xs">
                        Proses Peracikan & Auto-Deduct
                    </button>
                </div>

                <!-- Preview Kebutuhan Bahan -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-3">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Kebutuhan Bahan Baku Mentah
                    </h2>

                    @if (!empty($previewBahan))
                        <div class="divide-y divide-gray-100 border-t border-b border-gray-100 my-2">
                            @foreach ($previewBahan as $item)
                                <div class="py-2.5 flex justify-between items-center text-xs">
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $item['nama_bahan'] }}</p>
                                        <p class="text-gray-400">Stok Gudang: {{ $item['stok_text'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p
                                            class="font-extrabold {{ $item['cukup'] ? 'text-amber-600' : 'text-rose-600' }}">
                                            -{{ $item['kebutuhan_text'] }}
                                        </p>
                                        @if (!$item['cukup'])
                                            <span class="text-[10px] text-rose-500 font-bold">Stok Kurang!</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center text-gray-400 text-xs">
                            Pilih produk terlebih dahulu untuk kalkulasi kebutuhan bahan.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section 2: Tabel Full Width -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="font-bold text-sm text-gray-800">Daftar Varian Produk & Komposisi Resep</h2>
                </div>

                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse text-xs min-w-full">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Nama Varian</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Stok Gudang</th>
                                <th class="py-3 px-4">Estimasi HPP</th>
                                <th class="py-3 px-4">Harga Jual</th>
                                <th class="py-3 px-4">Komposisi Resep (per 1 Cup)</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($produks as $p)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $p->nama_produk }}</td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-semibold">{{ $p->kategori }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span
                                            class="inline-block px-2.5 py-1 bg-emerald-50 text-emerald-700 font-extrabold rounded-lg border border-emerald-200 whitespace-nowrap">
                                            {{ number_format($p->stok_pusat ?? 0, 0, ',', '.') }} cup
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-gray-500 whitespace-nowrap">Rp
                                        {{ number_format($p->hpp ?? 0, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4 font-extrabold text-amber-600 whitespace-nowrap">Rp
                                        {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-wrap gap-1.5 max-w-md">
                                            @foreach ($p->reseps as $r)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 bg-amber-50 text-amber-800 rounded border border-amber-100 text-[10px] whitespace-nowrap">
                                                    {{ $r->bahanBaku->nama_bahan ?? '-' }}:
                                                    &nbsp;<b>{{ $this->formatSatuan($r->takaran_per_cup, $r->satuan) }}</b>
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <button wire:click="openModalEdit({{ $p->id }})"
                                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold text-[11px] transition">
                                            Edit Resep
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-gray-400">Belum ada varian produk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Section -->
            @if ($showModalVarian)
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                            <h3 class="text-base font-bold text-gray-800">
                                {{ $isEditMode ? 'Edit Varian & Resep Produk' : 'Tambah Varian Baru & Resep' }}</h3>
                            <button wire:click="$set('showModalVarian', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                        </div>

                        <!-- Form Informasi Produk -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div class="sm:col-span-2">
                                <label class="block font-medium text-gray-700 mb-1">Nama Varian Produk</label>
                                <input type="text" wire:model="nama_produk"
                                    placeholder="Contoh: Kopi Susu Butterscotch"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                @error('nama_produk')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Kategori</label>
                                <select wire:model="kategori"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
                                    <option value="Kopi">Kopi</option>
                                    <option value="Non-Kopi">Non-Kopi</option>
                                    <option value="Snack">Snack</option>
                                </select>
                                @error('kategori')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Harga Jual (Rp)</label>
                                <input type="number" wire:model="harga_jual" placeholder="15000"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                @error('harga_jual')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Input Komposisi Resep Dinamis -->
                        <div class="pt-3 border-t border-gray-100 space-y-3">
                            <div class="flex justify-between items-center">
                                <label class="font-bold text-xs uppercase tracking-wider text-amber-600">Komposisi
                                    Takaran (per 1 Cup)</label>
                                <button type="button" wire:click="tambahRowKomposisi"
                                    class="text-xs text-amber-600 font-bold hover:underline">+ Tambah Bahan</button>
                            </div>

                            @foreach ($komposisi as $index => $row)
                                <div class="flex items-center gap-2 text-xs">
                                    <div class="flex-1">
                                        <select wire:model="komposisi.{{ $index }}.bahan_baku_id"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            @foreach ($allBahanBaku as $bb)
                                                <option value="{{ $bb->id }}">{{ $bb->nama_bahan }} (stok:
                                                    {{ $bb->stok_sekarang }} {{ $bb->satuan }})</option>
                                            @endforeach
                                        </select>
                                        @error("komposisi.{$index}.bahan_baku_id")
                                            <span class="text-[10px] text-rose-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="w-28">
                                        <input type="number" step="0.01"
                                            wire:model="komposisi.{{ $index }}.takaran_per_cup"
                                            placeholder="Takaran"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                        @error("komposisi.{$index}.takaran_per_cup")
                                            <span class="text-[10px] text-rose-500">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="w-24">
                                        <select wire:model="komposisi.{{ $index }}.satuan"
                                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
                                            <option value="gram">gram</option>
                                            <option value="ml">ml</option>
                                            <option value="pcs">pcs</option>
                                            <option value="set">set</option>
                                        </select>
                                    </div>
                                    @if (count($komposisi) > 1)
                                        <button type="button" wire:click="hapusRowKomposisi({{ $index }})"
                                            class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg font-bold">&times;</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 text-xs">
                            <button wire:click="$set('showModalVarian', false)"
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-semibold">Batal</button>
                            <button wire:click="simpanVarian"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg transition shadow-sm">
                                {{ $isEditMode ? 'Simpan Perubahan' : 'Tambah Varian' }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
