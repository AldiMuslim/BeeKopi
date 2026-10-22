<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header Page -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Verifikasi Laporan Shift Sales</h1>
                    <p class="text-xs text-gray-500">Persetujuan rekap omzet, setoran tunai, dan bukti QRIS dari armada
                        lapak.</p>
                </div>
            </div>

            <!-- Flash Alert -->
            @if (session()->has('message'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg">
                    {{ session('message') }}
                </div>
            @endif

            <!-- Tab Filter Status & Tabel Laporan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <div class="flex gap-2">
                        <button wire:click="filterTab('pending')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $filterStatus === 'pending' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Pending
                        </button>
                        <button wire:click="filterTab('approved')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $filterStatus === 'approved' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Disetujui
                        </button>
                        <button wire:click="filterTab('rejected')"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $filterStatus === 'rejected' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Ditolak
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Tanggal Shift</th>
                                <th class="py-3 px-4">Lapak</th>
                                <th class="py-3 px-4">Sales Operator</th>
                                <th class="py-3 px-4">Total Cup (Sistem)</th>
                                <th class="py-3 px-4">Omzet Tunai / QRIS</th>
                                <th class="py-3 px-4">Setoran Tunai Bersih</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($laporans as $lap)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($lap->tanggal)->format('d M Y') }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ $lap->lapak->nama_lapak ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600 whitespace-nowrap">
                                        {{ $lap->user->name ?? 'Sales' }}
                                    </td>
                                    <td class="py-3 px-4 font-extrabold text-amber-700 whitespace-nowrap">
                                        {{ $lap->total_terjual_sistem }} Cup
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="text-[11px]">
                                            <span class="text-emerald-600 font-bold">Tunai: Rp
                                                {{ number_format($lap->omzet_tunai, 0, ',', '.') }}</span><br>
                                            <span class="text-blue-600 font-bold">QRIS: Rp
                                                {{ number_format($lap->omzet_qris, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-black text-emerald-700 text-sm whitespace-nowrap">
                                        Rp {{ number_format($lap->setoran_bersih, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if ($lap->status === 'approved')
                                            <span
                                                class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Disetujui</span>
                                        @elseif ($lap->status === 'rejected')
                                            <span
                                                class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px]">Ditolak</span>
                                        @else
                                            <span
                                                class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px] animate-pulse">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <button wire:click="lihatDetail({{ $lap->id }})"
                                            class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded-lg transition border border-amber-200">
                                            Periksa Laporan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-400">Tidak ada laporan dengan
                                        status {{ $filterStatus }}.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100">
                    {{ $laporans->links() }}
                </div>
            </div>

            <!-- Modal Detail & Verifikasi Laporan -->
            @if ($showModalDetail && $selectedLaporan)
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6 space-y-5 text-xs">

                        <!-- Header Modal -->
                        <div class="flex justify-between items-start border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-800">Detail Laporan Shift -
                                    {{ $selectedLaporan->lapak->nama_lapak ?? '-' }}</h3>
                                <p class="text-gray-500">Tanggal:
                                    {{ \Carbon\Carbon::parse($selectedLaporan->tanggal)->format('d F Y') }} | Kasir:
                                    {{ $selectedLaporan->user->name ?? '-' }}</p>
                            </div>
                            <button wire:click="$set('showModalDetail', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <!-- Grid 1: Rekap Omzet & Setoran -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-emerald-50/60 rounded-lg border border-emerald-100">
                                <span class="text-gray-500 block">Total Omzet Tunai</span>
                                <span class="text-sm font-extrabold text-emerald-700">Rp
                                    {{ number_format($selectedLaporan->omzet_tunai, 0, ',', '.') }}</span>
                            </div>
                            <div class="p-3 bg-blue-50/60 rounded-lg border border-blue-100">
                                <span class="text-gray-500 block">Total Omzet QRIS</span>
                                <span class="text-sm font-extrabold text-blue-700">Rp
                                    {{ number_format($selectedLaporan->omzet_qris, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Opname Stok Fisik vs Rusak -->
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 space-y-1">
                            <span class="font-bold text-gray-800 block">Rekonsiliasi Fisik Cup:</span>
                            <div class="grid grid-cols-3 gap-2 text-center pt-1">
                                <div class="bg-white p-2 rounded border">
                                    <span class="text-gray-400 block text-[10px]">Stok Terjual Sistem</span>
                                    <span
                                        class="font-bold text-amber-600 text-xs">{{ $selectedLaporan->total_terjual_sistem }}
                                        Cup</span>
                                </div>
                                <div class="bg-white p-2 rounded border">
                                    <span class="text-gray-400 block text-[10px]">Sisa Stok Fisik</span>
                                    <span
                                        class="font-bold text-emerald-600 text-xs">{{ $selectedLaporan->sisa_stok_fisik }}
                                        Cup</span>
                                </div>
                                <div class="bg-white p-2 rounded border">
                                    <span class="text-gray-400 block text-[10px]">Cup Rusak / Tumpah</span>
                                    <span
                                        class="font-bold text-rose-600 text-xs">{{ $selectedLaporan->stok_rusak_cup }}
                                        Cup</span>
                                </div>
                            </div>
                            @if ($selectedLaporan->alasan_stok_rusak)
                                <p class="text-[11px] text-gray-500 pt-1"><b>Ket. Cup Rusak:</b>
                                    {{ $selectedLaporan->alasan_stok_rusak }}</p>
                            @endif
                        </div>

                        <!-- Pengeluaran Operasional & Item List -->
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 space-y-2">
                            <span class="font-bold text-gray-800 block">Rincian Pengeluaran Operasional:</span>
                            <div class="flex justify-between text-gray-700">
                                <span>Uang Makan Staff (Standar):</span>
                                <span class="font-bold">Rp
                                    {{ number_format($selectedLaporan->uang_makan, 0, ',', '.') }}</span>
                            </div>

                            <!-- Tabel Itemized Pengeluaran Lain -->
                            @php
                                $items = json_decode($selectedLaporan->keterangan_pengeluaran, true) ?? [];
                            @endphp
                            @if (!empty($items))
                                <div class="border border-gray-200 rounded overflow-hidden mt-1">
                                    <table class="w-full text-left bg-white">
                                        <thead class="bg-gray-100 text-gray-600 font-semibold text-[10px]">
                                            <tr>
                                                <th class="p-1.5">Pengeluaran Lain</th>
                                                <th class="p-1.5">Nominal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($items as $it)
                                                <tr>
                                                    <td class="p-1.5">{{ $it['nama'] ?? '-' }}</td>
                                                    <td class="p-1.5 font-bold text-amber-600">Rp
                                                        {{ number_format($it['nominal'] ?? 0, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- Bukti Foto QRIS -->
                        @if ($selectedLaporan->foto_qris)
                            <div class="space-y-1">
                                <span class="font-bold text-gray-800 block">Bukti Screenshot QRIS:</span>
                                <a href="{{ asset('storage/' . $selectedLaporan->foto_qris) }}" target="_blank"
                                    class="inline-block">
                                    <img src="{{ asset('storage/' . $selectedLaporan->foto_qris) }}"
                                        class="h-32 rounded-lg border object-cover hover:opacity-90 transition">
                                </a>
                            </div>
                        @endif

                        <!-- Target Uang Setoran Bersih -->
                        <div
                            class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex justify-between items-center font-bold">
                            <span class="text-amber-900">Total Setoran Wajib Diterima:</span>
                            <span class="text-base text-amber-700 font-black">Rp
                                {{ number_format($selectedLaporan->setoran_bersih, 0, ',', '.') }}</span>
                        </div>

                        <!-- Input Catatan & Tombol Persetujuan -->
                        <div class="space-y-2 pt-2 border-t border-gray-100">
                            <label class="block font-bold text-gray-700">Catatan Management / Alasan Penolakan:</label>
                            <textarea wire:model="catatan_management" rows="2"
                                placeholder="Tuliskan feedback atau alasan jika menolak laporan ini..."
                                class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white"></textarea>
                            @error('catatan_management')
                                <span class="text-rose-500 text-[11px] block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button wire:click="reject({{ $selectedLaporan->id }})"
                                class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg transition">
                                Tolak Laporan
                            </button>
                            <button wire:click="approve({{ $selectedLaporan->id }})"
                                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition">
                                Approve & Setujui Setoran
                            </button>
                        </div>

                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
