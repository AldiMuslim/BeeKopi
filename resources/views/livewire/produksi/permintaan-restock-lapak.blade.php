<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6 w-full">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Monitoring Restock & Dashboard Produksi</h1>
                    <p class="text-xs text-gray-500">Pantau ketersediaan produk di Gudang Pusat dan riwayat pengiriman
                        stok ke armada Lapak/Outlet.</p>
                </div>
                <a href="{{ route('produksi.racik-kopi') }}"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center gap-1.5 w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Racik Kopi Baru
                </a>
            </div>

            <!-- Flash Alert Halaman Utama -->
            @if (session()->has('message'))
                <div
                    class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg font-medium">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Section 1: Top Stat Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Total Stok Siap Jual</p>
                    <p class="text-xl font-black text-amber-600">{{ number_format($totalStokPusat, 0, ',', '.') }} <span
                            class="text-xs font-normal text-gray-500">cup</span></p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Total Varian Menu</p>
                    <p class="text-xl font-black text-gray-800">{{ $totalVarian }} <span
                            class="text-xs font-normal text-gray-500">varian</span></p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Perlu Diracik (&lt; 10 Cup)</p>
                    <p class="text-xl font-black {{ $stokKritisCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $stokKritisCount }} <span class="text-xs font-normal text-gray-500">menu</span>
                    </p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Distribusi Hari Ini</p>
                    <p class="text-xl font-black text-blue-600">{{ number_format($distribusiHariIni, 0, ',', '.') }}
                        <span class="text-xs font-normal text-gray-500">cup</span></p>
                </div>
            </div>

            <!-- Section 2: Rincian Stok Varian (Gudang Pusat) + Filter & Shortcut Racik -->
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Rincian Stok Varian Siap
                            Jual (Gudang Pusat)</h2>
                        <p class="text-[11px] text-gray-400">Pantau ketersediaan fisik produk per varian sebelum
                            didistribusikan.</p>
                    </div>

                    <!-- Filter Kategori Varian -->
                    <div class="flex items-center gap-1 text-xs">
                        <span class="text-gray-400 font-medium mr-1">Kategori:</span>
                        <button wire:click="$set('kategoriFilter', '')"
                            class="px-2.5 py-1 rounded-lg font-bold transition {{ $kategoriFilter === '' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semua</button>
                        <button wire:click="$set('kategoriFilter', 'Kopi')"
                            class="px-2.5 py-1 rounded-lg font-bold transition {{ $kategoriFilter === 'Kopi' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Kopi</button>
                        <button wire:click="$set('kategoriFilter', 'Non-Kopi')"
                            class="px-2.5 py-1 rounded-lg font-bold transition {{ $kategoriFilter === 'Non-Kopi' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Non-Kopi</button>
                        <button wire:click="$set('kategoriFilter', 'Snack')"
                            class="px-2.5 py-1 rounded-lg font-bold transition {{ $kategoriFilter === 'Snack' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Snack</button>
                    </div>
                </div>

                <!-- Grid Card Varian -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
                    @forelse ($varians as $v)
                        <div
                            class="p-3 bg-gray-50/80 rounded-xl border border-gray-100 flex flex-col justify-between space-y-2">
                            <div>
                                <span
                                    class="text-[10px] text-gray-400 uppercase font-semibold block">{{ $v->kategori }}</span>
                                <p class="font-bold text-gray-800 truncate" title="{{ $v->nama_produk }}">
                                    {{ $v->nama_produk }}</p>
                            </div>

                            <div class="space-y-2 pt-1 border-t border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span
                                        class="font-black text-sm {{ $v->stok_pusat < 10 ? 'text-rose-600' : 'text-amber-600' }}">
                                        {{ number_format($v->stok_pusat, 0, ',', '.') }} <span
                                            class="text-[10px] font-normal text-gray-500">cup</span>
                                    </span>
                                    @if ($v->stok_pusat < 10)
                                        <span
                                            class="px-1.5 py-0.5 bg-rose-100 text-rose-700 font-bold rounded text-[9px]">Menipis</span>
                                    @else
                                        <span
                                            class="px-1.5 py-0.5 bg-emerald-100 text-emerald-700 font-bold rounded text-[9px]">Aman</span>
                                    @endif
                                </div>

                                <!-- Shortcut Racik Kopi Cepat -->
                                <a href="{{ route('produksi.racik-kopi') }}"
                                    class="w-full block text-center py-1 bg-white hover:bg-amber-50 text-amber-700 border border-amber-200 font-bold rounded-lg text-[10px] transition">
                                    + Racik Batch
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-6 text-center text-gray-400 italic">
                            Tidak ada varian produk pada kategori ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Section 3: Filter & Tabel Monitoring Distribusi Stok Lapak -->
            <div class="space-y-3">
                <div
                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                    <input type="text" wire:model.live="search" placeholder="Cari Kode Distribusi / Nama Lapak..."
                        class="w-full sm:w-80 px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500">

                    <select wire:model.live="statusFilter"
                        class="w-full sm:w-48 px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                        <option value="">-- Semua Status Distribusi --</option>
                        <option value="pending">Pending</option>
                        <option value="dalam_transit">Dalam Transit</option>
                        <option value="diterima">Diterima Lapak</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="font-bold text-xs uppercase text-gray-700">Riwayat Pengiriman & Transfer Stok Lapak
                        </h2>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Kode Distribusi</th>
                                    <th class="py-3 px-4">Outlet / Lapak</th>
                                    <th class="py-3 px-4">Total Items</th>
                                    <th class="py-3 px-4">Pengirim</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @forelse ($requests as $req)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="py-3.5 px-4 whitespace-nowrap text-gray-500">
                                            {{ \Carbon\Carbon::parse($req->tanggal)->format('d M Y') }}</td>
                                        <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">
                                            #{{ $req->kode_distribusi }}</td>
                                        <td class="py-3.5 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                            {{ $req->lapak->nama_lapak ?? '-' }}</td>
                                        <td class="py-3.5 px-4 whitespace-nowrap font-bold text-amber-600">
                                            {{ $req->details->sum(fn($d) => $d->jumlah_cup ?? ($d->jumlah ?? 0)) }} cup
                                        </td>
                                        <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                            {{ $req->pengirim->name ?? 'Management' }}</td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            @if ($req->status === 'diterima')
                                                <span
                                                    class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Diterima
                                                    Lapak</span>
                                            @elseif ($req->status === 'ditolak')
                                                <span
                                                    class="px-2.5 py-1 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px]">Ditolak</span>
                                            @elseif ($req->status === 'dalam_transit')
                                                <span
                                                    class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px]">Dalam
                                                    Transit</span>
                                            @else
                                                <span
                                                    class="px-2.5 py-1 bg-gray-100 text-gray-600 font-bold rounded-full text-[10px]">{{ ucfirst($req->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <button wire:click="openModalDetail({{ $req->id }})"
                                                class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-[11px] transition">
                                                Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-gray-400">Belum ada data
                                            distribusi stok.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-gray-100">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>

            <!-- Modal Detail Monitoring -->
            @if ($showModalDetail && $selectedRequest)
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-xl shadow-lg w-full max-w-xl p-6 space-y-4 max-h-[90vh] overflow-y-auto text-xs">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-800">Detail Distribusi Stok
                                    #{{ $selectedRequest->kode_distribusi }}</h3>
                                <p class="text-gray-500 text-[11px]">Lapak Tujuan:
                                    {{ $selectedRequest->lapak->nama_lapak ?? '-' }}</p>
                            </div>
                            <button wire:click="$set('showModalDetail', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded-lg text-[11px]">
                            <div>
                                <span class="text-gray-400 block">Pengirim (Management):</span>
                                <span
                                    class="font-bold text-gray-700">{{ $selectedRequest->pengirim->name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Penerima (Sales):</span>
                                <span
                                    class="font-bold text-gray-700">{{ $selectedRequest->penerima->name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Tanggal Pengiriman:</span>
                                <span
                                    class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($selectedRequest->tanggal)->format('d M Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Status:</span>
                                <span class="font-bold uppercase text-amber-600">{{ $selectedRequest->status }}</span>
                            </div>
                        </div>

                        <!-- Daftar Item Produk -->
                        <div class="space-y-2">
                            <h4 class="font-bold text-amber-600 uppercase tracking-wider text-[11px]">Rincian Barang
                                yang Ditransfer</h4>
                            <div class="divide-y divide-gray-100 border rounded-lg overflow-hidden">
                                @foreach ($selectedRequest->details as $detail)
                                    @php
                                        $qty = $detail->jumlah_cup ?? ($detail->jumlah ?? 0);
                                    @endphp
                                    @if ($qty > 0)
                                        <div class="p-3 flex justify-between items-center bg-white">
                                            <div>
                                                <p class="font-bold text-gray-800">
                                                    {{ $detail->produk->nama_produk ?? 'Produk' }}</p>
                                                <p class="text-[11px] text-gray-400">Kategori:
                                                    {{ $detail->produk->kategori ?? '-' }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span
                                                    class="font-extrabold text-amber-600 text-sm">{{ $qty }}
                                                    cup</span>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        @if ($selectedRequest->catatan)
                            <div class="p-3 bg-amber-50/50 border border-amber-100 rounded-lg text-gray-600">
                                <span class="font-bold text-amber-800 block mb-0.5">Catatan:</span>
                                <p>{{ $selectedRequest->catatan }}</p>
                            </div>
                        @endif

                        <!-- Tombol Tutup -->
                        <div class="flex justify-end pt-3 border-t border-gray-100">
                            <button wire:click="$set('showModalDetail', false)"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold rounded-lg transition">Tutup</button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
