<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6 w-full">

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Monitoring Restock & Dispatch Gudang</h1>
                    <p class="text-xs text-gray-500">Pantau ketersediaan produk Gudang Pusat, verifikasi penyiapan
                        barang, dan cetak surat jalan armada Lapak.</p>
                </div>
                <a href="{{ route('produksi.racik-kopi') }}"
                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center gap-1.5 w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    + Racik Kopi Baru
                </a>
            </div>

            <!-- Flash Alert -->
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

                <div wire:click="$set('kategoriFilter', '')"
                    class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1 cursor-pointer hover:bg-rose-50/20 transition">
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

            <!-- Section 2: Rincian Stok Varian (Gudang Pusat) -->
            <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-4">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Rincian Stok Varian Siap
                            Jual (Gudang Pusat)</h2>
                        <p class="text-[11px] text-gray-400">Pantau ketersediaan fisik produk per varian sebelum
                            disiapkan untuk pengiriman lapak.</p>
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

                                <a href="{{ route('produksi.racik-kopi') }}"
                                    class="w-full block text-center py-1 bg-white hover:bg-amber-50 text-amber-700 border border-amber-200 font-bold rounded-lg text-[10px] transition">
                                    + Racik Batch
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-6 text-center text-gray-400 italic">Tidak ada varian produk pada
                            kategori ini.</div>
                    @endforelse
                </div>
            </div>

            <!-- Section 3: Filter Tab Workflow & Tabel Pengiriman -->
            <div class="space-y-3">
                <div
                    class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 items-center justify-between">
                    <!-- Workflow Tabs Gudang -->
                    <div class="flex items-center gap-1.5 text-xs w-full sm:w-auto">
                        <button wire:click="setTabFilter('semua')"
                            class="px-3 py-1.5 rounded-lg font-bold transition {{ $tabFilter === 'semua' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Semua Riwayat
                        </button>
                        <button wire:click="setTabFilter('pending')"
                            class="px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1 {{ $tabFilter === 'pending' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <span>Perlu Disiapkan</span>
                            <span
                                class="px-1.5 py-0.2 bg-amber-800 text-amber-100 rounded-full text-[10px]">Pending</span>
                        </button>
                        <button wire:click="setTabFilter('dalam_transit')"
                            class="px-3 py-1.5 rounded-lg font-bold transition {{ $tabFilter === 'dalam_transit' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Dalam Transit
                        </button>
                        <button wire:click="setTabFilter('selesai')"
                            class="px-3 py-1.5 rounded-lg font-bold transition {{ $tabFilter === 'selesai' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Selesai
                        </button>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <input type="text" wire:model.live="search" placeholder="Cari Kode Distribusi / Lapak..."
                            class="w-full sm:w-64 px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden w-full">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="font-bold text-xs uppercase text-gray-700">Daftar Pengiriman Stok Lapak</h2>
                    </div>

                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr
                                    class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                    <th class="py-3 px-4">Tanggal</th>
                                    <th class="py-3 px-4">Kode Distribusi</th>
                                    <th class="py-3 px-4">Outlet / Lapak</th>
                                    <th class="py-3 px-4">Total Item</th>
                                    <th class="py-3 px-4">Status Pengiriman</th>
                                    <th class="py-3 px-4 text-center">Aksi Gudang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @forelse ($requests as $req)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="py-3.5 px-4 whitespace-nowrap text-gray-500">
                                            {{ \Carbon\Carbon::parse($req->tanggal)->format('d M Y') }}
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-gray-900 whitespace-nowrap">
                                            #{{ $req->kode_distribusi }}</td>
                                        <td class="py-3.5 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                            {{ $req->lapak->nama_lapak ?? '-' }}</td>
                                        <td class="py-3.5 px-4 whitespace-nowrap font-bold text-amber-600">
                                            {{ $req->details->sum(fn($d) => $d->jumlah_cup ?? ($d->jumlah ?? 0)) }} cup
                                        </td>
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
                                                    class="px-2.5 py-1 bg-blue-100 text-blue-800 font-bold rounded-full text-[10px]">Dalam
                                                    Transit</span>
                                            @else
                                                <span
                                                    class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px]">Perlu
                                                    Disiapkan</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button wire:click="openModalDetail({{ $req->id }})"
                                                    class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-[11px] transition">
                                                    Detail & Dispatch
                                                </button>
                                                <button wire:click="openSuratJalan({{ $req->id }})"
                                                    class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold border border-amber-200 rounded-lg text-[11px] transition">
                                                    Surat Jalan
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-gray-400 italic">Belum ada
                                            data pengiriman stok yang sesuai.</td>
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

            <!-- MODAL DETAIL & KONFIRMASI DISPATCH GUDANG -->
            @if ($showModalDetail && $selectedRequest)
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 space-y-4 max-h-[90vh] overflow-y-auto text-xs animate-in fade-in zoom-in duration-150">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-800">Verifikasi Pengiriman Stok
                                    #{{ $selectedRequest->kode_distribusi }}</h3>
                                <p class="text-gray-500 text-[11px]">Lapak Tujuan:
                                    <b>{{ $selectedRequest->lapak->nama_lapak ?? '-' }}</b></p>
                            </div>
                            <button wire:click="$set('showModalDetail', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded-xl text-[11px]">
                            <div>
                                <span class="text-gray-400 block">Dibuat Oleh (Management):</span>
                                <span
                                    class="font-bold text-gray-700">{{ $selectedRequest->pengirim->name ?? 'Management' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block">Tanggal Instruksi:</span>
                                <span
                                    class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($selectedRequest->tanggal)->format('d M Y') }}</span>
                            </div>
                        </div>

                        <!-- Table Rincian Item + Check Ketersediaan Stok Gudang -->
                        <div class="space-y-2">
                            <h4 class="font-bold text-amber-600 uppercase tracking-wider text-[11px]">Pengecekan Fisik
                                Stok Gudang Pusat</h4>
                            <div class="divide-y divide-gray-100 border rounded-xl overflow-hidden">
                                @php $isAllStockReady = true; @endphp
                                @foreach ($selectedRequest->details as $detail)
                                    @php
                                        $qty = $detail->jumlah_cup ?? ($detail->jumlah ?? 0);
                                        $stokPusatSekarang = $detail->produk->stok_pusat ?? 0;
                                        $isReady = $stokPusatSekarang >= $qty;
                                        if (!$isReady) {
                                            $isAllStockReady = false;
                                        }
                                    @endphp
                                    <div class="p-3 flex justify-between items-center bg-white">
                                        <div>
                                            <p class="font-bold text-gray-800">
                                                {{ $detail->produk->nama_produk ?? 'Produk' }}</p>
                                            <p class="text-[10px] text-gray-400">Stok Pusat Tersedia:
                                                <b>{{ $stokPusatSekarang }} cup</b></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-black text-amber-600 text-sm block">{{ $qty }}
                                                cup</span>
                                            @if ($isReady)
                                                <span class="text-[9px] text-emerald-600 font-bold">Stok Cukup ✓</span>
                                            @else
                                                <span class="text-[9px] text-rose-600 font-bold">Stok Defisit (Kurang
                                                    {{ $qty - $stokPusatSekarang }} Cup)!</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if ($selectedRequest->catatan)
                            <div class="p-3 bg-amber-50/50 border border-amber-100 rounded-xl text-gray-600">
                                <span class="font-bold text-amber-800 block mb-0.5">Catatan Instruksi:</span>
                                <p>{{ $selectedRequest->catatan }}</p>
                            </div>
                        @endif

                        <!-- Action Buttons Gudang -->
                        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                            <button wire:click="openSuratJalan({{ $selectedRequest->id }})"
                                class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
                                📄 Cetak Surat Jalan
                            </button>

                            <div class="flex gap-2">
                                <button type="button" wire:click="$set('showModalDetail', false)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">Tutup</button>

                                @if ($selectedRequest->status === 'pending')
                                    <button wire:click="konfirmasiSiapKirim({{ $selectedRequest->id }})"
                                        wire:loading.attr="disabled" @if (!$isAllStockReady) disabled @endif
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold rounded-xl transition shadow-sm">
                                        <span wire:loading.remove wire:target="konfirmasiSiapKirim">Konfirmasi Barang
                                            Siap & Dispatch</span>
                                        <span wire:loading wire:target="konfirmasiSiapKirim">Memproses...</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- MODAL SURAT JALAN (PRINTABLE DELIVERY MANIFEST) -->
            @if ($showSuratJalan && $selectedRequest)
                <div class="fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 space-y-5 max-h-[95vh] overflow-y-auto animate-in fade-in zoom-in duration-150">
                        <div class="flex justify-between items-center border-b border-gray-200 pb-3">
                            <div>
                                <h2 class="text-lg font-black text-gray-900 uppercase tracking-wide">Surat Jalan
                                    Pengiriman Stok</h2>
                                <p class="text-xs text-gray-500">Bee Kopi Management & Distribution System</p>
                            </div>
                            <button wire:click="$set('showSuratJalan', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-xl">&times;</button>
                        </div>

                        <!-- Print Container -->
                        <div id="printableArea" class="space-y-4 text-xs text-gray-800">
                            <div class="grid grid-cols-2 gap-4 border-b border-gray-100 pb-3">
                                <div>
                                    <p class="text-gray-400 text-[10px] uppercase font-bold">Kode Pengiriman:</p>
                                    <p class="font-extrabold text-sm text-amber-600">
                                        #{{ $selectedRequest->kode_distribusi }}</p>
                                    <p class="text-gray-400 text-[10px] uppercase font-bold mt-2">Tanggal Kirim:</p>
                                    <p class="font-bold">
                                        {{ \Carbon\Carbon::parse($selectedRequest->tanggal)->format('d F Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-[10px] uppercase font-bold">Lapak Tujuan:</p>
                                    <p class="font-extrabold text-sm text-gray-900">
                                        {{ $selectedRequest->lapak->nama_lapak ?? '-' }}</p>
                                    <p class="text-gray-500">
                                        {{ $selectedRequest->lapak->lokasi ?? 'Outlet Bee Kopi' }}</p>
                                </div>
                            </div>

                            <table class="w-full text-left border-collapse border border-gray-200">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-700 font-bold uppercase text-[10px]">
                                        <th class="p-2 border border-gray-200">No</th>
                                        <th class="p-2 border border-gray-200">Nama Varian Produk</th>
                                        <th class="p-2 border border-gray-200">Kategori</th>
                                        <th class="p-2 border border-gray-200 text-right">Jumlah (Cup)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedRequest->details as $index => $detail)
                                        @php $qty = $detail->jumlah_cup ?? ($detail->jumlah ?? 0); @endphp
                                        <tr>
                                            <td class="p-2 border border-gray-200 text-center">{{ $index + 1 }}
                                            </td>
                                            <td class="p-2 border border-gray-200 font-bold">
                                                {{ $detail->produk->nama_produk ?? '-' }}</td>
                                            <td class="p-2 border border-gray-200">
                                                {{ $detail->produk->kategori ?? '-' }}</td>
                                            <td class="p-2 border border-gray-200 text-right font-black">
                                                {{ $qty }} cup</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Signatures Field -->
                            <div class="grid grid-cols-3 gap-4 pt-8 text-center text-[11px]">
                                <div>
                                    <p class="text-gray-400 mb-10">Petugas Gudang,</p>
                                    <p class="font-bold border-t border-gray-300 pt-1">{{ auth()->user()->name }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-10">Kurir / Pengirim,</p>
                                    <p class="font-bold border-t border-gray-300 pt-1">( ............................ )
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-10">Penerima Lapak,</p>
                                    <p class="font-bold border-t border-gray-300 pt-1">( ............................ )
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" wire:click="$set('showSuratJalan', false)"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">Batal</button>
                            <button onclick="window.print()"
                                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow-sm">
                                🖨️ Cetak Dokumen
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
