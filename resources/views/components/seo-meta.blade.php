@props([
    'title',
    'description',
    'image' => null,
    'type' => 'website',
    'structuredData' => null,
])

{{-- Canonical URL: current path without query string, so paginated or
     UTM-tagged variants don't fragment indexing (LEVEL 31). --}}
@php $canonical = url()->current(); @endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Robots. Non-production and any environment without APP_ALLOW_INDEXING
     refuses indexing outright — see config/app.php and SitemapController. --}}
@if (app()->environment('production') && config('app.allow_indexing'))
    <meta name="robots" content="index, follow">
@else
    <meta name="robots" content="noindex, nofollow">
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="Growza">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
@if ($image)<meta property="og:image" content="{{ $image }}">@endif

{{-- X / Twitter --}}
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
@if ($image)<meta name="twitter:image" content="{{ $image }}">@endif

@if ($structuredData)
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
