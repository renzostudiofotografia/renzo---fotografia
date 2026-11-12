<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $site->get('branding.description') }}">
    <title>@yield('title', $site->get('branding.title')) | {{ $site->get('branding.brand') }}</title>
    <link rel="icon" href="{{ $site->get('branding.favicon') ? asset($site->get('branding.favicon')) : asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $site->fontUrl() }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}?v={{ filemtime(public_path('css/styles.css')) }}">
    <style>
        :root {
            --fuente-titulo: '{{ $site->get('typography.heading_font') }}', serif;
            --fuente-cuerpo: '{{ $site->get('typography.body_font') }}', serif;
            --color-dorado: {{ $site->get('branding.gold') }};
            --color-fondo: {{ $site->get('branding.background') }};
            --color-texto: {{ $site->get('branding.text') }};
            --title-scale: {{ $site->get('typography.title_scale') / 100 }};
            font-size: {{ $site->get('typography.base_size') }}px;
        }
        .hero-subtitle { color: {{ $site->get('typography.hero_color') }}; font-size: clamp(1.35rem, 2.2vw, {{ $site->get('typography.hero_size') }}px); }
        @media (max-width: 768px) { .hero-subtitle { font-size: clamp(1.1rem, 4.3vw, 1.35rem); } }
    </style>
    @stack('head')
</head>
<body data-page="{{ Route::currentRouteName() }}">
    <a class="skip-link" href="#main-content">Saltar al contenido</a>
    <x-site.header />

    <main id="main-content">
        @yield('content')
    </main>

    <x-site.footer />
    <script src="{{ asset('js/main.js') }}?v={{ filemtime(public_path('js/main.js')) }}" defer></script>
    @stack('scripts')
    @if($site->isPreview())
        <script src="{{ asset('js/editor-preview.js') }}?v={{ filemtime(public_path('js/editor-preview.js')) }}" defer></script>
    @endif
</body>
</html>
