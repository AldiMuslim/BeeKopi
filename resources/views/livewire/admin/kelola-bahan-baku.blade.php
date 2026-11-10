<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header & Action Buttons -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Master Bahan Baku Pusat</h1>
                    <p class="text-xs text-gray-500">Kelola persediaan bahan mentah, standar harga satuan HPP, batas stok
                        minimum, dan keterkaitan resep menu.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button wire:click="exportDaftarBelanjaCsv"
                        class="inline-flex items-center justify-center px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition gap-1.5">
                        <span>🛒 Ekspor Daftar Belanja (CSV)</span>
                    </button>
                    <button wire:click="openModalBahan"
                        class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                        + Tambah Bahan Baku
                    </button>
                </div>
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

            <!-- Section 1: Top Summary Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div wire:click="$set('filterStatus', 'semua')"
                    class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1 cursor-pointer hover:border-amber-300 transition">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Total Jenis Bahan</p>
                    <p class="text-xl font-black text-gray-800">{{ $totalJenisBahan }} <span
                            class="text-xs font-normal text-gray-500">item</span></p>
                </div>

                <div wire:click="$set('filterStatus', 'menipis')"
                    class="bg-white p-4 rounded-xl border {{ $filterStatus === 'menipis' ? 'border-amber-400 ring-2 ring-amber-100' : 'border-gray-100' }} shadow-sm space-y-1 cursor-pointer hover:border-amber-300 transition">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Stok Menipis (Restock)</p>
                    <p class="text-xl font-black {{ $stokMenipisCount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                        {{ $stokMenipisCount }} <span class="text-xs font-normal text-gray-500">bahan</span>
                    </p>
                </div>

                <div wire:click="$set('filterStatus', 'habis')"
                    class="bg-white p-4 rounded-xl border {{ $filterStatus === 'habis' ? 'border-rose-400 ring-2 ring-rose-100' : 'border-gray-100' }} shadow-sm space-y-1 cursor-pointer hover:border-rose-300 transition">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Stok Habis (Kosong)</p>
                    <p class="text-xl font-black {{ $stokHabisCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $stokHabisCount }} <span class="text-xs font-normal text-gray-500">bahan</span>
                    </p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Estimasi Valuasi Bahan</p>
                    <p class="text-xl font-black text-emerald-600">Rp {{ number_format($estimasiValuasi, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Section 2: Filter & Search Bar -->
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="w-full sm:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama bahan baku..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto text-xs">
                    <div class="flex items-center bg-gray-100 p-1 rounded-lg">
                        <button wire:click="$set('filterStatus', 'semua')"
                            class="px-2.5 py-1 rounded-md font-bold transition {{ $filterStatus === 'semua' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            Semua
                        </button>
                        <button wire:click="$set('filterStatus', 'menipis')"
                            class="px-2.5 py-1 rounded-md font-bold transition {{ $filterStatus === 'menipis' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            Menipis / Kritis
                        </button>
                        <button wire:click="$set('filterStatus', 'habis')"
                            class="px-2.5 py-1 rounded-md font-bold transition {{ $filterStatus === 'habis' ? 'bg-rose-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            Habis (0)
                        </button>
                        <button wire:click="$set('filterStatus', 'aman')"
                            class="px-2.5 py-1 rounded-md font-bold transition {{ $filterStatus === 'aman' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            Aman
                        </button>
                    </div>

                    <select wire:model.live="filterSatuan"
                        class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">
                        <option value="">-- Semua Satuan --</option>
                        <option value="gram">gram</option>
                        <option value="kg">kg</option>
                        <option value="ml">ml</option>
                        <option value="liter">liter</option>
                        <option value="pcs">pcs</option>
                        <option value="set">set</option>
                    </select>
                </div>
            </div>

            <!-- Section 3: Tabel Master Bahan Baku -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-[11px] font-semibold uppercase text-gray-500 whitespace-nowrap">
                                <th class="py-3 px-4">Nama Bahan Baku</th>
                                <th class="py-3 px-4">Stok Saat Ini</th>
                                <th class="py-3 px-4">Batas Minimum</th>
                                <th class="py-3 px-4">Harga / Satuan & Valuasi</th>
                                <th class="py-3 px-4">Keterkaitan Resep & Kapasitas</th>
                                <th class="py-3 px-4">Status Batas</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($bahanBakus as $b)
                                @php
                                    $stokSekarang = $this->getStok($b);
                                    $stokMin = (float) $b->stok_minimum;
                                    $hargaSatuan = $this->getHargaSatuan($b);
                                    $nilaiAset = round($stokSekarang * $hargaSatuan);
                                    $decStok = $stokSekarang == floor($stokSekarang) ? 0 : 2;
                                    $decMin = $stokMin == floor($stokMin) ? 0 : 2;
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <!-- Nama Bahan -->
                                    <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $b->nama_bahan }}
                                    </td>

                                    <!-- Stok Saat Ini -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span
                                            class="font-black text-sm {{ $stokSekarang <= 0 ? 'text-rose-600' : ($stokSekarang <= $stokMin ? 'text-amber-600' : 'text-gray-900') }}">
                                            {{ number_format($stokSekarang, $decStok, ',', '.') }}
                                        </span>
                                        <span
                                            class="text-[10px] font-bold text-gray-500 uppercase ml-0.5">{{ $b->satuan }}</span>
                                    </td>

                                    <!-- Stok Minimum -->
                                    <td class="py-3.5 px-4 whitespace-nowrap text-gray-600 font-semibold">
                                        {{ number_format($stokMin, $decMin, ',', '.') }} <span
                                            class="text-[10px] uppercase text-gray-400">{{ $b->satuan }}</span>
                                    </td>

                                    <!-- Harga Satuan & Valuasi -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($hargaSatuan > 0)
                                            <span class="font-bold text-gray-800 block">
                                                Rp
                                                {{ number_format($hargaSatuan, $hargaSatuan == floor($hargaSatuan) ? 0 : 2, ',', '.') }}
                                                <span class="text-[10px] font-normal text-gray-400">/
                                                    {{ $b->satuan }}</span>
                                            </span>
                                            <span class="text-[10px] font-semibold text-emerald-600 block">
                                                Aset: Rp {{ number_format($nilaiAset, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-[11px] text-rose-500 italic font-semibold">Harga belum
                                                diatur</span>
                                        @endif
                                    </td>

                                    <!-- Keterkaitan Resep & Estimasi Kapasitas Cup -->
                                    <td class="py-3.5 px-4 max-w-xs">
                                        @if ($b->reseps->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($b->reseps->take(3) as $rsp)
                                                    @php
                                                        $maxCup = $this->estimasiKapasitasCup($b, $rsp);
                                                    @endphp
                                                    <span
                                                        class="px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded text-[10px] font-semibold">
                                                        {{ $rsp->produk->nama_produk ?? 'Menu' }} ( cukup
                                                        ~{{ $maxCup }} cup)
                                                    </span>
                                                @endforeach
                                                @if ($b->reseps->count() > 3)
                                                    <span
                                                        class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] font-bold">
                                                        +{{ $b->reseps->count() - 3 }} menu lainnya
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">Belum ditautkan ke
                                                resep</span>
                                        @endif
                                    </td>

                                    <!-- Status Batas -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($stokSekarang <= 0)
                                            <span
                                                class="px-2.5 py-1 bg-rose-100 text-rose-800 font-extrabold rounded-full text-[10px]">
                                                Stok Habis
                                            </span>
                                        @elseif ($stokSekarang <= $stokMin)
                                            <span
                                                class="px-2.5 py-1 bg-amber-100 text-amber-800 font-extrabold rounded-full text-[10px]">
                                                Menipis / Restok
                                            </span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-extrabold rounded-full text-[10px]">
                                                Aman
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button wire:click="editBahan({{ $b->id }})"
                                                class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-[11px] transition">
                                                Edit / Koreksi
                                            </button>
                                            <button wire:click="hapusBahan({{ $b->id }})"
                                                wire:confirm="Hapus bahan baku '{{ $b->nama_bahan }}' dari daftar master?"
                                                class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold rounded-lg text-[11px] transition">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-gray-400 italic text-xs">
                                        Belum ada data bahan baku yang sesuai dengan pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL TAMBAH / EDIT BAHAN BAKU -->
            @if ($showModalBahan)
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4 max-h-[92vh] overflow-y-auto animate-in fade-in zoom-in duration-150 text-xs">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <div>
                                <h3 class="text-base font-bold text-gray-800">
                                    {{ $bahan_baku_id ? 'Edit & Koreksi Bahan Baku' : 'Tambah Master Bahan Baku Baru' }}
                                </h3>
                                <p class="text-[11px] text-gray-400">Pastikan satuan dan harga per satuan sesuai untuk
                                    akurasi HPP resep.</p>
                            </div>
                            <button wire:click="$set('showModalBahan', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <form wire:submit.prevent="simpanBahan" class="space-y-3.5">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Nama Bahan Baku:</label>
                                <input type="text" wire:model="nama_bahan"
                                    placeholder="Contoh: Biji Kopi Arabika / Susu UHT Full Cream"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                @error('nama_bahan')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Satuan Gudang:</label>
                                    <select wire:model.live="satuan"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold text-gray-800">
                                        <option value="gram">Gram (gr)</option>
                                        <option value="kg">Kilogram (kg)</option>
                                        <option value="ml">Mililiter (ml)</option>
                                        <option value="liter">Liter (L)</option>
                                        <option value="pcs">Pieces (pcs)</option>
                                        <option value="set">Set (Cup+Tutup)</option>
                                    </select>
                                    @error('satuan')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Stok Saat Ini:</label>
                                    <input type="number" step="any" wire:model="stok" min="0"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold text-amber-700">
                                    @error('stok')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Batas Minimum:</label>
                                    <input type="number" step="any" wire:model="stok_minimum" min="0"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                    @error('stok_minimum')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Input Harga Satuan & Kalkulator Bantu Kemasan -->
                            <div class="p-3.5 bg-amber-50/60 border border-amber-200 rounded-xl space-y-2.5">
                                <div class="flex justify-between items-center">
                                    <label class="font-bold text-gray-800">
                                        Harga Beli per 1 <span
                                            class="uppercase text-amber-700">{{ $satuan }}</span> (Rp):
                                    </label>
                                    <button type="button" wire:click="$toggle('showKalkulatorKemasan')"
                                        class="text-[11px] text-amber-700 hover:text-amber-900 font-bold underline">
                                        {{ $showKalkulatorKemasan ? 'Tutup Kalkulator Kemasan' : '🧮 Hitung dari Harga Kemasan' }}
                                    </button>
                                </div>

                                <input type="number" step="any" wire:model="harga_satuan" min="0"
                                    placeholder="Contoh: 150"
                                    class="w-full px-3 py-2 border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-black text-emerald-700">
                                @error('harga_satuan')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror

                                @if ($showKalkulatorKemasan)
                                    <div
                                        class="pt-2 border-t border-amber-200/80 grid grid-cols-2 gap-2.5 animate-in fade-in duration-150">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-600 mb-1">Harga Beli 1
                                                Kemasan (Rp):</label>
                                            <input type="number" step="any" wire:model.live="harga_beli_kemasan"
                                                placeholder="Misal: 150000"
                                                class="w-full px-2.5 py-1.5 border rounded-lg bg-white text-xs font-semibold">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-600 mb-1">Isi Kemasan
                                                (dalam {{ $satuan }}):</label>
                                            <input type="number" step="any" wire:model.live="isi_per_kemasan"
                                                placeholder="Misal: 1000"
                                                class="w-full px-2.5 py-1.5 border rounded-lg bg-white text-xs font-semibold">
                                        </div>
                                        <div class="col-span-2 text-[10px] text-amber-800 font-medium">
                                            💡 Otomatis membagi Harga Kemasan dengan Isi Kemasan untuk mengisi harga per
                                            {{ $satuan }}.
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Input Alasan Koreksi Stok (Hanya tampil saat Edit) -->
                            @if ($bahan_baku_id)
                                <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl space-y-1.5">
                                    <label class="block font-bold text-gray-700">Catatan / Alasan Koreksi Stok
                                        (Opsional):</label>
                                    <input type="text" wire:model="alasan_koreksi"
                                        placeholder="Contoh: Penyesuaian Stok Opname Mingguan / Bahan Tumpah"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white">
                                    <p class="text-[10px] text-gray-400">
                                        Jika jumlah stok diubah, alasan ini akan dicatat langsung ke dalam Audit Log.
                                    </p>
                                </div>
                            @endif

                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button type="button" wire:click="$set('showModalBahan', false)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">
                                    Batal
                                </button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-amber-600 text-white rounded-xl font-bold hover:bg-amber-700 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="simpanBahan">Simpan Master Bahan</span>
                                    <span wire:loading wire:target="simpanBahan">Menyimpan...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
