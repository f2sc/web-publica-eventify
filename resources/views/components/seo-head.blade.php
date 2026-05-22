@props([
    'title',
    'description',
    'canonical',
    'schema'     => null,
    'indexable'  => true,
    'ogImage'    => null,
    'ogType'     => 'website',
])

@php
    $siteName  = 'Eventify';
    $fullTitle = $title . ' | ' . $siteName;
    $image     = $ogImage ?? asset('images/og-default.png');
@endphp

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $canonical }}">
@if(! $indexable)
<meta name="robots" content="noindex, nofollow">
@endif

{{-- Open Graph --}}
<meta property="og:type"        content="{{ $ogType }}">
<meta property="og:title"       content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url"         content="{{ $canonical }}">
<meta property="og:image"       content="{{ $image }}">
<meta property="og:site_name"   content="{{ $siteName }}">
<meta property="og:locale"      content="es_ES">

{{-- Twitter Card --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image"       content="{{ $image }}">

{{-- Favicon --}}
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

{{-- Schema JSON-LD --}}
@if($schema)
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
@endif
