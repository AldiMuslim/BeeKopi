<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manajemen Produk & Resep</h1>
                <p class="text-sm text-gray-500">Kelola katalog menu jualan, harga, stok pusat, dan takaran resep baku.
                </p>
            </div>
            <button wire:click="openModalProduk"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-medium text-sm rounded-lg shadow-sm transition">
                + Tambah Produk Baru
            </button>
        </div>

        @if (session()->has('message'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <div
            class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row gap-3 justify-between items-center">
            <input type="text" wire:model.live="search" placeholder="Cari nama produk..."
                class="w-full sm:w-80 px-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            <select wire:model.live="filterKategori"
                class="w-full sm:w-48 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                <option value="">Semua Kategori</option>
                <option value="Coffee">Coffee</option>
                <option value="Non-Coffee">Non-Coffee</option>
                <option value="Snack">Snack</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($produks as $p)
                <div
                    class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex justify-between items-start">
                            <div>
                                <span
                                    class="px-2 py-0.5 bg-amber-50 text-amber-700 font-bold rounded text-[10px] uppercase tracking-wider">{{ $p->kategori ?? 'Umum' }}</span>
                                <h3 class="font-bold text-gray-800 text-base mt-1">{{ $p->nama_produk }}</h3>
                            </div>
                            <span class="text-base font-black text-emerald-600">Rp
                                {{ number_format($p->harga, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Stok Gudang Pusat: <strong
                                class="text-gray-800">{{ number_format($p->stok_pusat, 0, ',', '.') }} cup</strong></p>

                        <div class="mt-3 pt-3 border-t border-gray-50">
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Resep
                                Bahan Baku:</span>
                            <ul class="space-y-1 text-xs text-gray-600">
                                @forelse($p->reseps as $rsp)
                                    <li class="flex justify-between">
                                        <span>• {{ $rsp->bahanBaku->nama_bahan ?? '-' }}</span>
                                        <span class="font-semibold">{{ $rsp->jumlah_takaran }}
                                            {{ $rsp->bahanBaku->satuan ?? '' }}</span>
                                    </li>
                                @empty
                                    <li class="text-gray-400 italic">Belum ada resep diatur.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-50 flex gap-2 justify-end text-xs">
                        <button wire:click="openModalResep({{ $p->id }})"
                            class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded-md transition">
                            Atur Resep
                        </button>
                        <button wire:click="editProduk({{ $p->id }})"
                            class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-md transition">
                            Edit
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-8 text-center text-gray-400 text-sm">
                    Belum ada menu produk terdaftar.
                </div>
            @endforelse
        </div>

        @if ($showModalProduk)
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
                    <h3 class="text-lg font-bold text-gray-800">{{ $produk_id ? 'Edit Produk' : 'Tambah Produk Baru' }}
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Nama Produk</label>
                            <input type="text" wire:model="nama_produk"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Kategori</label>
                            <select wire:model="kategori"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                                <option value="Coffee">Coffee</option>
                                <option value="Non-Coffee">Non-Coffee</option>
                                <option value="Snack">Snack</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Harga Jual (Rp)</label>
                            <input type="number" wire:model="harga"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block font-medium text-gray-700 mb-1">Stok Gudang Pusat (Cup)</label>
                            <input type="number" wire:model="stok_pusat"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button wire:click="$set('showModalProduk', false)"
                            class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium">Batal</button>
                        <button wire:click="simpanProduk"
                            class="px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">Simpan</button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
