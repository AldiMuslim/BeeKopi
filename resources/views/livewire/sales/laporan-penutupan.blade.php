<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold text-gray-800">Laporan Penutupan Shift Sales</h1>
                <p class="text-xs text-gray-500">Rekonsiliasi stok fisik, rincian omzet, dan setoran harian.</p>
            </div>

            @if (session()->has('message'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg">
                    {{ session('message') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit.prevent="kirimLaporan" class="space-y-5">

                <!-- Section 1: Ringkasan Omzet Sistem & Detail Cup Terjual -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">1. Omzet Terdaftar
                                Sistem</h2>
                            <p class="text-[11px] text-gray-400">Rekap transaksi otomatis dari sistem kasir hari ini.
                            </p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span
                                class="px-2.5 py-1 bg-amber-100 text-amber-800 font-black rounded-lg text-xs border border-amber-300">
                                Total {{ number_format($total_cup_sistem, 0, ',', '.') }} Cup
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-100 space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-semibold">Omzet Tunai</span>
                                <span
                                    class="px-1.5 py-0.5 bg-emerald-200/60 text-emerald-800 font-bold rounded text-[10px]">
                                    {{ $total_cup_tunai }} Cup
                                </span>
                            </div>
                            <span class="font-extrabold text-emerald-700 text-base block">
                                Rp {{ number_format($omzet_tunai, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="p-3 bg-blue-50/50 rounded-xl border border-blue-100 space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-semibold">Omzet QRIS</span>
                                <span class="px-1.5 py-0.5 bg-blue-200/60 text-blue-800 font-bold rounded text-[10px]">
                                    {{ $total_cup_qris }} Cup
                                </span>
                            </div>
                            <span class="font-extrabold text-blue-700 text-base block">
                                Rp {{ number_format($omzet_qris, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 pt-1">
                        <div class="flex justify-between items-center">
                            <p class="text-xs font-bold text-gray-700">Rincian Varian Menu Terjual:</p>
                            <span class="text-[10px] text-gray-400">{{ count($rincian_produk) }} Jenis Varian</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @forelse ($rincian_produk as $item)
                                <div
                                    class="flex justify-between items-center text-xs py-2 px-3 bg-gray-50/80 rounded-lg border border-gray-100">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        <span
                                            class="px-2 py-0.5 bg-amber-500 text-white font-black text-[11px] rounded-md shrink-0">
                                            {{ $item->total_cup }} cup
                                        </span>
                                        <span
                                            class="font-bold text-gray-800 truncate">{{ $item->produk->nama_produk ?? 'Produk' }}</span>
                                    </div>
                                    <span class="font-extrabold text-gray-700 shrink-0 ml-2">
                                        Rp {{ number_format($item->total_nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @empty
                                <div
                                    class="col-span-full py-4 text-center text-gray-400 text-xs italic bg-gray-50 rounded-lg border border-dashed border-gray-200">
                                    Belum ada transaksi terdaftar untuk shift hari ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Section 2: Opname Fisik Cup Per Varian Menu -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                        <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">2. Opname Fisik Cup Per
                            Menu</h2>
                        <div class="flex gap-2">
                            <span
                                class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-extrabold rounded-lg text-xs border border-emerald-200">
                                Sisa Fisik: {{ $this->sisaStokFisikTotal }} Cup
                            </span>
                            <span
                                class="px-2.5 py-1 bg-rose-50 text-rose-700 font-extrabold rounded-lg text-xs border border-rose-200">
                                Rusak/Tumpah: {{ $this->stokRusakTotal }} Cup
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-xs text-gray-500">Hitung sisa cup fisik siap jual dan cup rusak/tumpah untuk
                            setiap varian menu:</p>

                        @forelse ($stokLapaks as $stok)
                            <div
                                class="p-3 bg-gray-50/70 border border-gray-100 rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div>
                                    <span
                                        class="font-bold text-gray-800 block text-sm">{{ $stok->produk->nama_produk ?? 'Produk' }}</span>
                                    <span class="text-gray-400 text-[11px]">{{ $stok->produk->kategori }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 w-full sm:w-auto">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Sisa Fisik
                                            (Cup)</label>
                                        <input type="number" min="0"
                                            wire:model.live="sisa_fisik_per_produk.{{ $stok->produk_id }}"
                                            class="w-28 px-2.5 py-1.5 border rounded-lg text-xs font-bold text-gray-800 focus:ring-2 focus:ring-amber-500 bg-white">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-semibold text-rose-500 mb-0.5">Rusak/Tumpah</label>
                                        <input type="number" min="0"
                                            wire:model.live="rusak_per_produk.{{ $stok->produk_id }}"
                                            class="w-28 px-2.5 py-1.5 border border-rose-200 rounded-lg text-xs font-bold text-rose-700 focus:ring-2 focus:ring-rose-500 bg-white">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center text-gray-400 text-xs italic">
                                Belum ada menu terdaftar pada stok lapak ini.
                            </div>
                        @endforelse
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700 mb-1 text-xs">Keterangan Cup Rusak / Tumpah
                            (Opsional)</label>
                        <input type="text" wire:model="alasan_stok_rusak"
                            placeholder="Contoh: 1 cup Matcha pecah saat peracikan"
                            class="w-full px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <!-- Section 3: Pengeluaran Operasional Lapak -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600 border-b border-gray-100 pb-2">
                        3. Pengeluaran Operasional Lapak</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div
                            class="p-3 bg-amber-50/60 border border-amber-200/60 rounded-lg flex justify-between items-center">
                            <div>
                                <span class="text-amber-800 font-bold block">Uang Makan Staff</span>
                                <span class="text-[10px] text-amber-600 italic">Ditentukan Management</span>
                            </div>
                            <span class="font-black text-amber-700 text-sm">Rp
                                {{ number_format($uang_makan, 0, ',', '.') }}</span>
                        </div>

                        <div
                            class="p-3 bg-amber-50/60 border border-amber-200/60 rounded-lg flex justify-between items-center">
                            <div>
                                <span class="text-amber-800 font-bold block">Jatah Minum Staff</span>
                                <span class="text-[10px] text-amber-600 italic">Ditentukan Management</span>
                            </div>
                            <span class="font-black text-amber-700 text-sm">{{ $jatah_minum_cup }} Cup</span>
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <label class="block font-bold text-gray-800 text-xs">Pengeluaran Lainnya (Beli Es Batu, Parkir,
                            dll):</label>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" wire:model="nama_item_input"
                                placeholder="Nama pengeluaran (misal: Beli Es Batu)"
                                class="flex-1 px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                            <input type="number" wire:model="nominal_item_input" placeholder="Nominal (Rp)"
                                class="w-full sm:w-36 px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                            <button type="button" wire:click="tambahPengeluaran"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition shadow-sm whitespace-nowrap">
                                + Tambah
                            </button>
                        </div>
                        @error('nama_item_input')
                            <span class="text-rose-500 text-[11px] block">{{ $message }}</span>
                        @enderror
                        @error('nominal_item_input')
                            <span class="text-rose-500 text-[11px] block">{{ $message }}</span>
                        @enderror

                        <div class="border border-gray-100 rounded-lg overflow-hidden mt-3">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr
                                        class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase">
                                        <th class="py-2 px-3">Detail Pengeluaran</th>
                                        <th class="py-2 px-3">Nominal</th>
                                        <th class="py-2 px-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-gray-700">
                                    @forelse ($item_pengeluaran as $index => $item)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="py-2 px-3 font-semibold text-gray-800">{{ $item['nama'] }}</td>
                                            <td class="py-2 px-3 font-bold text-amber-600">Rp
                                                {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                                            <td class="py-2 px-3 text-center">
                                                <button type="button"
                                                    wire:click="hapusPengeluaran({{ $index }})"
                                                    class="text-rose-600 hover:text-rose-800 font-bold text-[11px]">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-4 text-center text-gray-400 italic">Belum ada
                                                pengeluaran lain yang ditambahkan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if (count($item_pengeluaran) > 0)
                                    <tfoot>
                                        <tr class="bg-amber-50/50 font-bold text-gray-800 border-t border-amber-100">
                                            <td class="py-2.5 px-3">Total Pengeluaran Lain:</td>
                                            <td colspan="2" class="py-2.5 px-3 text-amber-700 font-black">Rp
                                                {{ number_format($this->pengeluaranLainTotal, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Upload Bukti QRIS & Ringkasan Setoran -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-3">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">4. Upload Rekap QRIS &
                        Setoran</h2>
                    <div class="text-xs">
                        <label class="block font-medium text-gray-700 mb-1">Upload Tangkapan Layar Rekap QRIS</label>
                        <input type="file" wire:model="foto_qris"
                            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                    </div>

                    <div
                        class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex justify-between items-center text-xs mt-3">
                        <span class="font-bold text-amber-900">Total Uang Setoran Tunai Bersih:</span>
                        <span class="text-lg font-extrabold text-amber-700">Rp
                            {{ number_format($this->setoranBersih, 0, ',', '.') }}</span>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl shadow-md transition text-xs">
                    Kirim Laporan Penutupan Shift
                </button>
            </form>

        </div>
    </div>
</div>
