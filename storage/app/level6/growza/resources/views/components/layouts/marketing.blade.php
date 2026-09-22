@props([
    'seoTitle' => null,
    'seoDescription' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo :title="$seoTitle" :description="$seoDescription" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Organization structured data — factual identity only, no invented
         claims (ratings, review counts, awards). See LEVEL 32. --}}
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('growza-marketing.company.name'),
            'slogan' => config('growza-marketing.company.tagline'),
            'url' => url('/'),
        ], JSON_UNESCAPED_SLASHES) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-white text-ink-800 font-sans min-h-screen flex flex-col">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:top-3 focus:left-3 focus:bg-ink-900 focus:text-white focus:px-4 focus:py-2 focus:rounded">
        Skip to content
    </a>

    <x-nav-bar />

    <main id="main" class="flex-1">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>
