<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header Page -->
            <div>
                <h1 class="text-xl font-bold text-gray-800">Penerimaan Stok Lapak -
                    {{ Auth::user()->lapak->nama_lapak ?? 'Lapak' }}</h1>
                <p class="text-xs text-gray-500">Konfirmasi fisik produk kiriman dari Gudang Pusat untuk memperbarui stok
                    kasir.</p>
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

            <!-- Section 1: Pengiriman Dalam Transit (Butuh Konfirmasi Sales) -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 space-y-4 text-xs">
                <div class="flex justify-between items-center border-b border-gray-100 pb-2">
                    <div>
                        <h2 class="font-bold text-xs uppercase tracking-wider text-amber-600">Pengiriman Masuk (Menunggu
                            Konfirmasi)</h2>
                        <p class="text-[11px] text-gray-400">Cek kelengkapan fisik cup sebelum menekan tombol terima.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-xs">
                        {{ $pengirimanPending->count() }} Kiriman
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($pengirimanPending as $dst)
                        <div
                            class="p-4 bg-amber-50/50 border border-amber-200/80 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-black text-gray-900 text-sm">#{{ $dst->kode_distribusi }}</span>
                                    <span class="text-gray-400 text-[11px]">•
                                        {{ \Carbon\Carbon::parse($dst->tanggal)->format('d M Y') }}</span>
                                    <span
                                        class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold text-[10px] rounded animate-pulse">Dalam
                                        Transit</span>
                                </div>

                                <!-- Rincian Item Dikirim -->
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($dst->details as $det)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 bg-white text-gray-800 rounded-lg border border-amber-200 font-semibold text-xs">
                                            {{ $det->produk->nama_produk ?? 'Produk' }}: <b
                                                class="ml-1 text-amber-600">{{ $det->jumlah_cup }} cup</b>
                                        </span>
                                    @endforeach
                                </div>

                                @if ($dst->catatan)
                                    <p class="text-[11px] text-gray-600 italic"><b>Catatan Pengirim:</b>
                                        {{ $dst->catatan }}</p>
                                @endif
                                <p class="text-[10px] text-gray-400">Dikirim oleh:
                                    {{ $dst->pengirim->name ?? 'Gudang Pusat' }}</p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <button wire:click="tolakStok({{ $dst->id }})"
                                    wire:confirm="Yakin ingin menolak pengiriman stok ini?"
                                    class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-lg transition border border-rose-200">
                                    Tolak Kiriman
                                </button>
                                <button wire:click="terimaStok({{ $dst->id }})"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition shadow-sm">
                                    ✓ Terima & Tambah Stok
                                </button>
                            </div>
                        </div>
                    @empty
                        <div
                            class="py-6 text-center text-gray-400 italic bg-gray-50 rounded-xl border border-dashed border-gray-200">
                            Tidak ada pengiriman stok baru yang sedang dalam perjalanan.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Section 2: Riwayat Penerimaan Stok -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="font-bold text-xs uppercase text-gray-700">Histori Penerimaan Stok Lapak</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Kode / Tanggal</th>
                                <th class="py-3 px-4">Rincian Menu Diterima</th>
                                <th class="py-3 px-4">Pengirim</th>
                                <th class="py-3 px-4">Penerima</th>
                                <th class="py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($riwayatPenerimaan as $rw)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="font-bold text-gray-900 block">#{{ $rw->kode_distribusi }}</span>
                                        <span
                                            class="text-gray-400 text-[11px]">{{ \Carbon\Carbon::parse($rw->tanggal)->format('d M Y') }}</span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($rw->details as $det)
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 bg-gray-100 text-gray-800 rounded border border-gray-200 text-[11px]">
                                                    {{ $det->produk->nama_produk ?? 'Produk' }}:
                                                    <b>&nbsp;{{ $det->jumlah_cup }} cup</b>
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                                        {{ $rw->pengirim->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                                        {{ $rw->penerima->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if ($rw->status === 'diterima')
                                            <span
                                                class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Selesai
                                                Diterima</span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px]">Ditolak</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">Belum ada riwayat
                                        penerimaan stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $riwayatPenerimaan->links() }}
                </div>
            </div>

        </div>
    </div>
</div>
