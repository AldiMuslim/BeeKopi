<div>
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            <!-- Header & Tombol Ekspor / Pemeliharaan -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Audit Log & Pengawasan Aktivitas Sistem</h1>
                    <p class="text-xs text-gray-500">Pantau jejak operasi pengguna, perubahan resep, distribusi stok, dan
                        aksi sensitif secara real-time.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <button wire:click="exportCsv" wire:loading.attr="disabled"
                        class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm transition flex items-center gap-1.5">
                        <span>📊 Unduh CSV / Excel</span>
                    </button>

                    <button onclick="window.print()"
                        class="px-3.5 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-bold rounded-xl shadow-sm transition">
                        🖨️ Cetak
                    </button>

                    <button wire:click="bersihkanLogLama"
                        wire:confirm="Hapus seluruh rekaman Audit Log yang berusia lebih dari 90 hari agar database tetap ringan?"
                        class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl transition">
                        🧹 Prune Log (&gt; 90 Hari)
                    </button>
                </div>
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

            <!-- Section 1: Top Metric Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div wire:click="$set('periodeFilter', 'hari_ini')"
                    class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1 cursor-pointer hover:border-amber-300 transition">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Aktivitas Hari Ini</p>
                    <p class="text-xl font-black text-amber-600">{{ number_format($totalLogHariIni, 0, ',', '.') }}
                        <span class="text-xs font-normal text-gray-500">operasi</span></p>
                </div>

                <div wire:click="toggleLevelKritis"
                    class="bg-white p-4 rounded-xl border {{ $levelFilter === 'kritis' ? 'border-rose-400 ring-2 ring-rose-100' : 'border-gray-100' }} shadow-sm space-y-1 cursor-pointer hover:border-rose-300 transition">
                    <div class="flex justify-between items-center">
                        <p class="text-[11px] text-gray-400 font-semibold uppercase">Aksi Sensitif / Kritis</p>
                        @if ($levelFilter === 'kritis')
                            <span class="text-[9px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-bold">Filter
                                Aktif</span>
                        @endif
                    </div>
                    <p class="text-xl font-black text-rose-600">{{ number_format($aksiKritisCount, 0, ',', '.') }} <span
                            class="text-xs font-normal text-gray-500">kejadian</span></p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Staf Aktif Hari Ini</p>
                    <p class="text-xl font-black text-blue-600">{{ $operatorAktifHariIni }} <span
                            class="text-xs font-normal text-gray-500">pengguna</span></p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-1">
                    <p class="text-[11px] text-gray-400 font-semibold uppercase">Total Rekaman Audit</p>
                    <p class="text-xl font-black text-gray-800">{{ number_format($totalSemuaLog, 0, ',', '.') }} <span
                            class="text-xs font-normal text-gray-500">baris</span></p>
                </div>
            </div>

            <!-- Section 2: Filter & Search Control Bar -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 space-y-3">
                <div class="flex flex-col lg:flex-row gap-3 items-center justify-between">
                    <!-- Search Input -->
                    <div class="w-full lg:w-80">
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Cari aktivitas, nama barang, atau staf..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white">
                    </div>

                    <!-- Quick Periode Filter & Role Dropdown -->
                    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto text-xs">
                        <div class="flex items-center bg-gray-100 p-1 rounded-lg">
                            <button wire:click="$set('periodeFilter', 'semua')"
                                class="px-2.5 py-1 rounded-md font-bold transition {{ $periodeFilter === 'semua' && !$tanggalSpesifik ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                Semua
                            </button>
                            <button wire:click="$set('periodeFilter', 'hari_ini')"
                                class="px-2.5 py-1 rounded-md font-bold transition {{ $periodeFilter === 'hari_ini' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                Hari Ini
                            </button>
                            <button wire:click="$set('periodeFilter', '7_hari')"
                                class="px-2.5 py-1 rounded-md font-bold transition {{ $periodeFilter === '7_hari' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                7 Hari
                            </button>
                            <button wire:click="$set('periodeFilter', 'bulan_ini')"
                                class="px-2.5 py-1 rounded-md font-bold transition {{ $periodeFilter === 'bulan_ini' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                Bulan Ini
                            </button>
                        </div>

                        <input type="date" wire:model.live="tanggalSpesifik"
                            class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">

                        <select wire:model.live="roleFilter"
                            class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-amber-500 bg-white font-semibold text-gray-700">
                            <option value="">-- Semua Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->label ?? $r->name }}</option>
                            @endforeach
                        </select>

                        @if ($search || $roleFilter || $userFilter || $periodeFilter !== 'semua' || $tanggalSpesifik || $levelFilter)
                            <button wire:click="resetFilters"
                                class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold rounded-lg transition">
                                Reset Filter
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Indikator Filter Investigasi User / Kritis Aktif -->
                @if ($selectedUserObj || $levelFilter === 'kritis')
                    <div class="pt-2 border-t border-gray-100 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-gray-400 font-semibold text-[11px]">Mode Investigasi Aktif:</span>
                        @if ($selectedUserObj)
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-full font-bold text-[11px]">
                                👤 Pengguna: {{ $selectedUserObj->name }} ({{ $selectedUserObj->email }})
                                <button wire:click="$set('userFilter', '')"
                                    class="text-amber-900 hover:text-rose-600 font-black">&times;</button>
                            </span>
                        @endif
                        @if ($levelFilter === 'kritis')
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 text-rose-800 border border-rose-200 rounded-full font-bold text-[11px]">
                                ⚠️ Hanya Aksi Sensitif / Kritis
                                <button wire:click="$set('levelFilter', '')"
                                    class="text-rose-900 font-black">&times;</button>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Section 3: Tabel Audit Log -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr
                                class="bg-gray-50 border-b border-gray-100 text-[11px] font-semibold uppercase text-gray-500 whitespace-nowrap">
                                <th class="py-3 px-4">Waktu Kejadian</th>
                                <th class="py-3 px-4">Pelaku / Staf</th>
                                <th class="py-3 px-4">Hak Akses & Unit</th>
                                <th class="py-3 px-4">Modul & Klasifikasi</th>
                                <th class="py-3 px-4">Rincian Operasi / Jejak Audit</th>
                                <th class="py-3 px-4 text-center">Inspeksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse ($logs as $log)
                                @php
                                    $parsed = $this->analisaLog($log);
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <!-- Waktu -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span
                                            class="font-bold text-gray-800 block">{{ $log->created_at ? $log->created_at->format('d M Y, H:i:s') : '-' }}</span>
                                        <span
                                            class="text-[10px] text-gray-400">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
                                    </td>

                                    <!-- Pengguna (Click to Filter) -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        @if ($log->user)
                                            <button wire:click="filterByUser({{ $log->user_id }})"
                                                title="Klik untuk menyaring semua aktivitas {{ $log->user->name }}"
                                                class="text-left group">
                                                <span
                                                    class="font-bold text-gray-900 group-hover:text-amber-600 group-hover:underline block">
                                                    {{ $log->user->name }}
                                                </span>
                                                <span
                                                    class="text-[10px] text-gray-400 block">{{ $log->user->email }}</span>
                                            </button>
                                        @else
                                            <span class="font-bold text-gray-500 italic">Sistem Otomatis</span>
                                        @endif
                                    </td>

                                    <!-- Role & Lapak -->
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded font-bold text-[10px] inline-block">
                                            {{ $log->user->role->label ?? ($log->user->role->name ?? 'Sistem') }}
                                        </span>
                                        @if ($log->user?->lapak)
                                            <span class="text-[10px] text-amber-700 font-semibold block mt-0.5">
                                                📍 {{ $log->user->lapak->nama_lapak }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Modul & Klasifikasi Risiko -->
                                    <td class="py-3.5 px-4 whitespace-nowrap space-y-1">
                                        <span
                                            class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $parsed['modul_color'] }} block w-fit">
                                            {{ $parsed['modul'] }}
                                        </span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[9px] font-extrabold border {{ $parsed['badge_color'] }} block w-fit">
                                            {{ $parsed['level'] }}
                                        </span>
                                    </td>

                                    <!-- Rincian Aktivitas -->
                                    <td class="py-3.5 px-4 max-w-md">
                                        <p class="font-bold text-gray-900 text-xs">{{ $parsed['judul'] }}</p>
                                        <p class="text-gray-600 text-[11px] leading-relaxed mt-0.5">
                                            {{ $parsed['rincian'] }}</p>
                                    </td>

                                    <!-- Tombol Detail -->
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <button wire:click="openDetail({{ $log->id }})"
                                            class="px-2.5 py-1 bg-gray-100 hover:bg-amber-50 hover:text-amber-700 text-gray-700 font-bold rounded-lg text-[11px] border border-transparent hover:border-amber-200 transition">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-gray-400 italic text-xs">
                                        Belum ada rekaman audit log yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div class="p-4 border-t border-gray-100 bg-gray-50/30">
                    {{ $logs->links() }}
                </div>
            </div>

            <!-- MODAL INSPEKSI DETAIL LOG -->
            @if ($showModalDetail && $selectedLog)
                @php
                    $detailParsed = $this->analisaLog($selectedLog);
                @endphp
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div
                        class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4 animate-in fade-in zoom-in duration-150 text-xs">
                        <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-800">Inspeksi Rekaman Audit
                                    #{{ $selectedLog->id }}</h3>
                                <p class="text-[11px] text-gray-400">Bukti jejak aktivitas sistem Bee Kopi (Immutable
                                    Record)</p>
                            </div>
                            <button wire:click="$set('showModalDetail', false)"
                                class="text-gray-400 hover:text-gray-600 font-bold text-lg">&times;</button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Stempel Waktu
                                    Presisi</span>
                                <span
                                    class="font-bold text-gray-800">{{ $selectedLog->created_at ? $selectedLog->created_at->format('d F Y, H:i:s') : '-' }}</span>
                                <span
                                    class="text-[10px] text-gray-500 block">{{ $selectedLog->created_at ? $selectedLog->created_at->diffForHumans() : '' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Klasifikasi &
                                    Modul</span>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $detailParsed['modul_color'] }}">
                                        {{ $detailParsed['modul'] }}
                                    </span>
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-extrabold border {{ $detailParsed['badge_color'] }}">
                                        {{ $detailParsed['level'] }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Nama Pelaku /
                                    Operator</span>
                                <span
                                    class="font-bold text-gray-900">{{ $selectedLog->user->name ?? 'Sistem Otomatis' }}</span>
                                <span
                                    class="text-[10px] text-gray-500 block">{{ $selectedLog->user->email ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Hak Akses &
                                    Armada</span>
                                <span
                                    class="font-bold text-gray-800">{{ $selectedLog->user->role->label ?? ($selectedLog->user->role->name ?? 'Sistem') }}</span>
                                <span class="text-[10px] text-amber-700 font-semibold block">
                                    {{ $selectedLog->user?->lapak ? '📍 ' . $selectedLog->user->lapak->nama_lapak : 'Pusat / Non-Lapak' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4 bg-amber-50/50 border border-amber-200 rounded-xl space-y-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block">Judul
                                Operasi:</span>
                            <p class="font-extrabold text-gray-900 text-sm">{{ $detailParsed['judul'] }}</p>

                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-amber-700 block pt-2">Rincian
                                Lengkap Jejak Audit:</span>
                            <p class="text-gray-700 leading-relaxed font-medium">{{ $detailParsed['rincian'] }}</p>
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                            @if ($selectedLog->user_id)
                                <button
                                    wire:click="filterByUser({{ $selectedLog->user_id }}); $set('showModalDetail', false)"
                                    class="px-3 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 font-bold rounded-xl transition">
                                    🔍 Lacak Semua Aktivitas {{ $selectedLog->user->name }}
                                </button>
                            @else
                                <div></div>
                            @endif

                            <button type="button" wire:click="$set('showModalDetail', false)"
                                class="px-5 py-2 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded-xl transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
