@extends('admin.layout')
@php
    $showTrash = $showTrash ?? false;
    $activeCount = $activeCount ?? $landings->total();
    $trashCount = $trashCount ?? 0;
@endphp
@section('title', 'Landing pages')
@section('publication-status')<span class="draft-badge">{{ $showTrash ? 'Papelera' : 'Campañas' }}</span>@endsection
@section('publish-action')
    @if($showTrash)<a class="button secondary" href="{{ route('admin.landings.index') }}">← Ver landing pages</a>@else<a class="button primary" href="#nueva-landing">＋ Crear landing</a>@endif
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/landing-editor.css') }}?v={{ filemtime(public_path('css/landing-editor.css')) }}">@endpush
@section('content')
<main class="page-padding landing-index">
    <section class="landing-intro">
        <div><span class="eyebrow">CAMPAÑAS QUE CONECTAN</span><h1>De una primera mirada<br>a una nueva historia.</h1><p>Crea una página para cada campaña. Dale tu estilo, conecta tu Meta Pixel y recibe las consultas en un solo lugar.</p></div>
        <div class="landing-intro-note"><span>01 · DISEÑA</span><span>02 · PUBLICA</span><span>03 · CONECTA</span><small>Edición en vivo · Adaptable a móviles · Siempre en soles</small></div>
    </section>
    <nav class="landing-list-tabs" aria-label="Estado de las landing pages"><a href="{{ route('admin.landings.index') }}" class="{{ !$showTrash ? 'active' : '' }}" @if(!$showTrash) aria-current="page" @endif>Landing pages <span>{{ $activeCount }}</span></a><a href="{{ route('admin.landings.index', ['trash' => 1]) }}" class="{{ $showTrash ? 'active' : '' }}" @if($showTrash) aria-current="page" @endif>Papelera <span>{{ $trashCount }}</span></a></nav>
    <div class="section-heading"><div><span class="eyebrow">{{ $showTrash ? 'PUEDES RECUPERARLAS' : 'TU COLECCIÓN' }}</span><h2>{{ $showTrash ? 'Papelera' : 'Landing pages' }} <span class="count-badge">{{ $landings->total() }}</span></h2></div><span class="muted">{{ $showTrash ? 'Los contactos se conservan. Al restaurar, la landing vuelve como borrador privado.' : 'Cada campaña tiene su propia publicación.' }}</span></div>
    @if($landings->isEmpty())
        @if($showTrash)<div class="empty-card"><span>↺</span><h3>La papelera está vacía.</h3><p>Las landing pages que elimines aparecerán aquí para que puedas recuperarlas.</p><a class="button secondary" href="{{ route('admin.landings.index') }}">Volver a mis landing pages →</a></div>@else<div class="empty-card"><span>↗</span><h3>Tu próxima campaña empieza aquí.</h3><p>Crea tu primera landing, personalízala y comparte su enlace en tus anuncios.</p><a class="button secondary" href="#nueva-landing">Crear mi primera landing →</a></div>@endif
    @else
        <div class="landing-card-grid">
        @foreach($landings as $landing)
            @php
                $hero = collect($landing->draft['sections'] ?? [])->firstWhere('type', 'hero');
                $cover = $hero['image'] ?? 'assets/images/hero/hero-01.webp';
            @endphp
            <article class="landing-card">
                @if($showTrash)<div class="landing-card-cover">@else<a class="landing-card-cover" href="{{ route('admin.landings.edit', $landing) }}">@endif<img src="{{ asset($cover ?: 'assets/images/hero/hero-01.webp') }}" alt="" loading="lazy"><span class="landing-status {{ !$showTrash && $landing->published ? 'is-live' : '' }}">{{ $showTrash ? 'En papelera' : ($landing->published ? 'Publicada' : 'Borrador privado') }}</span><span class="landing-cover-title">{{ $hero['title'] ?? $landing->name }}</span>@if($showTrash)</div>@else</a>@endif
                <div class="landing-card-content"><h3>{{ $landing->name }}</h3><p class="landing-slug">/lp/{{ $landing->slug }}</p><div class="landing-card-meta">@if($showTrash)<span><strong>{{ $landing->leads_count }}</strong> {{ $landing->leads_count === 1 ? 'consulta conservada' : 'consultas conservadas' }}</span>@else<a href="{{ route('admin.landings.leads', $landing) }}"><strong>{{ $landing->leads_count }}</strong> {{ $landing->leads_count === 1 ? 'consulta recibida' : 'consultas recibidas' }} →</a>@endif<span>{{ ($showTrash ? $landing->deleted_at : $landing->updated_at)->format('d/m/Y') }}</span></div><div class="landing-card-actions">@if($showTrash)<form action="{{ route('admin.landings.restore', $landing) }}" method="POST">@csrf<input type="hidden" name="revision" value="{{ $landing->revision }}"><button class="button secondary" type="submit">Restaurar como borrador ↺</button></form>@else<a class="button primary" href="{{ route('admin.landings.edit', $landing) }}">Editar landing →</a>@if($landing->published)<a class="text-link" href="{{ route('landing.show', $landing->slug) }}" target="_blank" rel="noopener">Ver publicada ↗</a>@else<a class="text-link" href="{{ route('admin.landings.preview', $landing) }}" target="_blank" rel="noopener">Vista previa ↗</a>@endif @endif</div>@unless($showTrash)<div class="landing-card-delete"><button class="text-button danger" type="button" data-delete-landing data-delete-url="{{ route('admin.landings.destroy', $landing) }}" data-delete-name="{{ $landing->name }}" data-delete-revision="{{ $landing->revision }}">Eliminar landing</button></div>@endunless</div>
            </article>
        @endforeach
        </div>
        @if($landings->hasPages())<nav class="landing-pagination" aria-label="Páginas de campañas">@if($landings->onFirstPage())<span class="muted">← Anterior</span>@else<a class="button secondary" href="{{ $landings->previousPageUrl() }}" rel="prev">← Anterior</a>@endif<span>Página {{ $landings->currentPage() }} de {{ $landings->lastPage() }}</span>@if($landings->hasMorePages())<a class="button secondary" href="{{ $landings->nextPageUrl() }}" rel="next">Siguiente →</a>@else<span class="muted">Siguiente →</span>@endif</nav>@endif
    @endif
    @unless($showTrash)
    <section class="landing-create" id="nueva-landing" aria-labelledby="create-title"><div><span class="eyebrow">UNA NUEVA POSIBILIDAD</span><h2 id="create-title">Dale un lugar a tu campaña.</h2><p>Empieza con una composición profesional. Después podrás personalizar cada detalle en el editor.</p><small>La página nace como borrador privado. Tú decides cuándo publicarla.</small></div><form action="{{ route('admin.landings.store') }}" method="POST">@csrf<label class="field">Nombre de la campaña<input name="name" value="{{ old('name') }}" placeholder="Fotografía de bodas 2027" maxlength="160" required></label><label class="field">Enlace de la landing<div class="landing-slug-input"><span>/lp/</span><input name="slug" value="{{ old('slug') }}" placeholder="bodas-2027" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" maxlength="100" required></div></label><small class="field-help">Usa letras minúsculas, números y guiones. El enlace se mantiene estable para tus anuncios.</small><fieldset class="landing-preset-picker"><legend>Elige un punto de partida</legend><label><input type="radio" name="preset" value="editorial" @checked(old('preset', 'editorial') === 'editorial')><span class="preset-swatch editorial-swatch">Aa</span><span><strong>Editorial</strong><small>Cálida, elegante, atemporal</small></span></label><label><input type="radio" name="preset" value="modern" @checked(old('preset') === 'modern')><span class="preset-swatch modern-swatch">Aa</span><span><strong>Contemporánea</strong><small>Limpia, directa, expresiva</small></span></label><label><input type="radio" name="preset" value="wedding" @checked(old('preset') === 'wedding')><span class="preset-swatch wedding-swatch">R.</span><span><strong>Bodas de autor</strong><small>Fotografía protagonista, historias y consulta</small></span></label></fieldset><button class="button primary full-width" type="submit">Crear y personalizar →</button></form></section>
    @endunless
</main>
@unless($showTrash)@include('admin.landing-delete-dialog')@endunless
@endsection
