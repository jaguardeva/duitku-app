<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
        <meta name="color-scheme" content="light dark">

        <!-- Primary SEO Meta Tags -->
        <meta name="description" content="CatatDuit adalah aplikasi pencatatan keuangan pribadi modern untuk mengelola pengeluaran, memantau pemasukan, alokasi dompet multi-rekening, dan perencanaan anggaran bulanan dengan presisi ala m-banking.">
        <meta name="keywords" content="catatduit, aplikasi keuangan pribadi, pencatatan keuangan, pelacak pengeluaran, kelola anggaran, buku kas pribadi, personal finance indonesia, manajemen dompet, financial tracker">
        <meta name="author" content="CatatDuit">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

        <!-- Theme Color & Mobile App Meta Tags -->
        <meta name="theme-color" media="(prefers-color-scheme: light)" content="#003A70">
        <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#061224">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="CatatDuit">
        <meta name="application-name" content="CatatDuit">
        <meta name="format-detection" content="telephone=no">

        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Favicons & Web Manifest -->
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.png" type="image/png">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="manifest" href="/site.webmanifest">

        <!-- Open Graph Meta Tags (Facebook, WhatsApp, LinkedIn, Discord) -->
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="CatatDuit">
        <meta property="og:title" content="CatatDuit - Aplikasi Pencatatan Keuangan & Manajemen Anggaran Modern">
        <meta property="og:description" content="Kelola pengeluaran, pantau pemasukan, alokasi dompet multi-rekening, dan perencanaan anggaran bulanan dengan presisi ala m-banking.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('og-image.png') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="CatatDuit - Solusi Pencatatan Keuangan Pribadi & Anggaran Modern">
        <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'id_ID' }}">
        <meta property="og:locale:alternate" content="{{ app()->getLocale() === 'en' ? 'id_ID' : 'en_US' }}">

        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="CatatDuit - Aplikasi Pencatatan Keuangan & Manajemen Anggaran Modern">
        <meta name="twitter:description" content="Kelola pengeluaran, pantau pemasukan, alokasi dompet multi-rekening, dan perencanaan anggaran bulanan dengan presisi ala m-banking.">
        <meta name="twitter:image" content="{{ asset('og-image.png') }}">

        <!-- Structured Data: JSON-LD (Schema.org) -->
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/') . '#organization',
                    'name' => 'CatatDuit',
                    'url' => url('/'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('favicon.png'),
                    ],
                ],
                [
                    '@type' => 'WebApplication',
                    '@id' => url('/') . '#webapp',
                    'name' => 'CatatDuit',
                    'url' => url('/'),
                    'applicationCategory' => 'FinanceApplication',
                    'operatingSystem' => 'All',
                    'description' => 'Aplikasi pencatatan keuangan pribadi modern untuk mengelola pengeluaran, pemasukan, dompet multi-rekening, dan anggaran bulanan.',
                    'offers' => [
                        '@type' => 'Offer',
                        'price' => '0',
                        'priceCurrency' => 'IDR',
                    ],
                    'featureList' => [
                        'Pencatatan Pemasukan dan Pengeluaran',
                        'Multi-Dompet dan Kantung Keuangan',
                        'Batas dan Peringatan Anggaran Bulanan',
                        'Laporan Analisis Arus Kas Interaktif',
                        'Fitur Privasi Sembunyikan Saldo Ala M-Banking',
                        'Manajemen Multi-Sesi Perangkat',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
        </script>

        <script>
            (function() {
                try {
                    const stored = localStorage.getItem('catatduit_theme');
                    const userPref = @json(auth()->user()?->theme_preference);
                    const theme = stored || userPref;
                    if (theme === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {}
            })();
        </script>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>{{ config('app.name', 'CatatDuit') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased min-h-screen bg-bg text-text-main selection:bg-accent selection:text-[#003A70]">
        <x-inertia::app />
    </body>
</html>
