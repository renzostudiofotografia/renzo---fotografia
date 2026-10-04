<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Fotografía editorial y documental de bodas en Arequipa, Perú y destinos internacionales.">
    <title>@yield('title', 'Fotografía de Bodas') | {{ config('site.brand') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @stack('head')
</head>
<body data-page="{{ Route::currentRouteName() }}">
    <a class="skip-link" href="#main-content">Saltar al contenido</a>
    <x-site.header />

    <main id="main-content">
        @yield('content')
    </main>

    <x-site.footer />
    <script src="{{ asset('js/main.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
