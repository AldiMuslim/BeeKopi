<div>
    {{-- To attain knowledge, add things every day; To attain wisdom, subtract things every day. --}}
</div>
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Audit Log & Riwayat Aktivitas</h1>
            <p class="text-sm text-gray-500">Pantau seluruh jejak aktivitas pengguna dan perubahan data krusial secara sistematis.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold uppercase text-gray-500">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Pengguna</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">Aktivitas / Operasi</th>
                            <th class="py-3 px-4">Rincian Modul</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs text-gray-700">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-gray-50/50">
                                <td class="py-3 px-4 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td class="py-3 px-4 font-bold text-gray-800">{{ $log->user->name ?? 'Sistem' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-semibold text-[11px]">
                                        {{ $log->user->role->label ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-amber-700">{{ $log->aktivitas }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $log->keterangan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 text-sm">Belum ada rekaman audit log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        </div>

    </div>
</div>