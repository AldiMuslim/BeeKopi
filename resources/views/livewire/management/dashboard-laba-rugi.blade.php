<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header & Filter Periode + Tombol Export -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Laporan Laba & Rugi (Bee Kopi)</h1>
                    <p class="text-xs text-gray-500">Ringkasan pendapatan kotor, belanja bahan baku, operasional lapak,
                        dan laba bersih.</p>
                </div>

                <!-- Control Filter Tanggal & Tombol Export -->
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-xl border border-gray-100 shadow-sm text-xs">
                        <select wire:model.live="periode"
                            class="px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">
                            <option value="hari_ini">Hari Ini</option>
                            <option value="bulan_ini">Bulan Ini</option>
                            <option value="tahun_ini">Tahun Ini</option>
                            <option value="custom">Rentang Custom</option>
                        </select>

                        @if ($periode === 'custom')
                            <input type="date" wire:model.live="tanggal_mulai"
                                class="px-2.5 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
                            <span class="text-gray-400">s/d</span>
                            <input type="date" wire:model.live="tanggal_selesai"
                                class="px-2.5 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
                        @endif
                    </div>

                    <!-- Tombol Export Excel -->
                    <button wire:click="exportExcel" wire:loading.attr="disabled"
                        class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition text-xs flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                        <span wire:loading wire:target="exportExcel">Mengunduh...</span>
                    </button>

                    <!-- Tombol Export PDF -->
                    <button wire:click="exportPdf" wire:loading.attr="disabled"
                        class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition text-xs flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span wire:loading.remove wire:target="exportPdf">Export PDF</span>
                        <span wire:loading wire:target="exportPdf">Mengunduh...</span>
                    </button>
                </div>
            </div>

            <!-- Card Summary Utama Laba Rugi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Omzet Kotor -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">Total Omzet
                        Penjualan</span>
                    <span class="text-xl font-black text-emerald-600 block">Rp
                        {{ number_format($totalOmzet, 0, ',', '.') }}</span>
                    <div
                        class="flex justify-between items-center text-[11px] pt-1 border-t border-gray-50 text-gray-500">
                        <span>Tunai: <b>Rp {{ number_format($omzetTunai, 0, ',', '.') }}</b></span>
                        <span>QRIS: <b>Rp {{ number_format($omzetQris, 0, ',', '.') }}</b></span>
                    </div>
                </div>

                <!-- Belanja Bahan Baku (HPP) -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">Belanja Bahan
                        Baku</span>
                    <span class="text-xl font-black text-rose-600 block">Rp
                        {{ number_format($totalBiayaBahan, 0, ',', '.') }}</span>
                    <p class="text-[11px] text-gray-400 pt-1 border-t border-gray-50">Sisa / Restok Gudang Pusat</p>
                </div>

                <!-- Operasional Lapak -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 block">Operasional
                        Lapak</span>
                    <span class="text-xl font-black text-amber-600 block">Rp
                        {{ number_format($totalBiayaOperasional, 0, ',', '.') }}</span>
                    <div
                        class="flex justify-between items-center text-[11px] pt-1 border-t border-gray-50 text-gray-500">
                        <span>Makan: <b>Rp {{ number_format($totalUangMakan, 0, ',', '.') }}</b></span>
                        <span>Lain: <b>Rp {{ number_format($totalPengeluaranLain, 0, ',', '.') }}</b></span>
                    </div>
                </div>

                <!-- Net Profit / Laba Bersih -->
                <div class="bg-amber-600 text-white p-4 rounded-xl shadow-md space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-100 block">Estimasi Laba
                        Bersih</span>
                    <span class="text-2xl font-black block">Rp {{ number_format($labaBersih, 0, ',', '.') }}</span>
                    <p class="text-[11px] text-amber-100/80 pt-1 border-t border-amber-500">Omzet - Total Pengeluaran
                    </p>
                </div>
            </div>

            <!-- Tabel Breakdown Kontribusi Omzet Per Lapak -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-xs uppercase tracking-wider text-gray-800">Kontribusi Penjualan Per
                            Lapak</h2>
                        <p class="text-[11px] text-gray-400">Peringkat kontribusi pendapatan dari setiap armada cabang
                            lapak.</p>
                    </div>
                </div>

                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Nama Lapak Cabang</th>
                                <th class="py-3 px-4">Total Transaksi</th>
                                <th class="py-3 px-4">Kontribusi Omzet</th>
                                <th class="py-3 px-4">Persentase (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($performaLapaks as $lapak)
                                @php
                                    $omzetLapak = $lapak->total_omzet ?? 0;
                                    $persentase = $totalOmzet > 0 ? round(($omzetLapak / $totalOmzet) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 font-bold text-gray-800 whitespace-nowrap">
                                        {{ $lapak->nama_lapak }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-gray-600 whitespace-nowrap">
                                        {{ number_format($lapak->total_transaksi, 0, ',', '.') }} Transaksi
                                    </td>
                                    <td class="py-3 px-4 font-extrabold text-amber-600 whitespace-nowrap">
                                        Rp {{ number_format($omzetLapak, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-24 bg-gray-100 rounded-full h-2 overflow-hidden">
                                                <div class="bg-amber-500 h-2 rounded-full"
                                                    style="width: {{ $persentase }}%"></div>
                                            </div>
                                            <span class="font-bold text-gray-700">{{ $persentase }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-400">Belum ada data penjualan
                                        lapak.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
