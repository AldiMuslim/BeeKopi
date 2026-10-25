<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="font-extrabold text-lg text-amber-600 tracking-wider">
                        BEE KOPI
                    </a>
                </div>

                <!-- Navigasi Utama Sesuai Peran (Desktop) -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @php $role = Auth::user()->role?->nama; @endphp

                    {{-- Menu Sales --}}
                    @if ($role === 'sales')
                        <x-nav-link :href="route('sales.kasir')" :active="request()->routeIs('sales.kasir')">
                            Kasir POS
                        </x-nav-link>

                        <!-- Tombol Laporan Penutupan -->
                        <div class="flex items-center">
                            <a href="{{ route('sales.laporan-penutupan') }}"
                                class="inline-flex items-center px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg shadow-sm transition">
                                Tutup Shift & Laporan
                            </a>
                        </div>

                        <!-- Link Menu Penerimaan Stok -->
                        <a href="{{ route('sales.penerimaan-stok') }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('sales.penerimaan-stok') ? 'bg-amber-600 text-white' : 'text-gray-600 hover:bg-amber-50 hover:text-amber-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                </path>
                            </svg>
                            <span>Penerimaan Stok</span>
                        </a>
                    @endif

                    {{-- Menu Produksi / Gudang --}}
                    @if ($role === 'produksi')
                        <x-nav-link :href="route('produksi.bahan-baku')" :active="request()->routeIs('produksi.bahan-baku')">
                            Stok Bahan Baku
                        </x-nav-link>
                        <x-nav-link :href="route('produksi.racik-kopi')" :active="request()->routeIs('produksi.racik-kopi')">
                            Racik Kopi Otomatis
                        </x-nav-link>
                    @endif

                    {{-- Menu Management --}}
                    @if ($role === 'management')
                        <x-nav-link :href="route('management.analisis-penjualan')" :active="request()->routeIs('management.analisis-penjualan')">
                            Analisis Trend
                        </x-nav-link>
                        <x-nav-link :href="route('management.laba-rugi')" :active="request()->routeIs('management.laba-rugi')">
                            Laporan Laba Rugi
                        </x-nav-link>
                        <x-nav-link :href="route('management.verifikasi')" :active="request()->routeIs('management.verifikasi')">
                            Verifikasi Laporan
                        </x-nav-link>
                        <x-nav-link :href="route('management.alokasi-stok')" :active="request()->routeIs('management.alokasi-stok')">
                            Alokasi Stok Lapak
                        </x-nav-link>
                    @endif

                    {{-- Menu Admin --}}
                    @if ($role === 'admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            Dashboard
                        </x-nav-link>
                        <x-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')">
                            Kelola User
                        </x-nav-link>
                        <x-nav-link :href="route('admin.lapak')" :active="request()->routeIs('admin.lapak')">
                            Kelola Lapak
                        </x-nav-link>
                        <x-nav-link :href="route('admin.produk')" :active="request()->routeIs('admin.produk')">
                            Produk & Resep
                        </x-nav-link>
                        <x-nav-link :href="route('admin.bahan-baku')" :active="request()->routeIs('admin.bahan-baku')">
                            Master Bahan Baku
                        </x-nav-link>
                        <x-nav-link :href="route('admin.audit-log')" :active="request()->routeIs('admin.audit-log')">
                            Audit Log
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Profile & Logout Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition">
                            <div>
                                <span class="font-bold text-gray-800">{{ Auth::user()->name }}</span>
                                <span
                                    class="block text-[10px] text-amber-600 font-semibold uppercase">{{ Auth::user()->role?->label }}</span>
                            </div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil Akun
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Keluar (Logout)
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger Menu Mobile -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu Mobile -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1">
            @php $role = Auth::user()->role?->nama; @endphp

            @if ($role === 'sales')
                <x-responsive-nav-link :href="route('sales.kasir')" :active="request()->routeIs('sales.kasir')">Kasir POS</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sales.laporan-penutupan')" :active="request()->routeIs('sales.laporan-penutupan')">Laporan Penutupan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sales.penerimaan-stok')" :active="request()->routeIs('sales.penerimaan-stok')">Penerimaan Stok</x-responsive-nav-link>
            @endif

            @if ($role === 'produksi')
                <x-responsive-nav-link :href="route('produksi.bahan-baku')" :active="request()->routeIs('produksi.bahan-baku')">Stok Bahan Baku</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('produksi.racik-kopi')" :active="request()->routeIs('produksi.racik-kopi')">Racik Kopi Otomatis</x-responsive-nav-link>
            @endif

            @if ($role === 'management')
                <x-responsive-nav-link :href="route('management.analisis-penjualan')" :active="request()->routeIs('management.analisis-penjualan')">Analisis Trend</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.laba-rugi')" :active="request()->routeIs('management.laba-rugi')">Laporan Laba Rugi</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.verifikasi')" :active="request()->routeIs('management.verifikasi')">Verifikasi Laporan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('management.alokasi-stok')" :active="request()->routeIs('management.alokasi-stok')">Alokasi Stok Lapak</x-responsive-nav-link>
            @endif

            @if ($role === 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users')" :active="request()->routeIs('admin.users')">Kelola User</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.lapak')" :active="request()->routeIs('admin.lapak')">Kelola Lapak</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.produk')" :active="request()->routeIs('admin.produk')">Produk & Resep</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.bahan-baku')" :active="request()->routeIs('admin.bahan-baku')">Master Bahan Baku</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.audit-log')" :active="request()->routeIs('admin.audit-log')">Audit Log</x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-bold text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-amber-600 uppercase">{{ Auth::user()->role?->label }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        Keluar (Logout)
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
