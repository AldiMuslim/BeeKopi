<div>
    <div class="p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Gudang Pusat & Bahan Baku</h1>
                    <p class="text-sm text-gray-500">Kelola stok bahan mentah, pemakaian produksi, belanja harian, dan
                        pencatatan waste.</p>
                </div>
                <button wire:click="openModalBahan"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-medium text-sm rounded-lg shadow-sm transition">
                    + Tambah Bahan Baku
                </button>
            </div>

            <!-- Alert Notification -->
            @if (session()->has('message'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg font-medium">
                    {{ session('message') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-lg font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filter & Search -->
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-4 items-center justify-between">
                <input type="text" wire:model.live="search" placeholder="Cari nama bahan atau kategori..."
                    class="w-full sm:w-80 px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <!-- Tabel Utama Bahan Baku -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500">
                                <th class="py-3 px-4">Nama Bahan</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Sisa Stok</th>
                                <th class="py-3 px-4">Stok Min.</th>
                                <th class="py-3 px-4">Harga / Satuan</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @forelse ($bahanBakus as $bahan)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 font-semibold text-gray-900">{{ $bahan->nama_bahan }}</td>
                                    <td class="py-3 px-4">
                                        <span
                                            class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-md text-xs">{{ $bahan->kategori }}</span>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-gray-800">
                                        {{ number_format($bahan->stok_sekarang, 2, ',', '.') }} {{ $bahan->satuan }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-500">
                                        {{ number_format($bahan->stok_minimum, 2, ',', '.') }} {{ $bahan->satuan }}</td>
                                    <td class="py-3 px-4">Rp {{ number_format($bahan->harga_satuan, 0, ',', '.') }} /
                                        {{ $bahan->satuan }}</td>
                                    <td class="py-3 px-4">
                                        @if ($bahan->stok_sekarang <= $bahan->stok_minimum)
                                            <span
                                                class="px-2.5 py-1 bg-amber-100 text-amber-700 font-medium rounded-full text-xs animate-pulse">Menipis</span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 bg-emerald-100 text-emerald-700 font-medium rounded-full text-xs">Aman</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center space-x-2">
                                        <button wire:click="openModalStok({{ $bahan->id }})"
                                            class="px-3 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 rounded-md text-xs font-medium transition">
                                            Update Stok
                                        </button>
                                        <button wire:click="editBahan({{ $bahan->id }})"
                                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-md text-xs font-medium transition">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-gray-400 text-sm">Belum ada data
                                        bahan baku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $bahanBakus->links() }}
                </div>
            </div>

            <!-- Tab Log Riwayat Stok Pemakaian & Belanja -->
            <div x-data="{ tabLog: 'pemakaian' }"
                class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <!-- Header Tab Switcher -->
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h2 class="font-bold text-sm text-gray-800">Catatan & Histori Stok Gudang</h2>
                    <div class="flex gap-2">
                        <button @click="tabLog = 'pemakaian'"
                            :class="tabLog === 'pemakaian' ? 'bg-amber-600 text-white font-bold' :
                                'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-lg text-xs transition">
                            Log Pemakaian Produksi & Waste (- Out)
                        </button>
                        <button @click="tabLog = 'belanja'"
                            :class="tabLog === 'belanja' ? 'bg-emerald-600 text-white font-bold' :
                                'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3 py-1.5 rounded-lg text-xs transition">
                            Log Belanja Bahan (+ In)
                        </button>
                    </div>
                </div>

                <!-- Log Pemakaian Produksi -->
                <div x-show="tabLog === 'pemakaian'" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Tanggal & Waktu</th>
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
                                    <td class="py-3 px-4 font-bold text-gray-900">
                                        {{ $log->bahanBaku->nama_bahan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-extrabold text-rose-600 whitespace-nowrap">
                                        -{{ number_format($log->jumlah, 2, ',', '.') }} {{ $log->satuan }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $log->keterangan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ $log->user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada riwayat
                                        pemakaian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Log Belanja Bahan Baku -->
                <div x-show="tabLog === 'belanja'" class="overflow-x-auto" x-cloak>
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Tanggal & Waktu</th>
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
                                    <td class="py-3 px-4 font-bold text-gray-900">
                                        {{ $log->bahanBaku->nama_bahan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-extrabold text-emerald-600 whitespace-nowrap">
                                        +{{ number_format($log->jumlah, 2, ',', '.') }} {{ $log->satuan }}</td>
                                    <td class="py-3 px-4 font-bold text-amber-600 whitespace-nowrap">Rp
                                        {{ number_format($log->total_biaya, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-gray-600">{{ $log->keterangan ?? '-' }}</td>
                                    <td class="py-3 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ $log->user->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400">Belum ada riwayat belanja.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Form Master Bahan Baku -->
            @if ($showModalBahan)
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $bahan_id ? 'Edit Bahan Baku' : 'Tambah Bahan Baku Baru' }}
                        </h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Nama Bahan</label>
                                <input type="text" wire:model="nama_bahan"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                @error('nama_bahan')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Kategori</label>
                                <input type="text" wire:model="kategori"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
                                    placeholder="Bahan Basah / Biji Kopi / Kemasan">
                                @error('kategori')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-medium text-gray-700 mb-1">Stok Awal</label>
                                    <input type="number" step="0.01" wire:model="stok_sekarang"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                    @error('stok_sekarang')
                                        <span class="text-xs text-rose-500">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium text-gray-700 mb-1">Stok Minimum</label>
                                    <input type="number" step="0.01" wire:model="stok_minimum"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                    @error('stok_minimum')
                                        <span class="text-xs text-rose-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-medium text-gray-700 mb-1">Satuan</label>
                                    <input type="text" wire:model="satuan"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
                                        placeholder="liter, kg, box, pcs, dus">
                                    @error('satuan')
                                        <span class="text-xs text-rose-500">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block font-medium text-gray-700 mb-1">Harga Satuan (Rp)</label>
                                    <input type="number" wire:model="harga_satuan"
                                        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                    @error('harga_satuan')
                                        <span class="text-xs text-rose-500">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button wire:click="$set('showModalBahan', false)"
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">Batal</button>
                            <button wire:click="simpanBahan"
                                class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">Simpan</button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Modal Form Update Stok -->
            @if ($showModalStok)
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
                        <h3 class="text-lg font-bold text-gray-800">Catat Belanja / Pemakaian / Waste</h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Jenis Pencatatan</label>
                                <select wire:model="jenis_penyesuaian"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
                                    <option value="belanja">+ Belanja Bahan (Stok Masuk)</option>
                                    <option value="pemakaian">Pemakaian Produksi (Stok Keluar)</option>
                                    <option value="rusak">Rusak / Kadaluarsa (Waste)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Jumlah</label>
                                <input type="number" step="0.01" wire:model="jumlah_jumlah"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
                                    placeholder="Masukkan jumlah">
                                @error('jumlah_jumlah')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block font-medium text-gray-700 mb-1">Keterangan / Catatan</label>
                                <input type="text" wire:model="keterangan"
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500"
                                    placeholder="Misal: Restok Supplier / Tumpah saat meracik">
                                @error('keterangan')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button wire:click="$set('showModalStok', false)"
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">Batal</button>
                            <button wire:click="updateStok"
                                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">Simpan
                                Perubahan</button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
