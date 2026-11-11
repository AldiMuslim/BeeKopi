<nav x-data="{
    open: false,
    dark: document.documentElement.classList.contains('dark'),
    toggleTheme() {
        if (window.toggleBeeKopiTheme) {
            window.toggleBeeKopiTheme();
        } else {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            localStorage.setItem('beekopi_theme', this.dark ? 'dark' : 'light');
        }
    }
}" @theme-changed.window="dark = $event.detail.dark"
    class="bg-white dark:bg-slate-800 border-b border-gray-100 dark:border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="font-extrabold text-lg text-amber-600 dark:text-amber-500 tracking-wider">
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
                        <div class="flex items-center">
                            <a href="{{ route('sales.penerimaan-stok') }}"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold transition {{ request()->routeIs('sales.penerimaan-stok') ? 'bg-amber-600 text-white' : 'text-gray-600 dark:text-slate-300 hover:bg-amber-50 hover:text-amber-700' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                                <span>Penerimaan Stok</span>
                            </a>
                        </div>
                    @endif

                    {{-- Menu Produksi / Gudang --}}
                    @if ($role === 'produksi')
                        <x-nav-link :href="route('produksi.restock-lapak')" :active="request()->routeIs('produksi.restock-lapak')">
                            Restock Lapak
                        </x-nav-link>
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

            <!-- Toggle Tema & Profile Dropdown (Desktop) -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-2.5">
                <!-- Tombol Toggle Dark / Light Mode -->
                <button type="button" @click="toggleTheme()"
                    :title="dark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
                    class="p-2 rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-gray-600 dark:text-amber-400 hover:bg-gray-100 dark:hover:bg-slate-700 transition focus:outline-none">
                    <!-- Ikon Matahari (Tampil saat Mode Gelap aktif) -->
                    <svg x-show="dark" style="display: none;" class="w-4 h-4 text-amber-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <!-- Ikon Bulan (Tampil saat Mode Terang aktif) -->
                    <svg x-show="!dark" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-slate-300 bg-white dark:bg-slate-800 hover:text-gray-700 dark:hover:text-white focus:outline-none transition">
                            <div class="text-right">
                                <span
                                    class="font-bold text-gray-800 dark:text-slate-100">{{ Auth::user()->name }}</span>
                                <span
                                    class="block text-[10px] text-amber-600 dark:text-amber-400 font-semibold uppercase">{{ Auth::user()->role?->label }}</span>
                            </div>
                            <div class="ms-1.5">
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

            <!-- Toggle Tema & Hamburger Menu (Mobile) -->
            <div class="-me-2 flex items-center gap-2 sm:hidden">
                <!-- Tombol Toggle Tema Mobile -->
                <button type="button" @click="toggleTheme()"
                    class="p-2 rounded-xl border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-gray-600 dark:text-amber-400 focus:outline-none transition">
                    <svg x-show="dark" style="display: none;" class="w-5 h-5 text-amber-400" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg x-show="!dark" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-slate-700 focus:outline-none transition">
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
    <div :class="{ 'block': open, 'hidden': !open }"
        class="hidden sm:hidden border-t border-gray-200 dark:border-slate-700">
        <div class="pt-2 pb-3 space-y-1">
            @php $role = Auth::user()->role?->nama; @endphp

            @if ($role === 'sales')
                <x-responsive-nav-link :href="route('sales.kasir')" :active="request()->routeIs('sales.kasir')">Kasir POS</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sales.laporan-penutupan')" :active="request()->routeIs('sales.laporan-penutupan')">Laporan Penutupan</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('sales.penerimaan-stok')" :active="request()->routeIs('sales.penerimaan-stok')">Penerimaan Stok</x-responsive-nav-link>
            @endif

            @if ($role === 'produksi')
                <x-responsive-nav-link :href="route('produksi.restock-lapak')" :active="request()->routeIs('produksi.restock-lapak')">Restock Lapak</x-responsive-nav-link>
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

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-slate-700">
            <div class="px-4">
                <div class="font-bold text-base text-gray-800 dark:text-slate-100">{{ Auth::user()->name }}</div>
                <div class="font-medium text-xs text-amber-600 dark:text-amber-400 uppercase">
                    {{ Auth::user()->role?->label }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profil Akun
                </x-responsive-nav-link>
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
