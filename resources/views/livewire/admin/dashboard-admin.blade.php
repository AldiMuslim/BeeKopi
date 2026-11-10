<div>
    <!-- Load Chart.js CDN di dalam Single Root Wrapper Livewire -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header Dashboard & Filter Controls -->
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                <div>
                    <div class="flex items-center gap-2">
                        <span
                            class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-extrabold rounded-full text-[10px] uppercase tracking-wider">
                            Control Center
                        </span>
                        <span class="text-xs text-gray-400 font-medium">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mt-1">Dashboard Eksekutif Bee Kopi</h1>
                    <p class="text-xs text-gray-500">Ringkasan performa finansial, kesehatan inventori pusat, dan
                        aktivitas armada lapak.</p>
                </div>

                <!-- Dropdown Filter dengan pl-3.5 pr-9 agar panah tidak menabrak teks -->
                <div class="flex flex-wrap items-center gap-2.5 text-xs shrink-0">
                    <select wire:model.live="filterLapak"
                        class="pl-3.5 pr-9 py-2 border border-gray-200 rounded-xl bg-gray-50 hover:bg-white focus:ring-2 focus:ring-amber-500 font-semibold text-gray-700 transition cursor-pointer">
                        <option value="">📍 Semua Armada Lapak</option>
                        @foreach ($allLapaks as $lpk)
                            <option value="{{ $lpk->id }}">{{ $lpk->nama_lapak }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterRange"
                        class="pl-3.5 pr-9 py-2 border border-gray-200 rounded-xl bg-gray-50 hover:bg-white focus:ring-2 focus:ring-amber-500 font-bold text-amber-700 transition cursor-pointer">
                        <option value="1">Hari Ini</option>
                        <option value="7">7 Hari Terakhir</option>
                        <option value="30">30 Hari Terakhir</option>
                        <option value="90">3 Bulan Terakhir</option>
                        <option value="365">1 Tahun Terakhir</option>
                    </select>

                    @if ($filterRange != 30 || $filterLapak !== '')
                        <button wire:click="resetFilter"
                            class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl transition">
                            Reset
                        </button>
                    @endif
                </div>
            </div>

            <!-- Section 1: 4 Kartu KPI Keuangan Utama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- KPI 1: Total Omzet -->
                <div
                    class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Total Omzet
                            Penjualan</span>
                        <h2 class="text-xl sm:text-2xl font-black text-emerald-600 truncate">
                            Rp {{ number_format($totalOmzet, 0, ',', '.') }}
                        </h2>
                        <p class="text-[11px] text-gray-500 font-medium">
                            Dari <strong
                                class="text-gray-800">{{ number_format($totalTransaksiCount, 0, ',', '.') }}</strong>
                            transaksi selesai
                        </p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- KPI 2: Total Pengeluaran Operasional -->
                <div
                    class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Pengeluaran
                            Operasional</span>
                        <h2 class="text-xl sm:text-2xl font-black text-rose-600 truncate">
                            Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
                        </h2>
                        <p class="text-[11px] text-gray-500 font-medium">
                            Rekap laporan harian disetujui
                        </p>
                    </div>
                    <div class="p-3 bg-rose-50 rounded-xl text-rose-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- KPI 3: Estimasi Laba Bersih (Net Cashflow) -->
                <div
                    class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Estimasi Laba
                            Bersih</span>
                        <h2
                            class="text-xl sm:text-2xl font-black {{ $estimasiLabaBersih >= 0 ? 'text-amber-600' : 'text-rose-600' }} truncate">
                            Rp {{ number_format($estimasiLabaBersih, 0, ',', '.') }}
                        </h2>
                        <p
                            class="text-[11px] font-semibold {{ $persentaseMargin >= 30 ? 'text-emerald-600' : 'text-amber-600' }}">
                            Rasio Margin Kas: {{ $persentaseMargin }}%
                        </p>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-xl text-amber-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6">
                            </path>
                        </svg>
                    </div>
                </div>

                <!-- KPI 4: Total Cup Terjual -->
                <div
                    class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block">Volume Produk
                            Terjual</span>
                        <h2 class="text-xl sm:text-2xl font-black text-blue-600 truncate">
                            {{ number_format($totalCupTerjual, 0, ',', '.') }} <span
                                class="text-xs font-bold text-gray-500">Cup</span>
                        </h2>
                        <p class="text-[11px] text-gray-500 font-medium">
                            Rata-rata
                            ~{{ number_format(ceil($totalCupTerjual / max((int) $filterRange, 1)), 0, ',', '.') }} cup
                            / hari
                        </p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl text-blue-600 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Section 2: Bar Indikator Kesehatan Sistem & Peringatan Stok (Dirapikan jarak & proporsinya) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <!-- Status 1 -->
                <div
                    class="bg-white px-4 py-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between gap-3">
                    <div class="space-y-0.5 min-w-0">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wide block truncate">Status
                            Armada Lapak</span>
                        <span class="font-extrabold text-gray-800 text-xs sm:text-[13px] block truncate">
                            {{ $lapakAktifCount }} / {{ $totalLapakCount }} Cabang Aktif
                        </span>
                    </div>
                    <span
                        class="px-2.5 py-1 rounded-lg font-bold text-[10px] shrink-0 {{ $lapakAktifCount > 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        Operasional
                    </span>
                </div>

                <!-- Status 2 -->
                <div
                    class="bg-white px-4 py-3.5 rounded-xl border {{ $bahanKritisCount > 0 ? 'border-rose-200 bg-rose-50/30' : 'border-gray-100' }} shadow-sm flex items-center justify-between gap-3">
                    <div class="space-y-0.5 min-w-0">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wide block truncate">Bahan
                            Baku Kritis</span>
                        <span
                            class="font-extrabold {{ $bahanKritisCount > 0 ? 'text-rose-600' : 'text-emerald-600' }} text-xs sm:text-[13px] block truncate">
                            {{ $bahanKritisCount }} Bahan Menipis
                        </span>
                    </div>
                    <span
                        class="px-2.5 py-1 rounded-lg font-bold text-[10px] shrink-0 {{ $bahanKritisCount > 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $bahanKritisCount > 0 ? 'Perlu Restock' : 'Stok Aman' }}
                    </span>
                </div>

                <!-- Status 3 -->
                <div
                    class="bg-white px-4 py-3.5 rounded-xl border {{ $produkMenipisCount > 0 ? 'border-amber-200 bg-amber-50/30' : 'border-gray-100' }} shadow-sm flex items-center justify-between gap-3">
                    <div class="space-y-0.5 min-w-0">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wide block truncate">Stok
                            Cup Gudang Pusat</span>
                        <span
                            class="font-extrabold {{ $produkMenipisCount > 0 ? 'text-amber-700' : 'text-emerald-600' }} text-xs sm:text-[13px] block truncate">
                            {{ $produkMenipisCount }} Varian &le; 10 Cup
                        </span>
                    </div>
                    <span
                        class="px-2.5 py-1 rounded-lg font-bold text-[10px] shrink-0 {{ $produkMenipisCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $produkMenipisCount > 0 ? 'Siap Racik' : 'Tersedia' }}
                    </span>
                </div>

                <!-- Status 4 -->
                <div
                    class="bg-white px-4 py-3.5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between gap-3">
                    <div class="space-y-0.5 min-w-0">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wide block truncate">Laporan
                            Harian Lapak</span>
                        <span class="font-extrabold text-gray-800 text-xs sm:text-[13px] block truncate">
                            {{ $laporanPendingCount }} Menunggu Verifikasi
                        </span>
                    </div>
                    <span
                        class="px-2.5 py-1 rounded-lg font-bold text-[10px] shrink-0 {{ $laporanPendingCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $laporanPendingCount > 0 ? 'Pending' : 'Selesai' }}
                    </span>
                </div>
            </div>

            <!-- Section 3: Grid Grafik Reaktif (2x2) dengan wire:key Dinamis -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6"
                wire:key="dashboard-charts-{{ $filterRange }}-{{ $filterLapak }}">

                <!-- Grafik 1: Omzet Penjualan Harian -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm">Tren Omzet Penjualan Harian</h3>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">Line
                            Chart</span>
                    </div>
                    <div class="h-64 relative" data-labels="{{ json_encode($chartOmzetLabels) }}"
                        data-values="{{ json_encode($chartOmzetValues) }}" x-data="{
                            chart: null,
                            init() {
                                let labels = JSON.parse(this.$el.dataset.labels || '[]');
                                let values = JSON.parse(this.$el.dataset.values || '[]');
                                if (this.chart) this.chart.destroy();
                                this.chart = new Chart(this.$refs.canvas, {
                                    type: 'line',
                                    data: {
                                        labels: labels.length ? labels : ['Belum Ada Data'],
                                        datasets: [{
                                            label: 'Omzet (Rp)',
                                            data: values.length ? values : [0],
                                            borderColor: '#059669',
                                            backgroundColor: 'rgba(5, 150, 105, 0.12)',
                                            fill: true,
                                            tension: 0.35,
                                            pointRadius: 3
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } }
                                    }
                                });
                            }
                        }">
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>

                <!-- Grafik 2: Penjualan Per Armada Lapak -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm">Perbandingan Omzet per Armada Lapak</h3>
                        <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Bar
                            Chart</span>
                    </div>
                    <div class="h-64 relative" data-labels="{{ json_encode($chartLapakLabels) }}"
                        data-values="{{ json_encode($chartLapakValues) }}" x-data="{
                            chart: null,
                            init() {
                                let labels = JSON.parse(this.$el.dataset.labels || '[]');
                                let values = JSON.parse(this.$el.dataset.values || '[]');
                                if (this.chart) this.chart.destroy();
                                this.chart = new Chart(this.$refs.canvas, {
                                    type: 'bar',
                                    data: {
                                        labels: labels.length ? labels : ['Belum Ada Lapak'],
                                        datasets: [{
                                            label: 'Total Penjualan (Rp)',
                                            data: values.length ? values : [0],
                                            backgroundColor: '#d97706',
                                            borderRadius: 6
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } }
                                    }
                                });
                            }
                        }">
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>

                <!-- Grafik 3: Top 10 Produk Terlaris (Cup) -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm">Top 10 Menu Terlaris (Cup)</h3>
                        <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded">Horizontal
                            Bar</span>
                    </div>
                    <div class="h-64 relative" data-labels="{{ json_encode($chartProdukLabels) }}"
                        data-values="{{ json_encode($chartProdukValues) }}" x-data="{
                            chart: null,
                            init() {
                                let labels = JSON.parse(this.$el.dataset.labels || '[]');
                                let values = JSON.parse(this.$el.dataset.values || '[]');
                                if (this.chart) this.chart.destroy();
                                this.chart = new Chart(this.$refs.canvas, {
                                    type: 'bar',
                                    data: {
                                        labels: labels.length ? labels : ['Belum Ada Transaksi'],
                                        datasets: [{
                                            label: 'Terjual (Cup)',
                                            data: values.length ? values : [0],
                                            backgroundColor: '#2563eb',
                                            borderRadius: 6
                                        }]
                                    },
                                    options: {
                                        indexAxis: 'y',
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } }
                                    }
                                });
                            }
                        }">
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>

                <!-- Grafik 4: Tren Pengeluaran Operasional -->
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-3">
                    <div class="flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm">Tren Pengeluaran Operasional Harian</h3>
                        <span class="text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded">Expense
                            Trend</span>
                    </div>
                    <div class="h-64 relative" data-labels="{{ json_encode($chartPengeluaranLabels) }}"
                        data-values="{{ json_encode($chartPengeluaranValues) }}" x-data="{
                            chart: null,
                            init() {
                                let labels = JSON.parse(this.$el.dataset.labels || '[]');
                                let values = JSON.parse(this.$el.dataset.values || '[]');
                                if (this.chart) this.chart.destroy();
                                this.chart = new Chart(this.$refs.canvas, {
                                    type: 'line',
                                    data: {
                                        labels: labels.length ? labels : ['Belum Ada Pengeluaran'],
                                        datasets: [{
                                            label: 'Pengeluaran (Rp)',
                                            data: values.length ? values : [0],
                                            borderColor: '#e11d48',
                                            backgroundColor: 'rgba(225, 29, 72, 0.12)',
                                            fill: true,
                                            tension: 0.35,
                                            pointRadius: 3
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: { legend: { display: false } }
                                    }
                                });
                            }
                        }">
                        <canvas x-ref="canvas"></canvas>
                    </div>
                </div>

            </div>

            <!-- Section 4: Peringkat Performa Lapak & Jejak Audit Log Terbaru -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 text-xs">

                <!-- Kolom Kiri (5 Kolom): Peringkat Performa Armada Lapak -->
                <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">Peringkat Performa Armada Lapak</h3>
                            <p class="text-[11px] text-gray-400">Berdasarkan kontribusi omzet ({{ $filterRange }}
                                hari terakhir)</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @forelse ($penjualanPerLapak as $idx => $lpk)
                            @php
                                $omzetLapak = (float) ($lpk->transaksis_sum_total_harga ?? 0);
                                $persenKontribusi = $totalOmzet > 0 ? round(($omzetLapak / $totalOmzet) * 100, 1) : 0;
                            @endphp
                            <div class="p-3 bg-gray-50 rounded-xl border border-gray-100 space-y-1.5">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-5 h-5 rounded-full {{ $idx === 0 ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-700' }} font-black text-[10px] flex items-center justify-center">
                                            {{ $idx + 1 }}
                                        </span>
                                        <div>
                                            <span class="font-bold text-gray-900 block">{{ $lpk->nama_lapak }}</span>
                                            <span
                                                class="text-[10px] text-gray-400">{{ $lpk->total_transaksi_selesai ?? 0 }}
                                                transaksi • {{ $lpk->users_count ?? 0 }} staf</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-black text-emerald-600 block">Rp
                                            {{ number_format($omzetLapak, 0, ',', '.') }}</span>
                                        <span class="text-[10px] font-bold text-gray-500">{{ $persenKontribusi }}%
                                            omzet</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-200 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-1.5 rounded-full"
                                        style="width: {{ min($persenKontribusi, 100) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-400 italic py-6">Belum ada armada lapak terdaftar.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Kolom Kanan (7 Kolom): Aktivitas Sistem & Audit Log Terbaru -->
                <div class="lg:col-span-7 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm space-y-4">
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm">Jejak Aktivitas Sistem Terbaru (Live Audit
                                Feed)</h3>
                            <p class="text-[11px] text-gray-400">6 operasi terakhir yang tercatat di seluruh modul Bee
                                Kopi</p>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @forelse ($recentLogs as $log)
                            <div class="py-3 flex items-start justify-between gap-3">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="font-bold text-gray-900">{{ $log->user->name ?? 'Sistem' }}</span>
                                        <span
                                            class="px-1.5 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded text-[9px] font-bold">
                                            {{ $log->user->role->label ?? ($log->user->role->name ?? 'Sistem') }}
                                        </span>
                                    </div>
                                    <p class="text-gray-700 font-semibold text-xs">{{ $log->aktivitas }}</p>
                                    @if (!empty($log->keterangan))
                                        <p class="text-[11px] text-gray-500">{{ $log->keterangan }}</p>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 whitespace-nowrap font-medium">
                                    {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                                </span>
                            </div>
                        @empty
                            <p class="text-center text-gray-400 italic py-8">Belum ada rekaman aktivitas di Audit Log.
                            </p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
