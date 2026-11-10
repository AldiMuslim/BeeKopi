<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Manajemen Produk & Master Resep</h1>
                    <p class="text-xs text-gray-500">Kelola katalog menu jualan, kalkulasi HPP & margin profit, stok
                        pusat, serta takaran resep baku per cup.</p>
                </div>
                <button wire:click="openModalProduk"
                    class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    + Tambah Produk Baru
                </button>
            </div>

            <!-- Flash Alert Notifications -->
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

            <!-- Section 1: Top Summary Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Total Katalog Menu</p>
                    <p class="text-xl font-black text-gray-800">{{ $totalMenu }} <span
                            class="text-xs font-normal text-gray-500">varian</span></p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Belum Memiliki Resep</p>
                    <p class="text-xl font-black {{ $menuTanpaResep > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $menuTanpaResep }} <span class="text-xs font-normal text-gray-500">menu</span>
                    </p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Total Stok Gudang Pusat</p>
                    <p class="text-xl font-black text-amber-600">{{ number_format($totalStokPusat, 0, ',', '.') }} <span
                            class="text-xs font-normal text-gray-500">cup</span></p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Valuasi Stok Siap Jual</p>
                    <p class="text-xl font-black text-emerald-600">Rp {{ number_format($estimasiValuasi, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <!-- Section 2: Filter & Search Bar -->
            <div
                class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 justify-between items-center">
                <div class="w-full sm:w-80">
                    <input type="text" wire:model.live.debounce.300ms="search"
                        placeholder="Cari nama varian produk..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto text-xs">
                    <select wire:model.live="filterKategori"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">
                        <option value="">-- Semua Kategori --</option>
                        <option value="Kopi">Kopi / Coffee</option>
                        <option value="Non-Kopi">Non-Kopi / Non-Coffee</option>
                        <option value="Snack">Snack</option>
                    </select>

                    <select wire:model.live="filterStatus"
                        class="px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">
                        <option value="">-- Semua Status --</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Section 3: Grid Katalog Produk & Resep -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse ($produks as $p)
                    @php
                        $hargaJual = $this->getHargaProduk($p);
                        $hppCup = $this->hitungHppProduk($p);
                        $marginRp = $hargaJual - $hppCup;
                        $marginPct = $hargaJual > 0 ? round(($marginRp / $hargaJual) * 100) : 0;
                        $statusAktif = ($p->status ?? 'aktif') === 'aktif';
                    @endphp
                    <div
                        class="bg-white p-5 rounded-2xl shadow-sm border {{ $statusAktif ? 'border-gray-100' : 'border-rose-200 bg-gray-50/60 opacity-80' }} flex flex-col justify-between space-y-4">
                        <div class="space-y-3">
                            <!-- Card Top: Kategori, Status, Nama & Harga -->
                            <div class="flex justify-between items-start gap-2">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 font-bold rounded text-[10px] uppercase tracking-wider">
                                            {{ $p->kategori ?? 'Umum' }}
                                        </span>
                                        @if ($statusAktif)
                                            <span
                                                class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]">Aktif</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold rounded text-[10px]">Nonaktif</span>
                                        @endif
                                    </div>
                                    <h3 class="font-bold text-gray-900 text-base mt-1.5">{{ $p->nama_produk }}</h3>
                                </div>
                                <div class="text-right">
                                    <span class="text-base font-black text-emerald-600 block">
                                        Rp {{ number_format($hargaJual, 0, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-semibold">Harga Jual</span>
                                </div>
                            </div>

                            <!-- Finacial Box: HPP, Margin & Stok Pusat -->
                            <div
                                class="grid grid-cols-3 gap-2 p-2.5 bg-gray-50 rounded-xl border border-gray-100 text-xs">
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Estimasi HPP</span>
                                    <span class="font-bold text-gray-800">Rp
                                        {{ number_format($hppCup, 0, ',', '.') }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-gray-400 block">Margin Profit</span>
                                    <span
                                        class="font-extrabold {{ $marginPct < 30 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $marginPct }}%
                                    </span>
                                    @if ($hppCup > 0 && $marginPct < 30)
                                        <span class="text-[9px] text-rose-600 font-bold block">Margin Rendah!</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 block">Stok Pusat</span>
                                    <span
                                        class="font-black {{ ($p->stok_pusat ?? 0) < 10 ? 'text-rose-600' : 'text-amber-600' }}">
                                        {{ number_format($p->stok_pusat ?? 0, 0, ',', '.') }} cup
                                    </span>
                                </div>
                            </div>

                            <!-- Komposisi Resep List -->
                            <div class="pt-2 border-t border-gray-100">
                                <div class="flex justify-between items-center mb-1.5">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Komposisi
                                        Resep (per 1 Cup):</span>
                                    <span class="text-[10px] font-semibold text-gray-400">{{ $p->reseps->count() }}
                                        bahan</span>
                                </div>
                                <ul class="space-y-1 text-xs text-gray-600 max-h-28 overflow-y-auto pr-1">
                                    @forelse($p->reseps as $rsp)
                                        <li class="flex justify-between items-center py-0.5 border-b border-gray-50">
                                            <span
                                                class="font-medium text-gray-700 truncate">{{ $rsp->bahanBaku->nama_bahan ?? '-' }}</span>
                                            <span
                                                class="font-bold text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded text-[11px]">
                                                {{ number_format($this->getTakaranResep($rsp), $this->getTakaranResep($rsp) == floor($this->getTakaranResep($rsp)) ? 0 : 2, ',', '.') }}
                                                {{ $this->getSatuanResep($rsp) }}
                                            </span>
                                        </li>
                                    @empty
                                        <li
                                            class="py-2 text-rose-500 italic text-[11px] font-semibold flex items-center gap-1">
                                            ⚠️ Belum ada resep diatur (Tidak bisa diracik).
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <!-- Card Footer Actions -->
                        <div
                            class="pt-3 border-t border-gray-100 flex flex-wrap gap-1.5 justify-between items-center text-xs">
                            <button wire:click="openModalResep({{ $p->id }})"
                                class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg transition shadow-sm text-[11px]">
                                🧪 Atur Resep
                            </button>

                            <div class="flex items-center gap-1">
                                <button wire:click="duplikatProduk({{ $p->id }})" title="Duplikat Menu & Resep"
                                    class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold rounded-lg transition text-[11px]">
                                    Copy
                                </button>
                                <button wire:click="editProduk({{ $p->id }})"
                                    class="px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition text-[11px]">
                                    Edit
                                </button>
                                <button wire:click="toggleStatus({{ $p->id }})"
                                    class="px-2.5 py-1.5 {{ $statusAktif ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }} font-bold rounded-lg transition text-[11px]">
                                    {{ $statusAktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div
                        class="col-span-full py-12 text-center text-gray-400 italic text-xs bg-white rounded-2xl border border-gray-100">
                        Belum ada katalog produk yang sesuai dengan pencarian.
                    </div>
                @endforelse
            </div>

            <!-- MODAL 1: TAMBAH / EDIT PRODUK -->
            @if ($showModalProduk)
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4 animate-in fade-in zoom-in duration-150 text-xs">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                            <h3 class="text-base font-bold text-gray-800">
                                {{ $produk_id ? 'Edit Menu Produk' : 'Tambah Menu Produk Baru' }}
                            </h3>
                            <button wire:click="$set('showModalProduk', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <form wire:submit.prevent="simpanProduk" class="space-y-3">
                            <div>
                                <label class="block font-bold text-gray-700 mb-1">Nama Varian Produk:</label>
                                <input type="text" wire:model="nama_produk"
                                    placeholder="Contoh: Es Kopi Susu Gula Aren"
                                    class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                @error('nama_produk')
                                    <span
                                        class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Kategori Menu:</label>
                                    <select wire:model="kategori"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                        <option value="Kopi">Kopi</option>
                                        <option value="Non-Kopi">Non-Kopi</option>
                                        <option value="Snack">Snack</option>
                                    </select>
                                    @error('kategori')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Status Katalog:</label>
                                    <select wire:model="status"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Harga Jual (Rp):</label>
                                    <input type="number" wire:model="harga" min="0"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold text-emerald-700">
                                    @error('harga')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block font-bold text-gray-700 mb-1">Stok Gudang Pusat (Cup):</label>
                                    <input type="number" wire:model="stok_pusat" min="0"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold text-amber-700">
                                    @error('stok_pusat')
                                        <span
                                            class="text-rose-500 text-[11px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            @if ($produk_id)
                                <p
                                    class="text-[10px] text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-100">
                                    ℹ️ Perubahan langsung pada <b>Stok Gudang Pusat</b> akan tercatat otomatis di Audit
                                    Log sebagai koreksi stok admin.
                                </p>
                            @endif

                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button type="button" wire:click="$set('showModalProduk', false)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-300 transition">Batal</button>
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-amber-600 text-white rounded-xl font-bold hover:bg-amber-700 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="simpanProduk">Simpan Produk</span>
                                    <span wire:loading wire:target="simpanProduk">Menyimpan...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- MODAL 2: PENGATURAN KOMPOSISI RESEP & KALKULATOR HPP -->
            @if ($showModalResep && $selectedProduk)
                @php
                    $modalHargaJual = $this->getHargaProduk($selectedProduk);
                    $modalHpp = $this->hitungHppProduk($selectedProduk);
                    $modalMarginRp = $modalHargaJual - $modalHpp;
                    $modalMarginPct = $modalHargaJual > 0 ? round(($modalMarginRp / $modalHargaJual) * 100) : 0;
                @endphp
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 space-y-4 max-h-[90vh] overflow-y-auto animate-in fade-in zoom-in duration-150 text-xs">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-800">Komposisi Resep:
                                    {{ $selectedProduk->nama_produk }}</h3>
                                <p class="text-[11px] text-gray-500">Atur takaran bahan baku mentah untuk pembuatan 1
                                    Cup produk ini.</p>
                            </div>
                            <button wire:click="$set('showModalResep', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        @if (session()->has('resep_message'))
                            <div
                                class="p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl font-semibold">
                                {{ session('resep_message') }}
                            </div>
                        @endif

                        <!-- Live Summary HPP & Profitabilitas -->
                        <div class="grid grid-cols-3 gap-3 p-3 bg-amber-50/70 border border-amber-200 rounded-xl">
                            <div>
                                <span class="text-[10px] text-gray-500 uppercase font-bold block">Harga Jual</span>
                                <span class="text-sm font-black text-gray-800">Rp
                                    {{ number_format($modalHargaJual, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-500 uppercase font-bold block">Total HPP /
                                    Cup</span>
                                <span class="text-sm font-black text-amber-700">Rp
                                    {{ number_format($modalHpp, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-500 uppercase font-bold block">Estimasi
                                    Margin</span>
                                <span
                                    class="text-sm font-black {{ $modalMarginRp < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    Rp {{ number_format($modalMarginRp, 0, ',', '.') }} ({{ $modalMarginPct }}%)
                                </span>
                            </div>
                        </div>

                        <!-- Form Tambah / Update Bahan Resep -->
                        <form wire:submit.prevent="simpanResep"
                            class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 space-y-3">
                            <label class="font-bold text-gray-700 uppercase tracking-wider text-[10px] block">+ Tambah
                                / Update Bahan Baku ke Resep</label>
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                <div class="sm:col-span-6">
                                    <select wire:model.live="bahan_baku_id"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                        <option value="">-- Pilih Bahan Baku --</option>
                                        @foreach ($allBahanBaku as $bb)
                                            <option value="{{ $bb->id }}">
                                                {{ $bb->nama_bahan }} (Stok:
                                                {{ number_format($bb->stok_sekarang, 2, ',', '.') }}
                                                {{ $bb->satuan }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bahan_baku_id')
                                        <span
                                            class="text-rose-500 text-[10px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <input type="number" step="0.01" wire:model="jumlah_takaran"
                                        placeholder="Takaran"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-bold">
                                    @error('jumlah_takaran')
                                        <span
                                            class="text-rose-500 text-[10px] block mt-0.5 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <select wire:model="satuan_resep"
                                        class="w-full px-3 py-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                                        <option value="gram">gram</option>
                                        <option value="ml">ml</option>
                                        <option value="pcs">pcs</option>
                                        <option value="set">set</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow-sm">
                                    + Simpan Takaran Bahan
                                </button>
                            </div>
                        </form>

                        <!-- Tabel Daftar Bahan dalam Resep -->
                        <div class="space-y-2">
                            <h4 class="font-bold text-gray-700 uppercase tracking-wider text-[10px]">Daftar Komposisi
                                Saat Ini</h4>
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr
                                            class="bg-gray-50 border-b border-gray-200 text-[10px] font-bold uppercase text-gray-500">
                                            <th class="py-2.5 px-3">Nama Bahan</th>
                                            <th class="py-2.5 px-3">Takaran / Cup</th>
                                            <th class="py-2.5 px-3">Beban Biaya (HPP)</th>
                                            <th class="py-2.5 px-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse ($selectedProduk->reseps as $rsp)
                                            @php
                                                $bahan = $rsp->bahanBaku;
                                                $takaran = $this->getTakaranResep($rsp);
                                                $satuan = $this->getSatuanResep($rsp);
                                                $hargaSatuanBahan =
                                                    (float) ($bahan->harga_satuan ??
                                                        ($bahan->harga_beli ?? ($bahan->harga ?? 0)));
                                                $konversiKeGudang = $bahan
                                                    ? $this->konversiNilaiStok($takaran, $satuan, $bahan->satuan)
                                                    : 0;
                                                $biayaItem = round($konversiKeGudang * $hargaSatuanBahan);
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-2.5 px-3 font-bold text-gray-800">
                                                    {{ $bahan->nama_bahan ?? '-' }}</td>
                                                <td class="py-2.5 px-3 font-extrabold text-amber-700">
                                                    {{ number_format($takaran, $takaran == floor($takaran) ? 0 : 2, ',', '.') }}
                                                    {{ $satuan }}
                                                </td>
                                                <td class="py-2.5 px-3 font-semibold text-gray-600">
                                                    Rp {{ number_format($biayaItem, 0, ',', '.') }}
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <button type="button"
                                                        wire:click="hapusResep({{ $rsp->id }})"
                                                        class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold rounded-lg text-[10px] transition">
                                                        Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-6 text-center text-gray-400 italic">
                                                    Belum ada bahan baku di dalam resep ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-gray-100">
                            <button type="button" wire:click="$set('showModalResep', false)"
                                class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-xl transition">
                                Selesai
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
