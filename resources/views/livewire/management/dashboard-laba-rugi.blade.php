<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header & Control Filter Periode + Tombol Export -->
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
                                class="px-2.5 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white text-xs font-semibold">
                            <span class="text-gray-400 font-bold">s/d</span>
                            <input type="date" wire:model.live="tanggal_selesai"
                                class="px-2.5 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white text-xs font-semibold">
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

            <!-- Card Summary Utama Laba Rugi (Interaktif / Clickable Drill-Down) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <!-- 1. Card Total Omzet Penjualan -->
                <div wire:click="openModal('omzet')"
                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-emerald-400 hover:shadow-md transition cursor-pointer space-y-2 group">
                    <div class="flex justify-between items-center">
                        <span
                            class="text-[11px] font-bold uppercase tracking-wider text-gray-400 group-hover:text-emerald-600">Total
                            Omzet Penjualan</span>
                        <span
                            class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-1.5 py-0.5 rounded border border-emerald-200">Klik
                            Detail 🔍</span>
                    </div>
                    <span class="text-xl font-black text-emerald-600 block">
                        Rp {{ number_format($totalOmzet, 0, ',', '.') }}
                    </span>
                    <div
                        class="flex justify-between items-center text-[11px] pt-1 border-t border-gray-50 text-gray-500">
                        <span>Tunai: <b>Rp {{ number_format($omzetTunai, 0, ',', '.') }}</b></span>
                        <span>QRIS: <b>Rp {{ number_format($omzetQris, 0, ',', '.') }}</b></span>
                    </div>
                </div>

                <!-- 2. Card Belanja Bahan Baku (HPP / Restok Gudang) -->
                <div wire:click="openModal('bahan')"
                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-rose-400 hover:shadow-md transition cursor-pointer space-y-2 group">
                    <div class="flex justify-between items-center">
                        <span
                            class="text-[11px] font-bold uppercase tracking-wider text-gray-400 group-hover:text-rose-600">Belanja
                            Bahan Baku</span>
                        <span
                            class="text-[10px] bg-rose-50 text-rose-700 font-bold px-1.5 py-0.5 rounded border border-rose-200">Klik
                            Detail 🔍</span>
                    </div>
                    <span class="text-xl font-black text-rose-600 block">
                        Rp {{ number_format($totalBiayaBahan, 0, ',', '.') }}
                    </span>
                    <p class="text-[11px] text-gray-400 pt-1 border-t border-gray-50">Sisa / Restok Gudang Pusat</p>
                </div>

                <!-- 3. Card Operasional Lapak -->
                <div wire:click="openModal('operasional')"
                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-amber-400 hover:shadow-md transition cursor-pointer space-y-2 group">
                    <div class="flex justify-between items-center">
                        <span
                            class="text-[11px] font-bold uppercase tracking-wider text-gray-400 group-hover:text-amber-600">Operasional
                            Lapak</span>
                        <span
                            class="text-[10px] bg-amber-50 text-amber-700 font-bold px-1.5 py-0.5 rounded border border-amber-200">Klik
                            Detail 🔍</span>
                    </div>
                    <span class="text-xl font-black text-amber-600 block">
                        Rp {{ number_format($totalBiayaOperasional, 0, ',', '.') }}
                    </span>
                    <div
                        class="flex justify-between items-center text-[11px] pt-1 border-t border-gray-50 text-gray-500">
                        <span>Makan: <b>Rp {{ number_format($totalUangMakan, 0, ',', '.') }}</b></span>
                        <span>Lain: <b>Rp {{ number_format($totalPengeluaranLain, 0, ',', '.') }}</b></span>
                    </div>
                </div>

                <!-- 4. Card Net Profit / Estimasi Laba Bersih -->
                <div wire:click="openModal('laba')"
                    class="bg-amber-600 text-white p-4 rounded-xl shadow-md hover:bg-amber-700 transition cursor-pointer space-y-2 group">
                    <div class="flex justify-between items-center">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-100">Estimasi Laba
                            Bersih</span>
                        <span
                            class="text-[10px] bg-amber-500 text-white font-bold px-1.5 py-0.5 rounded border border-amber-400">Profit
                            Margin: {{ $profitMargin }}%</span>
                    </div>
                    <span class="text-2xl font-black block">
                        Rp {{ number_format($labaBersih, 0, ',', '.') }}
                    </span>
                    <p class="text-[11px] text-amber-100/80 pt-1 border-t border-amber-500">Omzet - Total Pengeluaran
                        (Klik Detail)</p>
                </div>
            </div>

            <!-- Tabel Breakdown Kontribusi Omzet Per Lapak -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-xs uppercase tracking-wider text-gray-800">Kontribusi Penjualan Per
                            Lapak Cabang</h2>
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

            <!-- MODAL DRILL-DOWN DETAIL FINANSIAL -->
            @if ($showModalDetail)
                <div
                    class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-150">

                        <!-- Modal Header -->
                        <div class="p-4 sm:p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <div>
                                <h3 class="font-bold text-sm sm:text-base text-gray-800">{{ $modalTitle }}</h3>
                                <p class="text-[11px] text-gray-500">Data auditabel periode
                                    {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} s/d
                                    {{ \Carbon\Carbon::parse($tanggal_selesai)->format('d M Y') }}</p>
                            </div>
                            <button wire:click="closeModal"
                                class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Modal Body (Content Dinamis berdasarkan $modalType) -->
                        <div class="p-4 sm:p-6 overflow-y-auto space-y-4 text-xs">

                            <!-- 1. DETAIL OMZET PENJUALAN -->
                            @if ($modalType === 'omzet')
                                <div class="grid grid-cols-2 gap-3 mb-4">
                                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                                        <span class="text-emerald-800 font-semibold block text-[11px]">Total Penerimaan
                                            Tunai</span>
                                        <span class="text-base font-black text-emerald-700">Rp
                                            {{ number_format($omzetTunai, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                                        <span class="text-blue-800 font-semibold block text-[11px]">Total Penerimaan
                                            QRIS</span>
                                        <span class="text-base font-black text-blue-700">Rp
                                            {{ number_format($omzetQris, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="border border-gray-100 rounded-xl overflow-hidden">
                                    <table class="w-full text-left">
                                        <thead
                                            class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase font-semibold">
                                            <tr>
                                                <th class="py-2.5 px-3">No. Struk</th>
                                                <th class="py-2.5 px-3">Lapak</th>
                                                <th class="py-2.5 px-3">Kasir</th>
                                                <th class="py-2.5 px-3">Metode</th>
                                                <th class="py-2.5 px-3 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 text-gray-700">
                                            @forelse ($transaksis as $tx)
                                                <tr class="hover:bg-gray-50/50">
                                                    <td class="py-2 px-3 font-bold text-gray-800">
                                                        {{ $tx->nomor_transaksi }}</td>
                                                    <td class="py-2 px-3">{{ $tx->lapak->nama_lapak ?? '-' }}</td>
                                                    <td class="py-2 px-3">{{ $tx->user->name ?? 'Kasir' }}</td>
                                                    <td class="py-2 px-3 uppercase font-semibold text-[10px]">
                                                        <span
                                                            class="px-2 py-0.5 rounded {{ $tx->metode_pembayaran === 'tunai' ? 'bg-emerald-100 text-emerald-800' : ($tx->metode_pembayaran === 'qris' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                                            {{ $tx->metode_pembayaran }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 px-3 text-right font-black text-emerald-600">Rp
                                                        {{ number_format($tx->total_harga, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="py-6 text-center text-gray-400 italic">
                                                        Tidak ada transaksi pada periode ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- 2. DETAIL BELANJA BAHAN BAKU (GUDANG/PRODUKSI) -->
                            @if ($modalType === 'bahan')
                                <div
                                    class="p-3 bg-rose-50 border border-rose-200 rounded-xl mb-3 flex justify-between items-center">
                                    <span class="text-rose-900 font-bold">Total Akumulasi Pembelian Bahan Baku:</span>
                                    <span class="text-base font-black text-rose-700">Rp
                                        {{ number_format($totalBiayaBahan, 0, ',', '.') }}</span>
                                </div>

                                <div class="border border-gray-100 rounded-xl overflow-hidden">
                                    <table class="w-full text-left">
                                        <thead
                                            class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase font-semibold">
                                            <tr>
                                                <th class="py-2.5 px-3">Waktu Restok</th>
                                                <th class="py-2.5 px-3">Nama Bahan Baku</th>
                                                <th class="py-2.5 px-3">Jumlah (Qty)</th>
                                                <th class="py-2.5 px-3 text-right">Total Biaya</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 text-gray-700">
                                            @forelse ($riwayatBahan as $rb)
                                                <tr class="hover:bg-gray-50/50">
                                                    <td class="py-2 px-3 text-gray-500">
                                                        {{ \Carbon\Carbon::parse($rb->created_at)->format('d/m/Y H:i') }}
                                                    </td>
                                                    <td class="py-2 px-3 font-bold text-gray-800">
                                                        {{ $rb->bahanBaku->nama_bahan ?? 'Bahan Baku' }}</td>
                                                    <td class="py-2 px-3 font-semibold">{{ $rb->jumlah }}
                                                        {{ $rb->bahanBaku->satuan ?? 'unit' }}</td>
                                                    <td class="py-2 px-3 text-right font-black text-rose-600">Rp
                                                        {{ number_format($rb->total_biaya, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="py-6 text-center text-gray-400 italic">
                                                        Tidak ada catatan belanja bahan baku pada periode ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- 3. DETAIL OPERASIONAL LAPAK -->
                            @if ($modalType === 'operasional')
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                        <span class="text-amber-800 font-semibold block text-[11px]">Total Uang Makan
                                            Staff</span>
                                        <span class="text-base font-black text-amber-700">Rp
                                            {{ number_format($totalUangMakan, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                                        <span class="text-amber-800 font-semibold block text-[11px]">Total Pengeluaran
                                            Lain-lain</span>
                                        <span class="text-base font-black text-amber-700">Rp
                                            {{ number_format($totalPengeluaranLain, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="border border-gray-100 rounded-xl overflow-hidden">
                                    <table class="w-full text-left">
                                        <thead
                                            class="bg-gray-50 border-b border-gray-100 text-gray-500 uppercase font-semibold">
                                            <tr>
                                                <th class="py-2.5 px-3">Tanggal Shift</th>
                                                <th class="py-2.5 px-3">Lapak Cabang</th>
                                                <th class="py-2.5 px-3">Uang Makan</th>
                                                <th class="py-2.5 px-3">Pengeluaran Lain</th>
                                                <th class="py-2.5 px-3 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 text-gray-700">
                                            @forelse ($laporansApproved as $lap)
                                                <tr class="hover:bg-gray-50/50">
                                                    <td class="py-2 px-3 text-gray-500">
                                                        {{ \Carbon\Carbon::parse($lap->tanggal)->format('d M Y') }}
                                                    </td>
                                                    <td class="py-2 px-3 font-bold text-gray-800">
                                                        {{ $lap->lapak->nama_lapak ?? '-' }}</td>
                                                    <td class="py-2 px-3">Rp
                                                        {{ number_format($lap->uang_makan, 0, ',', '.') }}</td>
                                                    <td class="py-2 px-3">Rp
                                                        {{ number_format($lap->pengeluaran_lain, 0, ',', '.') }}</td>
                                                    <td class="py-2 px-3 text-right font-black text-amber-600">Rp
                                                        {{ number_format($lap->uang_makan + $lap->pengeluaran_lain, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="py-6 text-center text-gray-400 italic">
                                                        Belum ada laporan operasional lapak disetujui (Approved) pada
                                                        periode ini.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- 4. DETAIL LABA BERSIH & PROFIT MARGIN -->
                            @if ($modalType === 'laba')
                                <div class="p-4 bg-gray-50 border border-gray-200 rounded-2xl space-y-3">
                                    <h4
                                        class="font-bold text-gray-800 text-xs uppercase tracking-wider border-b border-gray-200 pb-2">
                                        Matriks Perhitungan Laba Bersih</h4>

                                    <div class="space-y-2 text-xs">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-semibold">(+) Total Omzet Penjualan
                                                Kotor</span>
                                            <span class="font-extrabold text-emerald-600">Rp
                                                {{ number_format($totalOmzet, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-semibold">(-) Total Belanja Bahan Baku
                                                (Gudang)</span>
                                            <span class="font-extrabold text-rose-600">Rp
                                                {{ number_format($totalBiayaBahan, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600 font-semibold">(-) Total Biaya Operasional
                                                Lapak</span>
                                            <span class="font-extrabold text-amber-600">Rp
                                                {{ number_format($totalBiayaOperasional, 0, ',', '.') }}</span>
                                        </div>
                                        <div
                                            class="border-t border-gray-300 pt-2 flex justify-between items-center text-sm">
                                            <span class="font-black text-gray-900">(=) Estimasi Laba Bersih (Net
                                                Profit)</span>
                                            <span class="font-black text-amber-600 text-base">Rp
                                                {{ number_format($labaBersih, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex justify-between items-center">
                                    <div>
                                        <span class="font-bold text-amber-900 block text-xs">Rasio Margin Keuntungan
                                            (Net Profit Margin)</span>
                                        <span class="text-[11px] text-amber-700">Persentase laba bersih terhadap omzet
                                            kotor</span>
                                    </div>
                                    <span class="text-2xl font-black text-amber-800">{{ $profitMargin }}%</span>
                                </div>
                            @endif

                        </div>

                        <!-- Modal Footer -->
                        <div class="p-3 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button wire:click="closeModal"
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white font-bold text-xs rounded-xl transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
