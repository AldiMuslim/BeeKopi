<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header & Control Filter Periode + Lapak -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Analisis Trend Penjualan</h1>
                    <p class="text-xs text-gray-500">Evaluasi produk terlaris, jam sibuk transaksi, dan performa antar
                        lapak.</p>
                </div>

                <div
                    class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-xl border border-gray-100 shadow-sm text-xs">
                    <!-- Dropdown Filter Lapak -->
                    <select wire:model.live="lapak_id"
                        class="px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">
                        <option value="">Semua Lapak (Keseluruhan)</option>
                        @foreach ($lapaks as $lpk)
                            <option value="{{ $lpk->id }}">{{ $lpk->nama_lapak }}</option>
                        @endforeach
                    </select>

                    <!-- Dropdown Filter Periode Waktu -->
                    <select wire:model.live="periode"
                        class="px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">
                        <option value="hari_ini">Hari Ini</option>
                        <option value="7_hari">7 Hari Terakhir</option>
                        <option value="15_hari">15 Hari Terakhir</option>
                        <option value="30_hari">30 Hari Terakhir</option>
                        <option value="bulan_ini">Bulan Ini</option>
                    </select>
                </div>
            </div>

            <!-- Grid Row 1: Top 5 Produk & Jam Sibuk -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">

                <!-- Card 1: Top 5 Menu Terlaris -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Top 5 Menu Terlaris
                            </h2>
                            <p class="text-[11px] text-gray-400">Peringkat varian kopi berdasarkan volume porsi cup.</p>
                        </div>
                        <span
                            class="px-2 py-0.5 bg-amber-50 text-amber-700 font-bold rounded border border-amber-200 text-[10px]">
                            {{ $lapak_id ? $lapaks->find($lapak_id)->nama_lapak ?? 'Lapak' : 'Seluruh Lapak' }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($topProduk as $index => $item)
                            @php
                                $maxCup = $topProduk->first()->total_cup ?? 1;
                                $percentage = round(($item->total_cup / $maxCup) * 100);
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-5 h-5 rounded-full bg-amber-100 text-amber-800 font-black text-[10px] flex items-center justify-center">
                                            {{ $index + 1 }}
                                        </span>
                                        <span
                                            class="font-bold text-gray-800 text-xs">{{ $item->produk->nama_produk ?? 'Produk' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="font-black text-amber-600">{{ number_format($item->total_cup, 0, ',', '.') }}
                                            Cup</span>
                                        <span class="text-gray-400 text-[10px] block">Rp
                                            {{ number_format($item->total_omzet, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-2 rounded-full transition-all duration-500"
                                        style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 italic">Belum ada data penjualan pada periode dan
                                lapak ini.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Card 2: Analisis Jam Sibuk Transaksi -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Jam Sibuk Transaksi
                                (Peak Hours)</h2>
                            <p class="text-[11px] text-gray-400">Sebaran kepadatan pembeli berdasarkan jam transaksi.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                        @forelse ($jamSibuk as $js)
                            <div
                                class="p-2.5 bg-gray-50/80 rounded-lg border border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-1 bg-amber-50 text-amber-700 font-bold text-[11px] rounded border border-amber-200">
                                        {{ sprintf('%02d:00 - %02d:59', $js->jam, $js->jam) }} WIB
                                    </span>
                                    <span class="text-gray-600 font-medium">{{ $js->total_transaksi }} Transaksi</span>
                                </div>
                                <span class="font-extrabold text-emerald-600">
                                    Rp {{ number_format($js->total_omzet, 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 italic">Belum ada aktivitas transaksi terdaftar.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Grid Row 2: Visualisasi Tren Harian & Performa Lapak -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs">

                <!-- Tren Omzet Harian -->
                <div class="lg:col-span-2 bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Grafik Tren Omzet Harian
                        </h2>
                        <p class="text-[11px] text-gray-400">Pertumbuhan nilai penjualan dari hari ke hari.</p>
                    </div>

                    <div class="space-y-2">
                        @forelse ($trenHarian as $th)
                            @php
                                $maxOmzet = $trenHarian->max('omzet') ?: 1;
                                $width = round(($th->omzet / $maxOmzet) * 100);
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-24 text-gray-500 text-[11px] font-medium shrink-0">
                                    {{ \Carbon\Carbon::parse($th->tanggal)->format('d M Y') }}
                                </span>
                                <div
                                    class="flex-1 bg-gray-100 h-4 rounded-lg overflow-hidden relative flex items-center">
                                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 h-4 rounded-lg transition-all duration-500"
                                        style="width: {{ $width }}%"></div>
                                </div>
                                <span class="w-28 text-right font-black text-gray-800 shrink-0">
                                    Rp {{ number_format($th->omzet, 0, ',', '.') }}
                                </span>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 italic">Belum ada data tren harian.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Performa Lapak Cabang -->
                <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4">
                    <div class="border-b border-gray-100 pb-3">
                        <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Performa Armada Lapak</h2>
                        <p class="text-[11px] text-gray-400">Perbandingan omzet antar lokasi cabang lapak.</p>
                    </div>

                    <div class="space-y-3">
                        @forelse ($performaLapak as $lpk)
                            @php
                                $omzetLpk = $lpk->total_omzet ?? 0;
                                $pct = $totalOmzetAll > 0 ? round(($omzetLpk / $totalOmzetAll) * 100, 1) : 0;
                            @endphp
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-800">{{ $lpk->nama_lapak }}</span>
                                    <span
                                        class="px-2 py-0.5 bg-amber-100 text-amber-800 font-extrabold text-[10px] rounded-md">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-[11px] text-gray-500">
                                    <span>{{ $lpk->total_tx }} Transaksi</span>
                                    <span class="font-extrabold text-amber-600">Rp
                                        {{ number_format($omzetLpk, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-400 italic">Belum ada data lapak.</div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
