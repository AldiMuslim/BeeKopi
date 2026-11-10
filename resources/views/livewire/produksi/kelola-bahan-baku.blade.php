<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Gudang Pusat & Bahan Baku</h1>
                    <p class="text-xs text-gray-500">Kelola stok bahan mentah, pemakaian produksi, belanja harian, dan
                        pencatatan waste.</p>
                </div>
                <button wire:click="openModalBahan"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    + Tambah Bahan Baku Baru
                </button>
            </div>

            <!-- Flash Notifications -->
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

            <!-- Cards Summary Ringkasan Stok -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-gray-400 text-[11px] font-semibold block uppercase">Total Varian Bahan</span>
                        <span class="text-xl font-black text-gray-800">{{ $totalBahan }} Jenis</span>
                    </div>
                    <div
                        class="w-9 h-9 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 font-bold text-xs">
                        📦</div>
                </div>

                <div wire:click="filterStokTab('semua')"
                    class="p-4 bg-white rounded-xl shadow-sm border border-emerald-100 cursor-pointer hover:bg-emerald-50/30 transition flex items-center justify-between">
                    <div>
                        <span class="text-emerald-600 text-[11px] font-semibold block uppercase">Stok Status Aman</span>
                        <span class="text-xl font-black text-emerald-700">{{ $countAman }} Jenis</span>
                    </div>
                    <div
                        class="w-9 h-9 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-700 font-bold text-xs">
                        ✓</div>
                </div>

                <div wire:click="filterStokTab('menipis')"
                    class="p-4 bg-white rounded-xl shadow-sm border border-amber-100 cursor-pointer hover:bg-amber-50/30 transition flex items-center justify-between">
                    <div>
                        <span class="text-amber-600 text-[11px] font-semibold block uppercase">Stok Menipis / Perlu
                            Restok</span>
                        <span class="text-xl font-black text-amber-700">{{ $countMenipis }} Jenis</span>
                    </div>
                    <div
                        class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center text-amber-700 font-bold text-xs">
                        ⚠️</div>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between text-xs">
                <div class="relative w-full sm:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama bahan baku atau kategori..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                    @if ($search)
                        <button wire:click="$set('search', '')"
                            class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button wire:click="filterStokTab('semua')"
                        class="px-3 py-1.5 rounded-lg font-bold transition {{ $filter_stok === 'semua' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua Bahan ({{ $totalBahan }})
                    </button>
                    <button wire:click="filterStokTab('menipis')"
                        class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5 {{ $filter_stok === 'menipis' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <span>Stok Menipis</span>
                        @if ($countMenipis > 0)
                            <span
                                class="px-1.5 py-0.2 bg-rose-800 text-rose-100 rounded-full text-[10px]">{{ $countMenipis }}</span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Tabel Utama Bahan Baku -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-[11px] font-semibold uppercase text-gray-500 whitespace-nowrap">
                                <th class="py-3 px-4">Nama Bahan</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Sisa Stok</th>
                                <th class="py-3 px-4">Stok Min.</th>
                                <th class="py-3 px-4">Harga / Satuan</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($bahanBakus as $bahan)
                                @php
                                    $isMenipis = $bahan->stok_sekarang <= $bahan->stok_minimum;
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $bahan->nama_bahan }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md text-[10px] font-semibold">{{ $bahan->kategori }}</span>
                                    </td>
                                    <td
                                        class="py-3 px-4 font-black whitespace-nowrap {{ $isMenipis ? 'text-rose-600' : 'text-gray-800' }}">
                                        {{ number_format($bahan->stok_sekarang, 2, ',', '.') }} {{ $bahan->satuan }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 whitespace-nowrap">
                                        {{ number_format($bahan->stok_minimum, 2, ',', '.') }} {{ $bahan->satuan }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold whitespace-nowrap">
                                        Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }} /
                                        {{ $bahan->satuan }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if ($isMenipis)
                                            <span
                                                class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px] animate-pulse">Stok
                                                Menipis</span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Aman</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button wire:click="openModalStok({{ $bahan->id }})"
                                                class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-bold border border-emerald-200 transition">
                                                Update Stok
                                            </button>
                                            <button wire:click="editBahan({{ $bahan->id }})"
                                                class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold transition">
                                                Edit
                                            </button>
                                            <button wire:click="hapusBahan({{ $bahan->id }})"
                                                wire:confirm="Yakin ingin menghapus master bahan '{{ $bahan->nama_bahan }}'?"
                                                class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg font-bold transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 italic">Belum ada data
                                        bahan baku yang sesuai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $bahanBakus->links() }}
                </div>
            </div>

            <!-- Tab Log Riwayat Pemakaian & Belanja -->
            <div x-data="{ tabLog: 'pemakaian' }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Tab Switcher -->
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div>
                        <h2 class="font-bold text-xs text-gray-800 uppercase">Catatan & Histori Log Stok Gudang</h2>
                        <p class="text-[11px] text-gray-400">Jejak audit masuk (belanja) dan keluar (pemakaian / waste)
                            bahan baku.</p>
                    </div>
                    <div class="flex gap-2 text-xs">
                        <button @click="tabLog = 'pemakaian'"
                            :class="tabLog === 'pemakaian' ? 'bg-amber-600 text-white font-bold' :
                                'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-lg transition">
                            Log Pemakaian & Waste (- Out)
                        </button>
                        <button @click="tabLog = 'belanja'"
                            :class="tabLog === 'belanja' ? 'bg-emerald-600 text-white font-bold' :
                                'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-lg transition">
                            Log Belanja Bahan (+ In)
                        </button>
                    </div>
                </div>

                <!-- Log Pemakaian Produksi -->
                <div x-show="tabLog === 'pemakaian'" class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Bahan Baku</th>
                                <th class="py-3 px-4">Jumlah Terpakai</th>
                                <th class="py-3 px-4">Keterangan</th>
                                <th class="py-3 px-4">Operator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($logPemakaian as $log)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d M Y, H:i') }}</td>
                                    <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $log->bahanBaku->nama_bahan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-black text-rose-600 whitespace-nowrap">
                                        -{{ number_format($log->jumlah, 2, ',', '.') }} {{ $log->satuan }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ $log->keterangan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ $log->user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-400 italic">Belum ada riwayat
                                        pemakaian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Log Belanja Bahan Baku -->
                <div x-show="tabLog === 'belanja'" class="overflow-x-auto text-xs" x-cloak>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Bahan Baku</th>
                                <th class="py-3 px-4">Jumlah Dibeli</th>
                                <th class="py-3 px-4">Total Biaya</th>
                                <th class="py-3 px-4">Keterangan</th>
                                <th class="py-3 px-4">Operator</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($logBelanja as $log)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 text-gray-500 whitespace-nowrap">
                                        {{ $log->created_at->format('d M Y, H:i') }}</td>
                                    <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $log->bahanBaku->nama_bahan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-black text-emerald-600 whitespace-nowrap">
                                        +{{ number_format($log->jumlah, 2, ',', '.') }} {{ $log->satuan }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-amber-600 whitespace-nowrap">
                                        Rp {{ number_format($log->total_biaya, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">{{ $log->keterangan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ $log->user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400 italic">Belum ada riwayat
                                        belanja.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL FORM MASTER BAHAN BAKU -->
            @if ($showModalBahan)
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 animate-in fade-in zoom-in duration-150">
                        <h3 class="text-base font-bold text-gray-800 border-b border-gray-100 pb-2">
                            {{ $bahan_id ? 'Edit Master Bahan Baku' : 'Tambah Bahan Baku Baru' }}
                        </h3>

                        <form wire:submit.prevent="simpanBahan" class="space-y-3 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Nama Bahan Baku:</label>
                                <input type="text" wire:model="nama_bahan"
                                    placeholder="Contoh: Susu UHT Diamond 1L"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                @error('nama_bahan')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Kategori:</label>
                                <input type="text" wire:model="kategori"
                                    placeholder="Bahan Basah / Biji Kopi / Syrup / Kemasan"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                @error('kategori')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Stok Saat Ini:</label>
                                    <input type="number" step="0.01" wire:model="stok_sekarang"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold">
                                    @error('stok_sekarang')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Batas Stok Minimum:</label>
                                    <input type="number" step="0.01" wire:model="stok_minimum"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold">
                                    @error('stok_minimum')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Satuan Takaran:</label>
                                    <input type="text" wire:model="satuan" placeholder="Liter, Kg, Pcs, Pack"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                    @error('satuan')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Harga Standard / Satuan
                                        (Rp):</label>
                                    <input type="number" wire:model="harga_satuan" placeholder="18000"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold">
                                    @error('harga_satuan')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button type="button" wire:click="$set('showModalBahan', false)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">Batal</button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-amber-600 text-white rounded-xl font-bold hover:bg-amber-700 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="simpanBahan">Simpan Bahan</span>
                                    <span wire:loading wire:target="simpanBahan">Menyimpan...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- MODAL FORM UPDATE STOK (BELANJA / PEMAKAIAN / WASTE) -->
            @if ($showModalStok && $selectedBahan)
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 animate-in fade-in zoom-in duration-150">

                        <div class="border-b border-gray-100 pb-2">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Pencatatan
                                Stok Gudang</span>
                            <h3 class="text-base font-extrabold text-gray-800">{{ $selectedBahan->nama_bahan }}</h3>
                            <p class="text-[11px] text-gray-500">Stok Saat Ini: <b
                                    class="text-emerald-700 font-bold">{{ number_format($selectedBahan->stok_sekarang, 2, ',', '.') }}
                                    {{ $selectedBahan->satuan }}</b></p>
                        </div>

                        <form wire:submit.prevent="updateStok" class="space-y-3 text-xs">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Jenis Pencatatan Transaksi:</label>
                                <select wire:model.live="jenis_penyesuaian"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white font-bold text-gray-800">
                                    <option value="belanja">+ Belanja Bahan Baru (Stok Masuk / Restok)</option>
                                    <option value="pemakaian">- Pemakaian Produksi Manual (Stok Keluar)</option>
                                    <option value="rusak">- Rusak / Tumpah / Kadaluarsa (Waste)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Jumlah
                                        ({{ $selectedBahan->satuan }}):</label>
                                    <input type="number" step="0.01" wire:model="jumlah_jumlah"
                                        placeholder="0.00"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white font-black text-gray-900">
                                    @error('jumlah_jumlah')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                @if ($jenis_penyesuaian === 'belanja')
                                    <div>
                                        <label class="block font-bold text-gray-700 mb-1">Harga Beli / Satuan
                                            (Rp):</label>
                                        <input type="number" wire:model="harga_beli_satuan" placeholder="0"
                                            class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white font-bold text-emerald-700">
                                        @error('harga_beli_satuan')
                                            <span
                                                class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif
                            </div>

                            @if ($jenis_penyesuaian === 'belanja' && (float) $jumlah_jumlah > 0)
                                <div
                                    class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex justify-between items-center font-bold text-[11px]">
                                    <span class="text-emerald-900">Estimasi Total Biaya Belanja:</span>
                                    <span class="text-emerald-700 text-xs font-black">Rp
                                        {{ number_format((float) $jumlah_jumlah * (float) $harga_beli_satuan, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Keterangan / Catatan:</label>
                                <input type="text" wire:model="keterangan"
                                    placeholder="{{ $jenis_penyesuaian === 'belanja' ? 'Misal: Belanja di Toko Supplier A' : ($jenis_penyesuaian === 'rusak' ? 'Misal: Kemasan bocor / basi' : 'Misal: Racik syrup botol') }}"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white">
                                @error('keterangan')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button type="button" wire:click="$set('showModalStok', false)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">Batal</button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="updateStok">Simpan Perubahan Stok</span>
                                    <span wire:loading wire:target="updateStok">Memproses...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
