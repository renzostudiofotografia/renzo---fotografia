@extends('admin.layout')
@section('title', 'Landing pages')
@section('publication-status')<span class="draft-badge">Campañas</span>@endsection
@section('publish-action')<a class="button primary" href="#nueva-landing">＋ Crear landing</a>@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('css/landing-editor.css') }}?v={{ filemtime(public_path('css/landing-editor.css')) }}">@endpush
@section('content')
<main class="page-padding landing-index">
    <section class="landing-intro">
        <div><span class="eyebrow">CAMPAÑAS QUE CONECTAN</span><h1>De una primera mirada<br>a una nueva historia.</h1><p>Crea una página para cada campaña. Dale tu estilo, conecta tu Meta Pixel y recibe las consultas en un solo lugar.</p></div>
        <div class="landing-intro-note"><span>01 · DISEÑA</span><span>02 · PUBLICA</span><span>03 · CONECTA</span><small>Edición en vivo · Adaptable a móviles · Siempre en soles</small></div>
    </section>
    <div class="section-heading"><div><span class="eyebrow">TU COLECCIÓN</span><h2>Landing pages <span class="count-badge">{{ $landings->total() }}</span></h2></div><span class="muted">Cada campaña tiene su propia publicación.</span></div>
    @if($landings->isEmpty())
        <div class="empty-card"><span>↗</span><h3>Tu próxima campaña empieza aquí.</h3><p>Crea tu primera landing, personalízala y comparte su enlace en tus anuncios.</p><a class="button secondary" href="#nueva-landing">Crear mi primera landing →</a></div>
    @else
        <div class="landing-card-grid">
        @foreach($landings as $landing)
            @php
                $hero = collect($landing->draft['sections'] ?? [])->firstWhere('type', 'hero');
                $cover = $hero['image'] ?? 'assets/images/hero/hero-01.webp';
            @endphp
            <article class="landing-card">
                <a class="landing-card-cover" href="{{ route('admin.landings.edit', $landing) }}"><img src="{{ asset($cover ?: 'assets/images/hero/hero-01.webp') }}" alt="" loading="lazy"><span class="landing-status {{ $landing->published ? 'is-live' : '' }}">{{ $landing->published ? 'Publicada' : 'Borrador privado' }}</span><span class="landing-cover-title">{{ $hero['title'] ?? $landing->name }}</span></a>
                <div class="landing-card-content"><h3>{{ $landing->name }}</h3><p class="landing-slug">/lp/{{ $landing->slug }}</p><div class="landing-card-meta"><a href="{{ route('admin.landings.leads', $landing) }}"><strong>{{ $landing->leads_count }}</strong> {{ $landing->leads_count === 1 ? 'consulta recibida' : 'consultas recibidas' }} →</a><span>{{ $landing->updated_at->format('d/m/Y') }}</span></div><div class="landing-card-actions"><a class="button primary" href="{{ route('admin.landings.edit', $landing) }}">Editar landing →</a>@if($landing->published)<a class="text-link" href="{{ route('landing.show', $landing->slug) }}" target="_blank" rel="noopener">Ver publicada ↗</a>@else<a class="text-link" href="{{ route('admin.landings.preview', $landing) }}" target="_blank" rel="noopener">Vista previa ↗</a>@endif</div></div>
            </article>
        @endforeach
        </div>
        @if($landings->hasPages())<nav class="landing-pagination" aria-label="Páginas de campañas">@if($landings->onFirstPage())<span class="muted">← Anterior</span>@else<a class="button secondary" href="{{ $landings->previousPageUrl() }}" rel="prev">← Anterior</a>@endif<span>Página {{ $landings->currentPage() }} de {{ $landings->lastPage() }}</span>@if($landings->hasMorePages())<a class="button secondary" href="{{ $landings->nextPageUrl() }}" rel="next">Siguiente →</a>@else<span class="muted">Siguiente →</span>@endif</nav>@endif
    @endif
    <section class="landing-create" id="nueva-landing" aria-labelledby="create-title"><div><span class="eyebrow">UNA NUEVA POSIBILIDAD</span><h2 id="create-title">Dale un lugar a tu campaña.</h2><p>Empieza con una composición profesional. Después podrás personalizar cada detalle en el editor.</p><small>La página nace como borrador privado. Tú decides cuándo publicarla.</small></div><form action="{{ route('admin.landings.store') }}" method="POST">@csrf<label class="field">Nombre de la campaña<input name="name" value="{{ old('name') }}" placeholder="Fotografía de bodas 2027" maxlength="160" required></label><label class="field">Enlace de la landing<div class="landing-slug-input"><span>/lp/</span><input name="slug" value="{{ old('slug') }}" placeholder="bodas-2027" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" maxlength="100" required></div></label><small class="field-help">Usa letras minúsculas, números y guiones. El enlace se mantiene estable para tus anuncios.</small><fieldset class="landing-preset-picker"><legend>Elige un punto de partida</legend><label><input type="radio" name="preset" value="editorial" @checked(old('preset', 'editorial') === 'editorial')><span class="preset-swatch editorial-swatch">Aa</span><span><strong>Editorial</strong><small>Cálida, elegante, atemporal</small></span></label><label><input type="radio" name="preset" value="modern" @checked(old('preset') === 'modern')><span class="preset-swatch modern-swatch">Aa</span><span><strong>Contemporánea</strong><small>Limpia, directa, expresiva</small></span></label></fieldset><button class="button primary full-width" type="submit">Crear y personalizar →</button></form></section>
</main>
@endsection
