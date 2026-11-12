<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Tu estudio') · Studio</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @if(($section ?? '') === 'typography')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?{{ implode('&', array_map(fn ($font) => 'family='.$font, array_values(config('editor.fonts')))) }}&display=swap">
    @endif
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
    @stack('styles')
    @stack('scripts')
</head>
<body class="admin-app @yield('body-class')">
    <aside class="app-sidebar" aria-label="Panel del estudio">
        <a class="app-brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark">R.</span><span class="brand-label">STUDIO<small>El espacio de tu marca</small></span></a>
        <div class="nav-caption">TU ESPACIO</div>
        <nav class="app-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'selected' : '' }}" title="Resumen"><span class="nav-icon">◈</span><span class="nav-label">Resumen</span></a>
            <a href="{{ route('admin.edit', 'home_hero') }}" class="{{ request()->routeIs('admin.edit') && !in_array($section ?? '', ['typography','booking']) ? 'selected' : '' }}" title="Editor del sitio"><span class="nav-icon">▤</span><span class="nav-label">Editor del sitio</span></a>
            <a href="{{ route('admin.landings.index') }}" class="{{ request()->routeIs('admin.landings.*') ? 'selected' : '' }}" title="Landing pages"><span class="nav-icon">↗</span><span class="nav-label">Landing pages</span></a>
            <a href="{{ route('admin.media') }}" class="{{ request()->routeIs('admin.media') ? 'selected' : '' }}" title="Biblioteca"><span class="nav-icon">▧</span><span class="nav-label">Biblioteca</span></a>
            <a href="{{ route('admin.edit', 'typography') }}" class="{{ ($section ?? '') === 'typography' ? 'selected' : '' }}" title="Tipografías"><span class="nav-icon type-icon">Aa</span><span class="nav-label">Tipografías</span></a>
            <a href="{{ route('admin.edit', 'booking') }}" class="{{ ($section ?? '') === 'booking' ? 'selected' : '' }}" title="Citas"><span class="nav-icon">▦</span><span class="nav-label">Citas</span></a>
            <a href="{{ route('admin.integrations') }}" class="{{ request()->routeIs('admin.integrations') ? 'selected' : '' }}" title="Conexiones"><span class="nav-icon">⌘</span><span class="nav-label">Conexiones</span></a>
        </nav>
        <div class="sidebar-bottom"><a href="{{ route('home') }}" target="_blank" rel="noopener">↗ <span class="nav-label">Visitar mi sitio</span></a><div class="owner"><span class="owner-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="nav-label">{{ auth()->user()->name }}<small>Propietario</small></span></div></div>
    </aside>
    <div class="app-main">
        <header class="app-topbar">
            <div class="topbar-heading"><span class="eyebrow">RENZO · STUDIO</span><strong>@yield('title', 'Tu estudio')</strong></div>
            <div class="topbar-actions">
                @hasSection('publication-status')@yield('publication-status')@else<span class="draft-badge">{{ $pendingCount ? $pendingCount.' borradores' : 'Todo publicado' }}</span>@endif
                @hasSection('save-action')@yield('save-action')@endif
                @hasSection('publish-action')@yield('publish-action')@else<form action="{{ route('admin.publish') }}" method="POST">@csrf<button class="button primary" type="submit" @disabled(!$pendingCount)>Publicar cambios <span>↗</span></button></form>@endif
                <form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="icon-button" type="submit" title="Cerrar sesión" aria-label="Cerrar sesión">↪</button></form>
            </div>
        </header>
        @if(session('status'))<div class="notice success" role="status">✓ {{ session('status') }}</div>@endif
        @if($errors->any())<div class="notice error" role="alert"><strong>Revisa los cambios antes de continuar.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </div>
</body>
</html>
