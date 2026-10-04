@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
<section class="page-header">
    <div class="page-header-container">
        <p class="section-tag">Experiencias Fotográficas</p>
        <h1 class="page-title">Servicios con Intención</h1>
        <p class="page-subtitle">Cada experiencia está diseñada para preservar su historia con calma, dirección sutil y una mirada editorial.</p>
    </div>
</section>

<section class="services-featured services-page">
    <div class="services-container">
        <div class="services-grid">
            @foreach($services as $service)
                <article class="service-card">
                    <div class="service-img-wrapper">
                        <img src="{{ asset($service['image']) }}" alt="{{ $service['image_alt'] }}">
                        <span class="service-number">{{ $service['number'] }}</span>
                    </div>
                    <div class="service-content">
                        <p class="section-tag">{{ $service['tag'] }}</p>
                        <h2 class="service-card-title">{{ $service['title'] }}</h2>
                        <p class="service-description">{{ $service['hero'] }}</p>
                        <a class="service-link" href="{{ route('services.show', $service['slug']) }}">Ver experiencia <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="portfolio-cta">
    <div class="portfolio-cta-container">
        <h2 class="portfolio-cta-title">Una propuesta creada alrededor de su historia</h2>
        <p class="portfolio-cta-desc">Compartan la fecha, locación y visión de su celebración para preparar una cobertura a medida.</p>
        <a class="cta-btn primary-btn" href="{{ route('contact') }}">Solicitar Propuesta</a>
    </div>
</section>
@endsection
