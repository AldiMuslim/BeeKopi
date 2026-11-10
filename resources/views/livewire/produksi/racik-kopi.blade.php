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
                    class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center gap-1.5 w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    + Tambah Varian & Resep
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
            @if (session()->has('error'))
                <div
                    class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl font-semibold flex justify-between items-center shadow-sm">
                    <span>{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()"
                        class="text-rose-900 font-bold">&times;</button>
                </div>
            @endif

            <!-- Section 1: Form Batch Racik Kopi & Preview (Grid 2 Kolom) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
                <!-- Form Eksekusi Racik -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Eksekusi Peracikan Batch
                        </h2>
                        @if ($produk_id && $maxBatchCapacity > 0)
                            <span
                                class="px-2.5 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-bold">
                                Kapasitas Stok: Maks {{ $maxBatchCapacity }} Cup
                            </span>
                        @endif
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Pilih Varian Produk:</label>
                            <select wire:model.live="produk_id"
                                class="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-amber-500 bg-white font-bold text-gray-800">
                                <option value="">-- Pilih Varian Produk --</option>
                                @foreach ($produks as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama_produk }} (Stok Ready:
                                        {{ $p->stok_pusat ?? 0 }} Cup)</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Jumlah Porsi / Batch (Cup):</label>
                            <input type="number" wire:model.live="jumlah_cup" min="1"
                                class="w-full px-3 py-2 border rounded-xl text-xs focus:ring-2 focus:ring-amber-500 bg-white font-black text-gray-900">
                        </div>

                        <button wire:click="prosesRacik" wire:loading.attr="disabled"
                            @if (!$produk_id || !$stokCukup) disabled @endif
                            class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-bold rounded-xl transition shadow-sm text-xs flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="prosesRacik">Proses Peracikan & Auto-Deduct
                                Stok</span>
                            <span wire:loading wire:target="prosesRacik" class="flex items-center gap-1.5">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Memproses Peracikan Batch...
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Preview Kebutuhan Bahan -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-3">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600 border-b border-gray-100 pb-2">
                        Kebutuhan Bahan Baku Mentah</h2>

                    @if (!empty($previewBahan))
                        <div
                            class="divide-y divide-gray-100 border-t border-b border-gray-100 my-2 max-h-56 overflow-y-auto pr-1">
                            @foreach ($previewBahan as $item)
                                <div class="py-2 flex justify-between items-center text-xs">
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $item['nama_bahan'] }}</p>
                                        <p class="text-gray-400 text-[10px]">Stok Gudang: {{ $item['stok_text'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black {{ $item['cukup'] ? 'text-amber-600' : 'text-rose-600' }}">
                                            -{{ $item['kebutuhan_text'] }}
                                        </p>
                                        @if (!$item['cukup'])
                                            <span class="text-[9px] text-rose-600 font-bold block">Stok Tidak
                                                Cukup!</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center text-gray-400 text-xs italic">
                            Pilih varian produk terlebih dahulu untuk kalkulasi otomatis kebutuhan bahan.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Section 2: Tabel Full Width Varian & Resep -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">
                <div
                    class="p-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50">
                    <div>
                        <h2 class="font-bold text-xs uppercase text-gray-800">Daftar Varian Produk & Komposisi Resep
                        </h2>
                        <p class="text-[11px] text-gray-400">Master formulasi takaran per 1 cup serta estimasi HPP
                            produk.</p>
                    </div>
                    <div class="w-full sm:w-64">
                        <input type="text" wire:model.live.debounce.300ms="searchVarian"
                            placeholder="Cari varian produk..."
                            class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                    </div>
                </div>

                <div class="overflow-x-auto w-full text-xs">
                    <table class="w-full text-left border-collapse min-w-full">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Nama Varian</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Stok Ready</th>
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
                                            class="inline-block px-2.5 py-1 bg-emerald-50 text-emerald-700 font-extrabold rounded-lg border border-emerald-200">
                                            {{ number_format($p->stok_pusat ?? 0, 0, ',', '.') }} cup
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-gray-600 whitespace-nowrap">
                                        Rp {{ number_format($p->hpp ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4 font-extrabold text-amber-600 whitespace-nowrap">
                                        Rp {{ number_format($p->harga_jual, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-wrap gap-1.5 max-w-md">
                                            @forelse ($p->reseps as $r)
                                                <span
                                                    class="inline-flex items-center px-2 py-1 bg-amber-50 text-amber-800 rounded border border-amber-100 text-[10px] whitespace-nowrap">
                                                    {{ $r->bahanBaku->nama_bahan ?? '-' }}:
                                                    &nbsp;<b>{{ $this->formatSatuan($r->takaran_per_cup, $r->satuan) }}</b>
                                                </span>
                                            @empty
                                                <span class="text-rose-500 italic text-[10px] font-bold">Belum ada
                                                    resep!</span>
                                            @endforelse
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
                                    <td colspan="7" class="py-8 text-center text-gray-400 italic">Belum ada varian
                                        produk yang cocok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 3: Histori Batch Peracikan Gudang -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="font-bold text-xs uppercase text-gray-800">Histori Peracikan Batch Gudang</h2>
                    <p class="text-[11px] text-gray-400">Jejak pemotongan bahan baku mentah otomatis akibat eksekusi
                        peracikan batch.</p>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Waktu Racik</th>
                                <th class="py-3 px-4">Bahan Terpotong</th>
                                <th class="py-3 px-4">Jumlah Terpakai</th>
                                <th class="py-3 px-4">Keterangan Batch</th>
                                <th class="py-3 px-4">Operator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($historiPeracikan as $log)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d M Y, H:i') }}</td>
                                    <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $log->bahanBaku->nama_bahan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-black text-rose-600 whitespace-nowrap">
                                        -{{ number_format($log->jumlah, 2, ',', '.') }} {{ $log->satuan }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 font-semibold">{{ $log->keterangan }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ $log->user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-400 italic">Belum ada riwayat
                                        peracikan batch.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL FORM VARIAN & RESEP DINAMIS -->
            @if ($showModalVarian)
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in duration-150">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                            <h3 class="text-base font-bold text-gray-800">
                                {{ $isEditMode ? 'Edit Varian & Resep Produk' : 'Tambah Varian Baru & Resep' }}
                            </h3>
                            <button wire:click="$set('showModalVarian', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <!-- Form Informasi Produk -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-gray-700 mb-1">Nama Varian Produk:</label>
                                <input type="text" wire:model="nama_produk"
                                    placeholder="Contoh: Kopi Susu Butterscotch"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                @error('nama_produk')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Kategori:</label>
                                <select wire:model="kategori"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                    <option value="Kopi">Kopi</option>
                                    <option value="Non-Kopi">Non-Kopi</option>
                                    <option value="Snack">Snack</option>
                                </select>
                                @error('kategori')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Harga Jual (Rp):</label>
                                <input type="number" wire:model="harga_jual" placeholder="15000"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold text-amber-700">
                                @error('harga_jual')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
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
                                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-medium">
                                            <option value="">-- Pilih Bahan Baku --</option>
                                            @foreach ($allBahanBaku as $bb)
                                                <option value="{{ $bb->id }}">{{ $bb->nama_bahan }} (Stok:
                                                    {{ number_format($bb->stok_sekarang, 2, ',', '.') }}
                                                    {{ $bb->satuan }})</option>
                                            @endforeach
                                        </select>
                                        @error("komposisi.{$index}.bahan_baku_id")
                                            <span
                                                class="text-[10px] text-rose-500 block font-semibold">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="w-28">
                                        <input type="number" step="0.01"
                                            wire:model="komposisi.{{ $index }}.takaran_per_cup"
                                            placeholder="Takaran"
                                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold">
                                        @error("komposisi.{$index}.takaran_per_cup")
                                            <span
                                                class="text-[10px] text-rose-500 block font-semibold">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="w-24">
                                        <select wire:model="komposisi.{{ $index }}.satuan"
                                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                            <option value="gram">gram</option>
                                            <option value="ml">ml</option>
                                            <option value="pcs">pcs</option>
                                            <option value="set">set</option>
                                        </select>
                                    </div>
                                    @if (count($komposisi) > 1)
                                        <button type="button" wire:click="hapusRowKomposisi({{ $index }})"
                                            class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg font-bold text-base">&times;</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 text-xs">
                            <button type="button" wire:click="$set('showModalVarian', false)"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">Batal</button>
                            <button type="button" wire:click="simpanVarian" wire:loading.attr="disabled"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow-sm disabled:opacity-50">
                                <span wire:loading.remove
                                    wire:target="simpanVarian">{{ $isEditMode ? 'Simpan Perubahan' : 'Tambah Varian' }}</span>
                                <span wire:loading wire:target="simpanVarian">Kalkulasi & Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
