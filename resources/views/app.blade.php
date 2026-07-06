<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $seo = $page['props']['seo'] ?? [];
        $seoTitle = $seo['title'] ?? config('app.name');
        $seoDescription = $seo['description'] ?? null;
        $seoCanonical = $seo['canonical'] ?? url()->current();
        $seoImage = $seo['image'] ?? null;
        $seoRobots = $seo['robots'] ?? config('seo.robots');
        $seoType = $seo['type'] ?? 'website';
        $twitterCard = $seo['twitterCard'] ?? config('seo.twitter_card');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <meta name="robots" content="{{ $seoRobots }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:type" content="{{ $seoType }}">
    <meta name="twitter:card" content="{{ $twitterCard }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    @if ($seoDescription)
        <meta name="description" content="{{ $seoDescription }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
    @endif
    @if ($seoCanonical)
        <link rel="canonical" href="{{ $seoCanonical }}">
        <meta property="og:url" content="{{ $seoCanonical }}">
    @endif
    @if ($seoImage)
        <meta property="og:image" content="{{ $seoImage }}">
        <meta name="twitter:image" content="{{ $seoImage }}">
    @endif
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <title inertia>{{ $seoTitle }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
