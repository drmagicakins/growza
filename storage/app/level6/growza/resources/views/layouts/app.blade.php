<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Growza — Grow Smarter. Reach Further.')</title>

    {{-- Robots: dashboard/admin layouts (added at LEVEL 5/16) will extend
         a different base or override this — marketing pages are indexable
         by default, private areas are not. See LEVEL 31 / config/app.php
         'allow_indexing'. --}}
    @if (! config('app.allow_indexing') && ! app()->environment('production'))
        <meta name="robots" content="noindex, nofollow">
    @endif

    {{-- Fraunces (display serif) + Public Sans (UI sans) — the specific
         pairing documented in DESIGN_SYSTEM.md as the anti-generic-AI-look
         typography decision. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 text-ink-800 font-sans">
    @yield('content')
</body>
</html>
