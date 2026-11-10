<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Kasir Sales -
                        {{ Auth::user()->lapak->nama_lapak ?? 'Lapak Belum Dipilih' }}</h1>
                    <p class="text-xs text-gray-500">Pilih menu untuk mencatat transaksi harian.</p>
                </div>
                <div class="text-right hidden sm:block">
                    <span class="text-xs text-gray-400">Tanggal: {{ now()->translatedFormat('d F Y') }}</span>
                </div>
            </div>

            <!-- Ringkasan Total Penjualan Cup (Hari Ini, 7 Hari, 15 Hari, 30 Hari) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Cup Hari Ini -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Cup
                            Hari Ini</p>
                        <h3 class="text-base sm:text-lg font-extrabold text-amber-600 mt-0.5">
                            {{ number_format($cupHariIni, 0, ',', '.') }} <span
                                class="text-xs font-semibold text-amber-500">Cup</span>
                        </h3>
                    </div>
                    <div class="p-2 sm:p-2.5 bg-amber-50 text-amber-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Cup 7 Hari Terakhir -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">7
                            Hari Terakhir</p>
                        <h3 class="text-base sm:text-lg font-extrabold text-emerald-600 mt-0.5">
                            {{ number_format($cup7Hari, 0, ',', '.') }} <span
                                class="text-xs font-semibold text-emerald-500">Cup</span>
                        </h3>
                    </div>
                    <div class="p-2 sm:p-2.5 bg-emerald-50 text-emerald-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Cup 15 Hari Terakhir -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">15
                            Hari Terakhir</p>
                        <h3 class="text-base sm:text-lg font-extrabold text-blue-600 mt-0.5">
                            {{ number_format($cup15Hari, 0, ',', '.') }} <span
                                class="text-xs font-semibold text-blue-500">Cup</span>
                        </h3>
                    </div>
                    <div class="p-2 sm:p-2.5 bg-blue-50 text-blue-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>

                <!-- Cup 30 Hari Terakhir -->
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] sm:text-[11px] font-semibold text-gray-400 uppercase tracking-wider">30
                            Hari Terakhir</p>
                        <h3 class="text-base sm:text-lg font-extrabold text-purple-600 mt-0.5">
                            {{ number_format($cup30Hari, 0, ',', '.') }} <span
                                class="text-xs font-semibold text-purple-500">Cup</span>
                        </h3>
                    </div>
                    <div class="p-2 sm:p-2.5 bg-purple-50 text-purple-600 rounded-lg shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Alert Notification -->
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

            <!-- Section 1: Grid Menu Produk & Keranjang -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Grid Menu Produk -->
                <div class="lg:col-span-2 space-y-4">
                    <input type="text" wire:model.live="search" placeholder="Cari nama menu kopi..."
                        class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white">

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @forelse ($stokLapaks as $stok)
                            <div wire:click="tambahKeKeranjang({{ $stok->produk_id }})"
                                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:border-amber-400 cursor-pointer transition relative flex flex-col justify-between">

                                @if ($stok->jumlah_stok <= $stok->stok_minimum && $stok->jumlah_stok > 0)
                                    <span
                                        class="absolute top-2 right-2 px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold rounded-full animate-pulse">Menipis</span>
                                @elseif($stok->jumlah_stok <= 0)
                                    <span
                                        class="absolute top-2 right-2 px-2 py-0.5 bg-rose-100 text-rose-700 text-[10px] font-bold rounded-full">Habis</span>
                                @endif

                                <div>
                                    <span class="text-xs text-gray-400 font-medium">{{ $stok->produk->kategori }}</span>
                                    <h3 class="font-bold text-gray-800 text-sm mt-0.5">{{ $stok->produk->nama_produk }}
                                    </h3>
                                    <p class="text-amber-600 font-extrabold text-sm mt-1">Rp
                                        {{ number_format($stok->produk->harga_jual, 0, ',', '.') }}</p>
                                </div>

                                <div
                                    class="mt-3 pt-2 border-t border-gray-50 flex justify-between items-center text-xs">
                                    <span class="text-gray-500">Stok:</span>
                                    <span
                                        class="font-bold {{ $stok->jumlah_stok <= $stok->stok_minimum ? 'text-amber-600' : 'text-gray-700' }}">
                                        {{ $stok->jumlah_stok }} cup
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full py-8 text-center text-gray-400 text-xs">
                                Tidak ada produk yang tersedia.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Panel Ringkasan Keranjang -->
                <div
                    class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between h-fit space-y-4">
                    <div>
                        <h2 class="font-bold text-gray-800 border-b border-gray-100 pb-3 text-sm">Keranjang Transaksi
                        </h2>

                        <div class="divide-y divide-gray-100 max-h-72 overflow-y-auto my-2">
                            @forelse ($cart as $item)
                                <div class="py-2.5 flex justify-between items-center text-xs">
                                    <div class="space-y-0.5">
                                        <p class="font-bold text-gray-800">{{ $item['nama_produk'] }}</p>
                                        <p class="text-gray-400">@ Rp
                                            {{ number_format($item['harga_jual'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button wire:click="kurangiDariKeranjang({{ $item['produk_id'] }})"
                                            class="w-6 h-6 rounded-md bg-gray-100 text-gray-600 font-bold flex items-center justify-center hover:bg-gray-200">-</button>
                                        <span
                                            class="font-bold text-gray-800 w-4 text-center">{{ $item['jumlah'] }}</span>
                                        <button wire:click="tambahKeKeranjang({{ $item['produk_id'] }})"
                                            class="w-6 h-6 rounded-md bg-amber-100 text-amber-700 font-bold flex items-center justify-center hover:bg-amber-200">+</button>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-gray-400 text-xs">
                                    Keranjang masih kosong
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-gray-100 text-xs">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                            <select wire:model.live="metode_pembayaran"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                                <option value="tunai">Tunai (Cash)</option>
                                <option value="qris">QRIS / Non-Tunai</option>
                                <option value="split">Tunai & QRIS (Split Payment)</option>
                            </select>
                        </div>

                        <!-- Form Input Khusus Opsi Split Payment -->
                        <!-- Form Input Khusus Opsi Split Payment -->
                        @if ($metode_pembayaran === 'split')
                            <div class="p-3 bg-amber-50/70 border border-amber-200/80 rounded-lg space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Nominal
                                            Tunai</label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-2.5 top-2 text-xs text-gray-400 font-bold">Rp</span>
                                            <input type="number" wire:model.live="bayar_tunai" placeholder="0"
                                                onfocus="this.select()"
                                                class="w-full pl-7 pr-2 py-1.5 border border-gray-200 rounded-md text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Nominal
                                            QRIS</label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-2.5 top-2 text-xs text-gray-400 font-bold">Rp</span>
                                            <input type="number" wire:model.live="bayar_qris" placeholder="0"
                                                onfocus="this.select()"
                                                class="w-full pl-7 pr-2 py-1.5 border border-gray-200 rounded-md text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="flex justify-between items-center text-[11px] pt-1.5 border-t border-amber-200/60">
                                    <span class="text-gray-600">Total Pembayaran:</span>
                                    <span
                                        class="font-bold {{ (int) $bayar_tunai + (int) $bayar_qris === (int) $this->totalProses ? 'text-emerald-600' : 'text-rose-600' }}">
                                        Rp {{ number_format((int) $bayar_tunai + (int) $bayar_qris, 0, ',', '.') }}
                                    </span>
                                </div>

                                @if ((int) $bayar_tunai + (int) $bayar_qris !== (int) $this->totalProses)
                                    <p class="text-[10px] text-rose-600 italic">
                                        * Total Tunai + QRIS harus persis Rp
                                        {{ number_format($this->totalProses, 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <div class="flex justify-between items-center text-sm font-bold pt-2 border-t border-gray-50">
                            <span class="text-gray-700">Total:</span>
                            <span class="text-amber-600 text-base">Rp
                                {{ number_format($this->totalProses, 0, ',', '.') }}</span>
                        </div>

                        <button wire:click="prosesTransaksi"
                            class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg transition shadow-sm text-xs">
                            Proses Transaksi
                        </button>
                    </div>
                </div>
            </div>

            <!-- Section 2: Tabel Log Riwayat Penjualan Lapak Ini -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h2 class="font-bold text-sm text-gray-800">Riwayat Penjualan Hari Ini</h2>
                        <p class="text-xs text-gray-500">Daftar transaksi harian yang berhasil diproses di lapak ini.
                        </p>
                    </div>
                    <span
                        class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold">
                        Total {{ $riwayatTransaksi->count() }} Transaksi
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Tanggal & Waktu</th>
                                <th class="py-3 px-4">No. Transaksi</th>
                                <th class="py-3 px-4">Detail Pesanan</th>
                                <th class="py-3 px-4">Metode Bayar</th>
                                <th class="py-3 px-4">Total Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($riwayatTransaksi as $tx)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 text-gray-500 whitespace-nowrap">
                                        {{ $tx->created_at->format('d/m/Y, H:i') }} WIB
                                    </td>
                                    <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        #{{ $tx->nomor_transaksi ?? ($tx->kode_transaksi ?? $tx->id) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($tx->details as $d)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-700 rounded border border-gray-200 text-[11px]">
                                                    {{ $d->produk->nama_produk ?? 'Produk' }}
                                                    x<b>{{ $d->jumlah }}</b>
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase 
                                            {{ strtolower($tx->metode_pembayaran) === 'qris'
                                                ? 'bg-blue-50 text-blue-600 border border-blue-200'
                                                : (strtolower($tx->metode_pembayaran) === 'split'
                                                    ? 'bg-purple-50 text-purple-600 border border-purple-200'
                                                    : 'bg-emerald-50 text-emerald-600 border border-emerald-200') }}">
                                            {{ $tx->metode_pembayaran === 'split' ? 'Tunai & QRIS' : $tx->metode_pembayaran ?? 'Tunai' }}
                                        </span>

                                        @if (strtolower($tx->metode_pembayaran) === 'split')
                                            <div class="text-[10px] text-gray-500 mt-1 space-y-0.5">
                                                <div>Tunai: <b class="text-gray-700">Rp
                                                        {{ number_format($tx->bayar_tunai ?? 0, 0, ',', '.') }}</b>
                                                </div>
                                                <div>QRIS: <b class="text-gray-700">Rp
                                                        {{ number_format($tx->bayar_qris ?? 0, 0, ',', '.') }}</b>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 font-extrabold text-amber-600 whitespace-nowrap">
                                        Rp {{ number_format($tx->total_harga ?? $tx->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        Belum ada transaksi penjualan masuk hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
