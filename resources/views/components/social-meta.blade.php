@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
])

@php
    $siteName = config('app.name', 'ChemTrack');
    
    // Check if parent views yielded/passed a title section
    $sectionTitle = View::hasSection('title') ? View::getSection('title') : null;
    $defaultTitle = $sectionTitle 
                        ? $siteName . ' — ' . $sectionTitle 
                        : $siteName . ' — ' . __('welcome.hero_subtitle');

    $metaTitle = $title ?? $defaultTitle;
    $metaDescription = $description ?? __('welcome.hero_desc');
    $metaImage = $image ?? asset('images/social-cover.png');
    $metaUrl = $url ?? request()->url();
    $metaLocale = app()->getLocale() === 'ar' ? 'ar_AR' : 'en_US';
@endphp

<!-- Primary Meta Tags -->
<title>{{ $metaTitle }}</title>
<meta name="title" content="{{ $metaTitle }}">
<meta name="description" content="{{ $metaDescription }}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $metaUrl }}">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:locale" content="{{ $metaLocale }}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $metaUrl }}">
<meta property="twitter:title" content="{{ $metaTitle }}">
<meta property="twitter:description" content="{{ $metaDescription }}">
<meta property="twitter:image" content="{{ $metaImage }}">
