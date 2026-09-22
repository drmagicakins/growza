@props(['title' => null, 'heading' => null, 'subheading' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Auth screens are never indexable, regardless of environment. --}}
    <x-seo :title="$title" :noindex="true" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink-50 text-ink-800 font-sans min-h-screen flex flex-col">
    <div class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <a href="{{ route('home') }}" class="font-display text-2xl font-semibold text-ink-900">Growza</a>
                @if ($heading)
                    <h1 class="mt-6 text-display-sm font-display text-ink-900">{{ $heading }}</h1>
                @endif
                @if ($subheading)
                    <p class="mt-2 text-sm text-ink-600">{{ $subheading }}</p>
                @endif
            </div>

            <div class="bg-white border border-ink-200 rounded-md shadow-resting p-6 sm:p-8">
                @if (session('status'))
                    <x-alert variant="success" class="mb-6">{{ session('status') }}</x-alert>
                @endif

                {{ $slot }}
            </div>

            @isset($footer)
                <p class="mt-6 text-center text-sm text-ink-600">{{ $footer }}</p>
            @endisset
        </div>
    </div>

    <footer class="py-6 text-center text-xs text-ink-500">
        <a href="{{ route('legal.terms') }}" class="hover:text-ink-700">Terms</a>
        <span class="mx-2">·</span>
        <a href="{{ route('legal.privacy') }}" class="hover:text-ink-700">Privacy</a>
    </footer>
</body>
</html>
