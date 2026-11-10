<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header Page -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Verifikasi Laporan Shift Sales</h1>
                    <p class="text-xs text-gray-500">Persetujuan rekap omzet, setoran tunai, opname fisik cup, dan bukti QRIS dari armada lapak.</p>
                </div>
            </div>

            <!-- Flash Alert -->
            @if (session()->has('message'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl flex items-center justify-between shadow-sm">
                    <span>{{ session('message') }}</span>
                    <button type="button" class="font-bold text-emerald-800" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center justify-between shadow-sm">
                    <span>{{ session('error') }}</span>
                    <button type="button" class="font-bold text-rose-800" onclick="this.parentElement.remove()">&times;</button>
                </div>
            @endif

            <!-- Tab Status & Filter Bar -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50">
                    
                    <!-- Tab Filter Status + Badge Counter -->
                    <div class="flex flex-wrap gap-2 text-xs">
                        <button wire:click="filterTab('pending')"
                            class="px-3 py-2 rounded-lg font-bold transition flex items-center gap-2 {{ $filterStatus === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <span>Pending</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filterStatus === 'pending' ? 'bg-amber-800 text-amber-100' : 'bg-gray-200 text-gray-700' }}">
                                {{ $countPending }}
                            </span>
                        </button>
                        
                        <button wire:click="filterTab('approved')"
                            class="px-3 py-2 rounded-lg font-bold transition flex items-center gap-2 {{ $filterStatus === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <span>Disetujui</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filterStatus === 'approved' ? 'bg-emerald-800 text-emerald-100' : 'bg-gray-200 text-gray-700' }}">
                                {{ $countApproved }}
                            </span>
                        </button>
                        
                        <button wire:click="filterTab('rejected')"
                            class="px-3 py-2 rounded-lg font-bold transition flex items-center gap-2 {{ $filterStatus === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <span>Ditolak</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $filterStatus === 'rejected' ? 'bg-rose-800 text-rose-100' : 'bg-gray-200 text-gray-700' }}">
                                {{ $countRejected }}
                            </span>
                        </button>
                    </div>

                    <!-- Filter Lapak & Tanggal Shift -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <select wire:model.live="filterLapak"
                            class="px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">
                            <option value="">Semua Lapak</option>
                            @foreach ($lapaks as $lpk)
                                <option value="{{ $lpk->id }}">{{ $lpk->nama_lapak }}</option>
                            @endforeach
                        </select>

                        <input type="date" wire:model.live="tanggal_mulai" class="px-2.5 py-1.5 border rounded-lg bg-white font-semibold text-gray-700">
                        <span class="text-gray-400 font-bold">s/d</span>
                        <input type="date" wire:model.live="tanggal_selesai" class="px-2.5 py-1.5 border rounded-lg bg-white font-semibold text-gray-700">

                        @if ($filterLapak || $tanggal_mulai || $tanggal_selesai)
                            <button wire:click="resetFilter" class="px-2.5 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg transition">
                                Reset
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Tabel Daftar Laporan -->
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-gray-500 font-semibold uppercase whitespace-nowrap">
                                <th class="py-3 px-4">Tanggal Shift</th>
                                <th class="py-3 px-4">Lapak</th>
                                <th class="py-3 px-4">Sales Operator</th>
                                <th class="py-3 px-4">Terjual Sistem</th>
                                <th class="py-3 px-4">Omzet Tunai / QRIS</th>
                                <th class="py-3 px-4">Setoran Bersih</th>
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
                                        {{ number_format($lap->total_terjual_sistem, 0, ',', '.') }} Cup
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="text-[11px]">
                                            <span class="text-emerald-600 font-bold">Tunai: Rp {{ number_format($lap->omzet_tunai, 0, ',', '.') }}</span><br>
                                            <span class="text-blue-600 font-bold">QRIS: Rp {{ number_format($lap->omzet_qris, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-black text-emerald-700 text-sm whitespace-nowrap">
                                        Rp {{ number_format($lap->setoran_bersih, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if ($lap->status === 'approved')
                                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Disetujui</span>
                                        @elseif ($lap->status === 'rejected')
                                            <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px]">Ditolak</span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px] animate-pulse">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <button wire:click="lihatDetail({{ $lap->id }})"
                                            class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold rounded-lg transition border border-amber-200">
                                            Periksa Laporan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-400 italic">
                                        Tidak ada laporan shift dengan status <b class="uppercase">{{ $filterStatus }}</b>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $laporans->links() }}
                </div>
            </div>

            <!-- MODAL DETAIL & VERIFIKASI LAPORAN -->
            @if ($showModalDetail && $selectedLaporan)
                @php
                    // Kalkulasi Selisih Cup Unaccounted (Discrepancy)
                    $stokAwal = (int) $selectedLaporan->stok_awal;
                    $terjualSistem = (int) $selectedLaporan->total_terjual_sistem;
                    $sisaFisik = (int) $selectedLaporan->sisa_stok_fisik;
                    $stokRusak = (int) $selectedLaporan->stok_rusak_cup;

                    $stokSeharusnyaSisa = $stokAwal - $terjualSistem - $stokRusak;
                    $selisihUnaccounted = $sisaFisik - $stokSeharusnyaSisa;
                @endphp

                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in duration-150">

                        <!-- Modal Header -->
                        <div class="p-4 sm:p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-gray-800">Detail Laporan - {{ $selectedLaporan->lapak->nama_lapak ?? '-' }}</h3>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase 
                                        {{ $selectedLaporan->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($selectedLaporan->status === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $selectedLaporan->status }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-gray-500">Tanggal Shift: {{ \Carbon\Carbon::parse($selectedLaporan->tanggal)->format('d F Y') }} | Kasir: {{ $selectedLaporan->user->name ?? '-' }}</p>
                            </div>
                            <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-200 transition text-lg">&times;</button>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-4 sm:p-6 overflow-y-auto space-y-4 text-xs">

                            <!-- Omzet Tunai & QRIS -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 bg-emerald-50/70 rounded-xl border border-emerald-100">
                                    <span class="text-emerald-800 font-semibold block text-[11px]">Total Omzet Tunai</span>
                                    <span class="text-base font-black text-emerald-700">Rp {{ number_format($selectedLaporan->omzet_tunai, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-100">
                                    <span class="text-blue-800 font-semibold block text-[11px]">Total Omzet QRIS</span>
                                    <span class="text-base font-black text-blue-700">Rp {{ number_format($selectedLaporan->omzet_qris, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Opname Stok Fisik & Warning Indikator Selisih -->
                            <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                                <span class="font-bold text-gray-800 block text-xs">Rekonsiliasi Fisik Cup:</span>
                                
                                <div class="grid grid-cols-4 gap-2 text-center">
                                    <div class="bg-white p-2 rounded-lg border">
                                        <span class="text-gray-400 block text-[10px]">Terjual Sistem</span>
                                        <span class="font-bold text-amber-600">{{ $terjualSistem }} Cup</span>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border">
                                        <span class="text-gray-400 block text-[10px]">Sisa Fisik Lapak</span>
                                        <span class="font-bold text-emerald-600">{{ $sisaFisik }} Cup</span>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border">
                                        <span class="text-gray-400 block text-[10px]">Cup Rusak/Afkir</span>
                                        <span class="font-bold text-rose-600">{{ $stokRusak }} Cup</span>
                                    </div>
                                    <div class="bg-white p-2 rounded-lg border">
                                        <span class="text-gray-400 block text-[10px]">Stok Awal</span>
                                        <span class="font-bold text-gray-700">{{ $stokAwal }} Cup</span>
                                    </div>
                                </div>

                                <!-- WARNING BOX JIKA ADA SELISIH HILANG / UNACCOUNTED -->
                                @if ($selisihUnaccounted < 0)
                                    <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 flex items-center justify-between font-bold text-[11px] animate-pulse">
                                        <span>⚠️ Terdeteksi Selisih Cup Hilang (Unaccounted):</span>
                                        <span class="text-rose-700 font-black text-xs">{{ abs($selisihUnaccounted) }} Cup Tidak Cocok</span>
                                    </div>
                                @elseif ($selisihUnaccounted > 0)
                                    <div class="p-2.5 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 flex items-center justify-between font-bold text-[11px]">
                                        <span>ℹ️ Surplus Stok Fisik (Kelebihan):</span>
                                        <span class="text-blue-700 font-black text-xs">+{{ $selisihUnaccounted }} Cup</span>
                                    </div>
                                @else
                                    <div class="p-2 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-800 text-[11px] font-bold text-center">
                                        ✓ Stok Fisik Sinkron Tepat Sesuai Hitungan Sistem
                                    </div>
                                @endif

                                @if ($selectedLaporan->alasan_stok_rusak)
                                    <p class="text-[11px] text-gray-600 pt-1 border-t border-gray-200">
                                        <b>Keterangan Cup Rusak:</b> {{ $selectedLaporan->alasan_stok_rusak }}
                                    </p>
                                @endif
                            </div>

                            <!-- Pengeluaran Operasional Shift -->
                            <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-100 space-y-2">
                                <span class="font-bold text-gray-800 block text-xs">Pengeluaran Operasional Shift:</span>
                                <div class="flex justify-between text-gray-700">
                                    <span>Uang Makan Staff:</span>
                                    <span class="font-bold">Rp {{ number_format($selectedLaporan->uang_makan, 0, ',', '.') }}</span>
                                </div>

                                @php
                                    $items = json_decode($selectedLaporan->keterangan_pengeluaran, true) ?? [];
                                @endphp
                                @if (!empty($items))
                                    <div class="border border-gray-200 rounded-lg overflow-hidden mt-1">
                                        <table class="w-full text-left bg-white">
                                            <thead class="bg-gray-100 text-gray-600 font-semibold text-[10px]">
                                                <tr>
                                                    <th class="p-1.5">Pengeluaran Lain</th>
                                                    <th class="p-1.5 text-right">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach ($items as $it)
                                                    <tr>
                                                        <td class="p-1.5">{{ $it['nama'] ?? '-' }}</td>
                                                        <td class="p-1.5 text-right font-bold text-amber-600">Rp {{ number_format($it['nominal'] ?? 0, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            <!-- Bukti QRIS -->
                            @if ($selectedLaporan->foto_qris)
                                <div class="space-y-1">
                                    <span class="font-bold text-gray-800 block text-xs">Bukti Screenshot Transfer QRIS:</span>
                                    <a href="{{ asset('storage/' . $selectedLaporan->foto_qris) }}" target="_blank" class="inline-block">
                                        <img src="{{ asset('storage/' . $selectedLaporan->foto_qris) }}" class="h-32 rounded-xl border object-cover hover:opacity-90 transition shadow-sm">
                                    </a>
                                </div>
                            @endif

                            <!-- Target Uang Setoran Bersih -->
                            <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex justify-between items-center font-bold">
                                <span class="text-amber-900">Total Setoran Wajib Diterima:</span>
                                <span class="text-base text-amber-700 font-black">Rp {{ number_format($selectedLaporan->setoran_bersih, 0, ',', '.') }}</span>
                            </div>

                            <!-- SECTION VERIFIKASI AKSI ATAU READ-ONLY STATUS -->
                            @if ($selectedLaporan->status === 'pending')
                                <div class="space-y-2 pt-2 border-t border-gray-100">
                                    <label class="block font-bold text-gray-700">Catatan Management / Alasan Penolakan:</label>
                                    <textarea wire:model="catatan_management" rows="2"
                                        placeholder="Tuliskan catatan verifikasi atau alasan jika menolak laporan ini..."
                                        class="w-full p-2 border rounded-xl focus:ring-2 focus:ring-amber-500 bg-white text-xs"></textarea>
                                    @error('catatan_management')
                                        <span class="text-rose-500 text-[11px] block font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            @else
                                <!-- Mode Read-Only jika Laporan Sudah Disetujui/Ditolak -->
                                <div class="p-3 bg-gray-100 rounded-xl border border-gray-200 space-y-1">
                                    <span class="font-bold text-gray-700 block text-[11px]">Catatan Management Ter simpan:</span>
                                    <p class="text-gray-600 italic">{{ $selectedLaporan->catatan_management ?: 'Tidak ada catatan.' }}</p>
                                </div>
                            @endif

                        </div>

                        <!-- Modal Footer -->
                        <div class="p-3 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                            <button wire:click="closeModal" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold rounded-xl transition text-xs">
                                Tutup
                            </button>

                            @if ($selectedLaporan->status === 'pending')
                                <button wire:click="reject({{ $selectedLaporan->id }})"
                                    class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition text-xs shadow-sm">
                                    Tolak Laporan
                                </button>
                                <button wire:click="approve({{ $selectedLaporan->id }})"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition text-xs shadow-sm">
                                    Approve & Setujui Setoran
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            @endif

        </div>
    </div>
</div>