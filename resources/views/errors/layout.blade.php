<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Deteksi preferensi dark mode sistem, sama seperti app.blade.php --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>


        <title>@yield('code') | {{ config('app.name', 'Sistem E-Planning') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen w-full flex items-center justify-center p-6">
            <div class="w-full max-w-md rounded-xl border border-border bg-card shadow-lg p-10 flex flex-col items-center text-center gap-5">

                <!-- Icon error -->
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-100 dark:bg-red-950">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 dark:text-red-400">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                        <path d="M12 9v4"/>
                        <path d="M12 17h.01"/>
                    </svg>
                </div>

                <div class="space-y-2 px-2">
                    <p class="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                        Error @yield('code')
                    </p>
                    <h1 class="text-xl font-semibold text-foreground">
                        @yield('title')
                    </h1>
                    <p class="text-sm text-muted-foreground leading-relaxed">
                        @yield('message')
                    </p>
                </div>

                <a
                    href="@yield('action_url', '/dashboard')"
                    class="mt-1 px-5 py-2.5 rounded-lg text-sm font-medium bg-blue-600 text-white hover:bg-blue-700 shadow transition"
                >
                    @yield('action_label', 'Kembali ke Dashboard')
                </a>

            </div>
        </div>
    </body>
</html>
