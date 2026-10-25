<!-- Load Chart.js CDN di bagian paling atas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">

        <!-- Header Dashboard -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard Eksekutif</h1>
                <p class="text-sm text-gray-500">Ringkasan performa penjualan, pengeluaran, dan tren menu di seluruh
                    armada lapak.</p>
            </div>
            <div>
                <select wire:model.live="filterRange"
                    class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500 shadow-sm">
                    <option value="7">7 Hari Terakhir</option>
                    <option value="30">30 Hari Terakhir</option>
                    <option value="90">3 Bulan Terakhir</option>
                </select>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Penjualan</span>
                    <h2 class="text-2xl font-extrabold text-emerald-600 mt-1">Rp
                        {{ number_format($totalOmzet, 0, ',', '.') }}</h2>
                </div>
                <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pengeluaran
                        Operasional</span>
                    <h2 class="text-2xl font-extrabold text-rose-600 mt-1">Rp
                        {{ number_format($totalPengeluaran, 0, ',', '.') }}</h2>
                </div>
                <div class="p-3 bg-rose-50 rounded-lg text-rose-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Grid Grafik (2x2) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Grafik 1: Omzet Penjualan -->
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-3">
                <h3 class="font-bold text-gray-800 text-sm">Grafik Penjualan Total</h3>
                <div class="h-64" wire:ignore data-labels="{{ json_encode($chartOmzetLabels) }}"
                    data-values="{{ json_encode($chartOmzetValues) }}" x-data="{
                        init() {
                            let labels = JSON.parse(this.$el.dataset.labels || '[]');
                            let values = JSON.parse(this.$el.dataset.values || '[]');
                            new Chart(this.$refs.canvas, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Omzet (Rp)',
                                        data: values,
                                        borderColor: '#059669',
                                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                                        fill: true,
                                        tension: 0.3
                                    }]
                                },
                                options: { responsive: true, maintainAspectRatio: false }
                            });
                        }
                    }">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>

            <!-- Grafik 2: Penjualan Per Lapak -->
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-3">
                <h3 class="font-bold text-gray-800 text-sm">Penjualan Per Armada Lapak</h3>
                <div class="h-64" wire:ignore data-labels="{{ json_encode($chartLapakLabels) }}"
                    data-values="{{ json_encode($chartLapakValues) }}" x-data="{
                        init() {
                            let labels = JSON.parse(this.$el.dataset.labels || '[]');
                            let values = JSON.parse(this.$el.dataset.values || '[]');
                            new Chart(this.$refs.canvas, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Total Jualan (Rp)',
                                        data: values,
                                        backgroundColor: '#d97706'
                                    }]
                                },
                                options: { responsive: true, maintainAspectRatio: false }
                            });
                        }
                    }">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>

            <!-- Grafik 3: Trend Produk Terlaris -->
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-3">
                <h3 class="font-bold text-gray-800 text-sm">Top 10 Produk Terlaris (Cup)</h3>
                <div class="h-64" wire:ignore data-labels="{{ json_encode($chartProdukLabels) }}"
                    data-values="{{ json_encode($chartProdukValues) }}" x-data="{
                        init() {
                            let labels = JSON.parse(this.$el.dataset.labels || '[]');
                            let values = JSON.parse(this.$el.dataset.values || '[]');
                            new Chart(this.$refs.canvas, {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Terjual (Cup)',
                                        data: values,
                                        backgroundColor: '#2563eb'
                                    }]
                                },
                                options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false }
                            });
                        }
                    }">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>

            <!-- Grafik 4: Trend Pengeluaran Operasional -->
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-3">
                <h3 class="font-bold text-gray-800 text-sm">Trend Pengeluaran Operasional</h3>
                <div class="h-64" wire:ignore data-labels="{{ json_encode($chartPengeluaranLabels) }}"
                    data-values="{{ json_encode($chartPengeluaranValues) }}" x-data="{
                        init() {
                            let labels = JSON.parse(this.$el.dataset.labels || '[]');
                            let values = JSON.parse(this.$el.dataset.values || '[]');
                            new Chart(this.$refs.canvas, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Pengeluaran (Rp)',
                                        data: values,
                                        borderColor: '#e11d48',
                                        backgroundColor: 'rgba(225, 29, 72, 0.1)',
                                        fill: true,
                                        tension: 0.3
                                    }]
                                },
                                options: { responsive: true, maintainAspectRatio: false }
                            });
                        }
                    }">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>

        </div>

    </div>
</div>
