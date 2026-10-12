@props(['meta'])

<title>{{ $meta['title'] }}</title>
@if ($meta['index'])
    <link rel="canonical" href="{{ $meta['canonical'] }}">
    @if ($meta['description'] !== '')
        <meta name="description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:locale" content="ro_RO">
    <meta property="og:site_name" content="{{ $meta['siteName'] }}">
    <meta property="og:type" content="{{ $meta['type'] }}">
    <meta property="og:title" content="{{ $meta['title'] }}">
    @if ($meta['description'] !== '')
        <meta property="og:description" content="{{ $meta['description'] }}">
    @endif
    <meta property="og:url" content="{{ $meta['canonical'] }}">
    @if ($meta['image'])
        <meta property="og:image" content="{{ $meta['image'] }}">
    @endif
    <meta name="twitter:card" content="{{ $meta['card'] }}">
    <meta name="twitter:title" content="{{ $meta['title'] }}">
    @if ($meta['description'] !== '')
        <meta name="twitter:description" content="{{ $meta['description'] }}">
    @endif
    @if ($meta['image'])
        <meta name="twitter:image" content="{{ $meta['image'] }}">
    @endif
@else
    <meta name="robots" content="noindex">
@endif
