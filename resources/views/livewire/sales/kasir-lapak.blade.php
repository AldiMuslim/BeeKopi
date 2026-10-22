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
                            <select wire:model="metode_pembayaran"
                                class="w-full px-3 py-2 border rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                                <option value="tunai">Tunai (Cash)</option>
                                <option value="qris">QRIS / Non-Tunai</option>
                            </select>
                        </div>

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
                                        #{{ $tx->kode_transaksi ?? $tx->id }}
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
                                            class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ strtolower($tx->metode_pembayaran) === 'qris' ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200' }}">
                                            {{ $tx->metode_pembayaran ?? 'Tunai' }}
                                        </span>
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
