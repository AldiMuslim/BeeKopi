<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header Page -->
            <div>
                <h1 class="text-xl font-bold text-gray-800">Alokasi & Distribusi Stok</h1>
                <p class="text-xs text-gray-500">Kirim produk siap jual dari Gudang Pusat ke armada lapak cabang.</p>
            </div>

            <!-- Flash Alert -->
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

            <!-- Section 1: Form Alokasi Pengiriman Stok Ke Lapak -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4 text-xs">
                <div class="border-b border-gray-100 pb-2">
                    <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Form Alokasi Pengiriman Stok
                    </h2>
                    <p class="text-[11px] text-gray-400">Pilih armada lapak tujuan dan isi jumlah cup yang akan dikirim
                        pada daftar menu di bawah.</p>
                </div>

                <form wire:submit.prevent="kirimStok" class="space-y-4">
                    <!-- Pilih Lapak Tujuan -->
                    <div class="max-w-md">
                        <label class="block font-bold text-gray-700 mb-1">Pilih Lapak Cabang Tujuan:</label>
                        <select wire:model="lapak_id"
                            class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white font-semibold">
                            <option value="">-- Pilih Armada Lapak --</option>
                            @foreach ($lapaks as $l)
                                <option value="{{ $l->id }}">{{ $l->nama_lapak }}</option>
                            @endforeach
                        </select>
                        @error('lapak_id')
                            <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Direct List Semua Menu Kopi Aktif -->
                    <div class="space-y-2">
                        <label class="block font-bold text-gray-700">Daftar Menu Siap Jual (Gudang Pusat):</label>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @forelse ($produks as $p)
                                <div
                                    class="p-3 bg-gray-50/80 rounded-xl border border-gray-100 flex items-center justify-between gap-3">
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
                                        <input type="number" min="0" wire:model="jumlah_cup.{{ $p->id }}"
                                            placeholder="0"
                                            class="w-full px-2.5 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white font-black text-gray-800 text-center">
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full py-4 text-center text-gray-400 italic">Belum ada menu produk
                                    aktif.</div>
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

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                            Proses & Kirim Stok Ke Lapak
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Tabel Histori Pengiriman Stok Pusat -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="font-bold text-xs uppercase text-gray-700">Histori Pengiriman Stok Pusat</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Kode / Tanggal</th>
                                <th class="py-3 px-4">Lapak Tujuan</th>
                                <th class="py-3 px-4">Rincian Menu Dikirim</th>
                                <th class="py-3 px-4">Pengirim (Management)</th>
                                <th class="py-3 px-4">Penerima (Sales)</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($distribusis as $dst)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="font-bold text-gray-900 block">#{{ $dst->kode_distribusi }}</span>
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
                                        {{ $dst->pengirim->name ?? 'Admin' }}
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
                                                class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px]">Ditolak</span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px] animate-pulse">Dalam
                                                Transit</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400">Belum ada riwayat alokasi
                                        stok.</td>
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
    </div>
</div>
