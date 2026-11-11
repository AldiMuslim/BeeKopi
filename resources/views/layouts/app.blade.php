<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Bee Kopi') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Script Anti-Flicker Dark Mode & Handler Global -->
    <script>
        (function() {
            const applyTheme = () => {
                const savedTheme = localStorage.getItem('beekopi_theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

                document.documentElement.classList.toggle('dark', isDark);

                if (window.Chart) {
                    window.Chart.defaults.color = isDark ? '#94a3b8' : '#64748b';
                    window.Chart.defaults.borderColor = isDark ? 'rgba(148, 163, 184, 0.12)' :
                        'rgba(226, 232, 240, 0.8)';
                }
            };

            applyTheme();

            window.toggleBeeKopiTheme = function() {
                const isCurrentlyDark = document.documentElement.classList.contains('dark');
                const nextTheme = isCurrentlyDark ? 'light' : 'dark';
                localStorage.setItem('beekopi_theme', nextTheme);
                applyTheme();
                window.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: {
                        dark: !isCurrentlyDark
                    }
                }));
            };

            document.addEventListener('livewire:navigated', applyTheme);
        })();
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-slate-900 text-gray-900 dark:text-slate-100">
    <div class="min-h-screen bg-gray-100 dark:bg-slate-900">
        <livewire:layout.navigation />

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white dark:bg-slate-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>
