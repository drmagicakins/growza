@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
])

@php
    $siteName = config('growza-marketing.company.name', 'Growza');
    $fullTitle = $title ? $title . ' — ' . $siteName : $siteName . ' — ' . config('growza-marketing.company.tagline');
    $canonical = url()->current();
    $ogImage = $image ?: asset('images/growza-og.png');
@endphp

<title>{{ $fullTitle }}</title>

@if ($description)
    <meta name="description" content="{{ $description }}">
@endif

<link rel="canonical" href="{{ $canonical }}">

{{-- Private areas (dashboard/admin, LEVEL 5/16) pass :noindex="true".
     Non-production environments are never indexable regardless. --}}
@if ($noindex || ! config('app.allow_indexing'))
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow">
@endif

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $fullTitle }}">
@if ($description)
    <meta property="og:description" content="{{ $description }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
@if ($description)
    <meta name="twitter:description" content="{{ $description }}">
@endif
<meta name="twitter:image" content="{{ $ogImage }}">

{{ $slot ?? '' }}
