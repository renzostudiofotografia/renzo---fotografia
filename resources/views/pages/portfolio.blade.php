@extends('layouts.app')

@section('title', 'Portafolio')

@section('content')
<section class="page-header">
    <div class="page-header-container">
        <p class="section-tag">Galería & Historias</p>
        <h1 class="page-title">Portafolio Seleccionado</h1>
        <p class="page-subtitle">Una mirada honesta a instantes irrepetibles. Cada galería refleja la emoción genuina, la estética atemporal y la luz natural de cada historia.</p>
    </div>
</section>

<section class="portfolio-page-section">
    <div class="portfolio-page-container">
        <div class="portfolio-filter-bar" role="group" aria-label="Filtrar portafolio">
            <button class="filter-btn active" type="button" data-filter="all" aria-pressed="true">Todos</button>
            <button class="filter-btn" type="button" data-filter="wedding" aria-pressed="false">Bodas</button>
            <button class="filter-btn" type="button" data-filter="prewedding" aria-pressed="false">Preboda & Postboda</button>
            <button class="filter-btn" type="button" data-filter="elopement" aria-pressed="false">Elopements & Destino</button>
        </div>

        <div class="portfolio-gallery-grid" id="portfolio-grid">
            @foreach($portfolioItems as $item)
                <button class="gallery-card" type="button" data-category="{{ $item['category'] }}" data-caption="{{ $item['names'] }} — {{ $item['location'] }}">
                    <img src="{{ asset($item['image']) }}" alt="{{ $item['tag'] }} de {{ $item['names'] }}" loading="lazy">
                    <span class="gallery-card-overlay">
                        <span class="gallery-tag">{{ $item['tag'] }}</span>
                        <span class="gallery-card-title">{{ $item['names'] }}</span>
                        <span class="gallery-card-loc">{{ $item['location'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </div>
</section>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Vista ampliada" aria-hidden="true">
    <button class="lightbox-close" type="button" aria-label="Cerrar">×</button>
    <button class="lightbox-prev" type="button" aria-label="Fotografía anterior">❮</button>
    <div class="lightbox-content">
        <img class="lightbox-image" src="" alt="">
        <p class="lightbox-caption"></p>
    </div>
    <button class="lightbox-next" type="button" aria-label="Fotografía siguiente">❯</button>
</div>

<section class="portfolio-cta">
    <div class="portfolio-cta-container">
        <h2 class="portfolio-cta-title">¿Les gustaría que documente su gran día?</h2>
        <p class="portfolio-cta-desc">Cuéntenme sobre su fecha y locación para revisar disponibilidad en mi agenda.</p>
        <a class="cta-btn primary-btn" href="{{ route('contact') }}">Consultar Fecha</a>
    </div>
</section>
@endsection
