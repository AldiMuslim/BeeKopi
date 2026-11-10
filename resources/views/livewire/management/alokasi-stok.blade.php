<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen print:bg-white print:p-0">
        <div class="max-w-7xl mx-auto space-y-6 print:hidden">

            <!-- Header Page -->
            <div>
                <h1 class="text-xl font-bold text-gray-800">Alokasi & Distribusi Stok</h1>
                <p class="text-xs text-gray-500">Kirim produk siap jual dari Gudang Pusat ke armada lapak cabang.</p>
            </div>

            <!-- Flash Alert -->
            @if (session()->has('message'))
                <div
                    class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl flex justify-between items-center shadow-sm">
                    <span>{{ session('message') }}</span>
                    <button type="button" class="font-bold text-emerald-800"
                        onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif
            @if (session()->has('error'))
                <div
                    class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex justify-between items-center shadow-sm">
                    <span>{{ session('error') }}</span>
                    <button type="button" class="font-bold text-rose-800"
                        onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            <!-- Section 1: Form Alokasi Pengiriman Stok Ke Lapak -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4 text-xs">
                <div
                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Form Alokasi Pengiriman
                            Stok</h2>
                        <p class="text-[11px] text-gray-400">Pilih armada lapak tujuan dan isi jumlah cup yang akan
                            dikirim pada daftar menu di bawah.</p>
                    </div>
                    <div class="shrink-0">
                        <span
                            class="px-3 py-1.5 bg-amber-50 text-amber-800 font-extrabold border border-amber-200 rounded-lg text-xs shadow-sm">
                            Total Alokasi: {{ $this->totalCupDikirim }} Cup
                        </span>
                    </div>
                </div>

                <form wire:submit.prevent="kirimStok" class="space-y-4">

                    <!-- Top Controls: Lapak & Search Bar Produk -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Pilih Lapak Cabang Tujuan:</label>
                            <select wire:model="lapak_id"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-800">
                                <option value="">-- Pilih Armada Lapak --</option>
                                @foreach ($lapaks as $l)
                                    <option value="{{ $l->id }}">{{ $l->nama_lapak }}</option>
                                @endforeach
                            </select>
                            @error('lapak_id')
                                <span class="text-rose-500 text-[11px] block mt-1 font-semibold">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Cari Menu Produk Gudang:</label>
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.250ms="search_produk"
                                    placeholder="Ketik nama menu kopi atau kategori..."
                                    class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white text-xs pr-8">
                                @if ($search_produk)
                                    <button type="button" wire:click="$set('search_produk', '')"
                                        class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 font-bold">&times;</button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Direct List Semua Menu Kopi Aktif dengan Validasi Max -->
                    <div class="space-y-2">
                        <label class="block font-bold text-gray-700">Daftar Menu Siap Jual (Gudang Pusat):</label>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto pr-1">
                            @forelse ($produks as $p)
                                @php
                                    $valInput = (int) ($jumlah_cup[$p->id] ?? 0);
                                    $isOver = $valInput > $p->stok_pusat;
                                @endphp
                                <div
                                    class="p-3 rounded-xl border transition {{ $isOver ? 'bg-rose-50 border-rose-300' : 'bg-gray-50/80 border-gray-100 hover:border-gray-200' }} flex items-center justify-between gap-3">
                                    <div class="space-y-0.5 min-w-0">
                                        <span
                                            class="text-[10px] text-gray-400 uppercase font-semibold block">{{ $p->kategori }}</span>
                                        <h3 class="font-bold text-gray-800 truncate text-xs">{{ $p->nama_produk }}</h3>
                                        <p class="text-[11px] text-gray-500">
                                            Stok Gudang: <b
                                                class="{{ $p->stok_pusat > 0 ? 'text-amber-600' : 'text-rose-500' }}">{{ $p->stok_pusat }}
                                                cup</b>
                                        </p>
                                    </div>

                                    <div class="w-28 shrink-0">
                                        <label class="block text-[10px] font-semibold text-gray-500 mb-0.5">Dikirim
                                            (Cup)</label>
                                        <input type="number" min="0" max="{{ $p->stok_pusat }}"
                                            wire:model.live="jumlah_cup.{{ $p->id }}" placeholder="0"
                                            onfocus="this.select()"
                                            class="w-full px-2.5 py-1.5 border rounded-lg focus:ring-2 bg-white font-black text-center text-xs {{ $isOver ? 'border-rose-500 text-rose-700 focus:ring-rose-500' : 'border-gray-200 text-gray-800 focus:ring-amber-500' }}">
                                        @if ($isOver)
                                            <span
                                                class="text-[9px] text-rose-600 font-bold block text-center mt-0.5">Melebihi
                                                Stok!</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-8 text-center text-gray-400 italic">Tidak ada menu produk
                                    aktif yang cocok dengan pencarian.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Catatan Pengiriman -->
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Catatan Pengiriman (Opsional):</label>
                        <input type="text" wire:model="catatan"
                            placeholder="Contoh: Titip termos es batu tambahan dan kantong plastik"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white">
                    </div>

                    <!-- Submit Button dengan Proteksi Loading & Double Submit -->
                    <div class="pt-2 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled"
                            class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                            <span wire:loading.remove wire:target="kirimStok">Proses & Kirim Stok Ke Lapak
                                ({{ $this->totalCupDikirim }} Cup)</span>
                            <span wire:loading wire:target="kirimStok" class="flex items-center gap-1.5">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Memproses Alokasi...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Tabel Histori Pengiriman Stok Pusat -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div
                    class="p-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50">
                    <div>
                        <h2 class="font-bold text-xs uppercase text-gray-700">Histori Pengiriman Stok Pusat</h2>
                        <p class="text-[11px] text-gray-400">Klik baris atau tombol detail untuk melihat rincian
                            pengiriman, status transit, atau surat jalan.</p>
                    </div>

                    <!-- Filter Control Bar: Tabs & Inputs -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <!-- Tab Counters -->
                        <div class="flex bg-gray-200/70 p-1 rounded-lg">
                            <button wire:click="filterStatusTab('pending')"
                                class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 {{ $filter_status === 'pending' ? 'bg-amber-600 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                                <span>Transit</span>
                                <span
                                    class="px-1.5 py-0.2 rounded-full text-[9px] {{ $filter_status === 'pending' ? 'bg-amber-800 text-amber-100' : 'bg-gray-300 text-gray-700' }}">{{ $countTransit }}</span>
                            </button>
                            <button wire:click="filterStatusTab('diterima')"
                                class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 {{ $filter_status === 'diterima' ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                                <span>Diterima</span>
                                <span
                                    class="px-1.5 py-0.2 rounded-full text-[9px] {{ $filter_status === 'diterima' ? 'bg-emerald-800 text-emerald-100' : 'bg-gray-300 text-gray-700' }}">{{ $countDiterima }}</span>
                            </button>
                            <button wire:click="filterStatusTab('ditolak')"
                                class="px-2.5 py-1 rounded-md font-bold transition flex items-center gap-1 {{ $filter_status === 'ditolak' ? 'bg-rose-600 text-white' : 'text-gray-600 hover:text-gray-900' }}">
                                <span>Ditolak / Retur</span>
                                <span
                                    class="px-1.5 py-0.2 rounded-full text-[9px] {{ $filter_status === 'ditolak' ? 'bg-rose-800 text-rose-100' : 'bg-gray-300 text-gray-700' }}">{{ $countDitolak }}</span>
                            </button>
                        </div>

                        <!-- Dropdown Lapak -->
                        <select wire:model.live="filter_lapak"
                            class="px-2.5 py-1.5 border rounded-lg bg-white font-semibold text-gray-700">
                            <option value="">Semua Lapak</option>
                            @foreach ($lapaks as $l)
                                <option value="{{ $l->id }}">{{ $l->nama_lapak }}</option>
                            @endforeach
                        </select>

                        <!-- Search Kode -->
                        <input type="text" wire:model.live.debounce.300ms="search_distribusi"
                            placeholder="Cari Kode DST..." class="px-2.5 py-1.5 border rounded-lg bg-white">

                        @if ($filter_status || $filter_lapak || $search_distribusi)
                            <button wire:click="resetFilterTable"
                                class="px-2.5 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg transition">Reset</button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Kode / Tanggal</th>
                                <th class="py-3 px-4">Lapak Tujuan</th>
                                <th class="py-3 px-4">Rincian Menu Dikirim</th>
                                <th class="py-3 px-4">Pengirim (Manajemen)</th>
                                <th class="py-3 px-4">Penerima (Sales)</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($distribusis as $dst)
                                <tr class="hover:bg-amber-50/40 cursor-pointer transition"
                                    wire:click="bukaDetail({{ $dst->id }})">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span
                                            class="font-bold text-gray-900 block">#{{ $dst->kode_distribusi }}</span>
                                        <span
                                            class="text-gray-400 text-[11px]">{{ \Carbon\Carbon::parse($dst->tanggal)->format('d M Y') }}</span>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-gray-800 whitespace-nowrap">
                                        {{ $dst->lapak->nama_lapak ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($dst->details as $det)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 bg-amber-50 text-amber-800 rounded border border-amber-200 text-[11px]">
                                                    {{ $det->produk->nama_produk ?? 'Produk' }}:
                                                    <b>&nbsp;{{ $det->jumlah_cup }} cup</b>
                                                </span>
                                            @endforeach
                                        </div>
                                        @if ($dst->catatan)
                                            <p class="text-[10px] text-gray-400 mt-1 italic">Ket: {{ $dst->catatan }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                                        {{ $dst->pengirim->name ?? 'Manajer Bee Kopi' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                                        {{ $dst->penerima->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if ($dst->status === 'diterima')
                                            <span
                                                class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Diterima</span>
                                        @elseif ($dst->status === 'ditolak')
                                            <span
                                                class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px]">Ditolak
                                                Sales</span>
                                        @elseif ($dst->status === 'retur_selesai')
                                            <span
                                                class="px-2.5 py-0.5 bg-purple-100 text-purple-800 font-bold rounded-full text-[10px]">Retur
                                                Selesai</span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px] animate-pulse">Dalam
                                                Transit</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap"
                                        onclick="event.stopPropagation()">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button wire:click="bukaDetail({{ $dst->id }})"
                                                class="px-2.5 py-1 bg-amber-100 hover:bg-amber-200 text-amber-800 font-bold rounded text-[11px]">
                                                Detail
                                            </button>

                                            @if ($dst->status === 'pending')
                                                <button wire:click="batalkanPengiriman({{ $dst->id }})"
                                                    wire:confirm="Yakin ingin membatalkan pengiriman stok ini? Stok akan dikembalikan ke Gudang Pusat."
                                                    class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded text-[11px] border border-rose-200">
                                                    Batal Kirim
                                                </button>
                                            @elseif ($dst->status === 'ditolak')
                                                <button wire:click="prosesReturStokDitolak({{ $dst->id }})"
                                                    wire:confirm="Kembalikan kuantitas stok yang ditolak Sales ini ke Gudang Pusat?"
                                                    class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded text-[11px] shadow-sm">
                                                    Restok Retur
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 italic">Belum ada riwayat
                                        alokasi stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $distribusis->links() }}
                </div>
            </div>

        </div>

        <!-- MODAL CARD RINCIAN & SURAT JALAN PENGIRIMAN -->
        @if ($selectedDistribusi)
            <div
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 print:p-0 print:bg-white print:fixed print:inset-0">
                <div
                    class="bg-white w-full max-w-lg rounded-2xl shadow-xl overflow-hidden space-y-0 animate-in fade-in zoom-in duration-150 print:shadow-none print:w-full print:max-w-none">

                    <!-- Modal Header (Tampil Saat Normal & Cetak) -->
                    <div
                        class="p-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between print:bg-white print:border-b-2 print:border-gray-800">
                        <div>
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block print:text-gray-700">Surat
                                Jalan / Bukti Pengiriman Stok</span>
                            <h3 class="text-base font-extrabold text-gray-900">
                                #{{ $selectedDistribusi->kode_distribusi }}</h3>
                        </div>
                        <button wire:click="tutupDetail"
                            class="text-gray-400 hover:text-gray-600 font-bold text-lg p-1 print:hidden">&times;</button>
                    </div>

                    <!-- Modal Body -->
                    <div
                        class="p-5 space-y-4 text-xs max-h-[80vh] overflow-y-auto print:max-h-none print:overflow-visible">
                        <!-- Status & Metadata -->
                        <div
                            class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100 print:bg-white print:border-gray-300">
                            <div>
                                <span class="text-gray-400 text-[10px] block print:text-gray-600">Lapak Tujuan</span>
                                <span
                                    class="font-extrabold text-gray-800 text-sm">{{ $selectedDistribusi->lapak->nama_lapak ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] block print:text-gray-600">Status
                                    Pengiriman</span>
                                @if ($selectedDistribusi->status === 'diterima')
                                    <span
                                        class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px] inline-block">Diterima
                                        Sales</span>
                                @elseif ($selectedDistribusi->status === 'ditolak')
                                    <span
                                        class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px] inline-block">Ditolak
                                        Sales</span>
                                @elseif ($selectedDistribusi->status === 'retur_selesai')
                                    <span
                                        class="px-2 py-0.5 bg-purple-100 text-purple-800 font-bold rounded-full text-[10px] inline-block">Retur
                                        Restok Selesai</span>
                                @else
                                    <span
                                        class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px] inline-block">Dalam
                                        Transit</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] block print:text-gray-600">Pengirim
                                    (Manajemen)</span>
                                <span
                                    class="font-bold text-gray-700">{{ $selectedDistribusi->pengirim->name ?? 'Manajer Bee Kopi' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] block print:text-gray-600">Penerima
                                    (Sales)</span>
                                <span
                                    class="font-bold text-gray-700">{{ $selectedDistribusi->penerima->name ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Rincian Produk Dikirim -->
                        <div class="space-y-2">
                            <span class="font-bold text-gray-800 block text-xs">Daftar Varian Menu Dikirim:</span>
                            <div
                                class="space-y-1.5 border border-gray-100 rounded-xl p-3 bg-white print:border-gray-300">
                                @foreach ($selectedDistribusi->details as $det)
                                    <div
                                        class="flex justify-between items-center text-xs py-1 border-b border-gray-50 last:border-0 print:border-gray-200">
                                        <span
                                            class="font-semibold text-gray-700">{{ $det->produk->nama_produk ?? 'Produk' }}</span>
                                        <span
                                            class="font-extrabold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 print:bg-white print:border-0">
                                            {{ $det->jumlah_cup }} cup
                                        </span>
                                    </div>
                                @endforeach
                                <div
                                    class="flex justify-between items-center text-xs font-bold pt-2 text-gray-800 border-t border-gray-100 print:border-gray-400">
                                    <span>Total Alokasi Kirim:</span>
                                    <span
                                        class="text-amber-700 font-black text-sm print:text-gray-900">{{ $selectedDistribusi->details->sum('jumlah_cup') }}
                                        Cup</span>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Pengirim (Manajemen) -->
                        @if ($selectedDistribusi->catatan)
                            <div
                                class="p-3 bg-amber-50/60 border border-amber-200/60 rounded-xl space-y-1 print:bg-white print:border-gray-300">
                                <span class="font-bold text-amber-900 block text-[11px] print:text-gray-800">Catatan
                                    Dari Pengirim:</span>
                                <p class="text-amber-800 text-xs italic print:text-gray-700">
                                    {{ $selectedDistribusi->catatan }}</p>
                            </div>
                        @endif

                        <!-- Catatan / Respon Sales -->
                        @if ($selectedDistribusi->status === 'diterima')
                            <div
                                class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl space-y-1 print:bg-white print:border-gray-300">
                                <span class="font-bold text-emerald-900 block text-[11px] print:text-gray-800">Catatan
                                    Penerimaan Sales:</span>
                                <p class="text-emerald-800 text-xs print:text-gray-700">
                                    {{ $selectedDistribusi->catatan_penerimaan ?: 'Stok diterima lengkap tanpa catatan.' }}
                                </p>
                            </div>
                        @elseif (in_array($selectedDistribusi->status, ['ditolak', 'retur_selesai']))
                            <div
                                class="p-3 bg-rose-50 border border-rose-200 rounded-xl space-y-1 print:bg-white print:border-gray-300">
                                <span class="font-bold text-rose-900 block text-[11px] print:text-gray-800">Alasan
                                    Penolakan dari Sales:</span>
                                <p class="text-rose-800 text-xs font-semibold print:text-gray-700">
                                    {{ $selectedDistribusi->catatan_penolakan ?: 'Tidak mencantumkan alasan penolakan.' }}
                                </p>
                            </div>
                        @endif

                        <!-- Kolom Tanda Tangan Cetak Fisik -->
                        <div class="hidden print:grid grid-cols-2 gap-8 pt-8 text-center text-xs">
                            <div class="space-y-12">
                                <span>Petugas Gudang / Pengirim</span>
                                <div class="border-b border-gray-400 w-3/4 mx-auto"></div>
                                <span
                                    class="font-bold">({{ $selectedDistribusi->pengirim->name ?? 'Management' }})</span>
                            </div>
                            <div class="space-y-12">
                                <span>Driver / Sales Lapak</span>
                                <div class="border-b border-gray-400 w-3/4 mx-auto"></div>
                                <span
                                    class="font-bold">({{ $selectedDistribusi->penerima->name ?? 'Sales Lapak' }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer (Sembunyi Saat Cetak) -->
                    <div
                        class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center print:hidden">
                        <button onclick="window.print()"
                            class="px-3.5 py-2 bg-gray-700 hover:bg-gray-800 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 002-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                            Cetak Surat Jalan
                        </button>

                        <div class="flex gap-2">
                            @if ($selectedDistribusi->status === 'pending')
                                <button wire:click="batalkanPengiriman({{ $selectedDistribusi->id }})"
                                    wire:confirm="Yakin ingin membatalkan pengiriman stok ini?"
                                    class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition">
                                    Batal Kirim
                                </button>
                            @elseif ($selectedDistribusi->status === 'ditolak')
                                <button wire:click="prosesReturStokDitolak({{ $selectedDistribusi->id }})"
                                    wire:confirm="Kembalikan kuantitas stok yang ditolak Sales ini ke Gudang Pusat?"
                                    class="px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                                    Restok Retur ke Gudang
                                </button>
                            @endif

                            <button wire:click="tutupDetail"
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold text-xs rounded-xl transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
