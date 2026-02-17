<!DOCTYPE html>
<html lang="ar" dir="rtl" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- جافاسكربت مضمَّن يكتشف وضع الظلام للنظام ويطبِّقه فوراً --}}
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

        {{-- تنسيق مضمَّن يحدد خلفية HTML وفقًا للسمات في app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'تطبيق الدردشة') }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=tajawal:400,500,600,700" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased" style="font-family: 'Tajawal', sans-serif;">
        <div class="app-shell">
            @inertia
        </div>

        <div aria-hidden="true" class="tiny-toolset">
            <svg viewBox="0 0 24 24" role="presentation" class="tiny-toolset__icon" style="--float-delay: 0s">
                <path d="M4.5 7.5L7 5l1.5 1.5L6 10z" />
                <path d="M12 4l2 2-5 5-2-2z" />
                <path d="M19 5l-5 5-1.5-1.5 5-5z" />
                <path d="M5 14l5 5 1-1-5-5z" />
            </svg>
            <svg viewBox="0 0 24 24" role="presentation" class="tiny-toolset__icon" style="--float-delay: 2s">
                <circle cx="12" cy="12" r="3.5" />
                <path d="M12 2v2.5M12 19.5V22M2 12h2.5M19.5 12H22M4.6 5.4l1.77 1.77M16.63 17.37l1.77 1.77M4.6 18.6l1.77-1.77M16.63 6.63l1.77-1.77" />
            </svg>
            <svg viewBox="0 0 24 24" role="presentation" class="tiny-toolset__icon" style="--float-delay: 4s">
                <path d="M8 2l8 8-4 4-8-8z" />
                <path d="M10 10l-6 6" />
                <path d="M20 14l-4 4" />
            </svg>
            <svg viewBox="0 0 24 24" role="presentation" class="tiny-toolset__icon" style="--float-delay: 6s">
                <polyline points="13 2 4 14 11 14 10 22 19 10 12 10 13 2" />
            </svg>
        </div>
    </body>
</html>
