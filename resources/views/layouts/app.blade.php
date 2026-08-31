<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Growza — Grow Smarter. Reach Further.')</title>
    {{-- Design system tokens (colors, type scale, spacing) land at LEVEL 1;
         this layout intentionally ships with zero styling opinions beyond
         a normal document so LEVEL 1 isn't fighting inherited CSS. --}}
</head>
<body>
    @yield('content')
</body>
</html>
