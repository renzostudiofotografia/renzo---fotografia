@extends('layouts.app')

@section('title', 'Fotografía de Bodas')

@section('content')
<section class="hero" aria-label="Historias de boda destacadas">
    <div class="hero-slides" aria-hidden="true">
        @foreach(range(1, 5) as $number)
            <div class="hero-slide {{ $loop->first ? 'active' : '' }}" style="background-image: url('{{ asset(sprintf('assets/images/hero/hero-%02d.webp', $number)) }}')"></div>
        @endforeach
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="hero-subtitle">Fotografía Profesional de Bodas</p>
        <a href="#portafolio" class="hero-btn">Ver Portafolio</a>
    </div>
    <div class="hero-dots" aria-label="Seleccionar fotografía del carrusel">
        @foreach(range(1, 5) as $number)
            <button class="dot {{ $loop->first ? 'active' : '' }}" type="button" aria-label="Fotografía {{ $number }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
        @endforeach
    </div>
</section>

<section class="experience" aria-label="Experiencia profesional">
    <div class="experience-container">
        <div class="experience-stats">
            <div class="stat-item"><span class="stat-number">+150</span><span class="stat-label">Bodas Documentadas</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><span class="stat-number">8</span><span class="stat-label">Años de Trayectoria</span></div>
        </div>
        <div class="experience-separator"></div>
        <div class="experience-quote">
            <blockquote class="quote-text">“Fotografiar es colocar la cabeza, el ojo y el corazón en el mismo eje.”</blockquote>
            <cite class="quote-author">— Henri Cartier-Bresson</cite>
        </div>
    </div>
</section>

<section class="about-philosophy">
    <div class="about-container">
        <div class="about-text">
            <p class="section-tag">Mi Filosofía</p>
            <h2 class="about-title">Documentando emociones con alma y elegancia</h2>
            <p class="about-description">Creo en la belleza de lo espontáneo, en las miradas cómplices que pasan desapercibidas y en los pequeños detalles que hacen de tu boda algo irrepetible.</p>
            <p class="about-description">Mi enfoque combina la estética editorial con la sutileza del fotoperiodismo. Sin poses forzadas, simplemente contando tu historia tal como se siente: auténtica, atemporal y llena de vida.</p>
            <a class="about-link" href="{{ route('about') }}">Conoce más sobre mí <span aria-hidden="true">→</span></a>
        </div>
        <div class="about-collage" aria-label="Detalles de boda">
            <figure class="collage-frame frame-1"><img src="{{ asset('assets/images/details/rings.webp') }}" alt="Detalle de anillos de boda"></figure>
            <figure class="collage-frame frame-2"><img src="{{ asset('assets/images/details/dress.webp') }}" alt="Detalle de vestido de boda"></figure>
            <figure class="collage-frame frame-3"><img src="{{ asset('assets/images/details/flowers.webp') }}" alt="Detalle de flores de boda"></figure>
        </div>
    </div>
</section>

<section class="services-featured">
    <div class="services-container">
        <header class="services-header">
            <p class="section-tag">Fine Art</p>
            <h2 class="services-title">Sesiones, Bodas y Destino</h2>
        </header>
        <div class="services-grid">
            @foreach($services as $service)
                <article class="service-card">
                    <div class="service-img-wrapper">
                        <img src="{{ asset($service['image']) }}" alt="{{ $service['image_alt'] }}">
                        <span class="service-number">{{ $service['number'] }}</span>
                    </div>
                    <div class="service-content">
                        <h3 class="service-card-title">{{ $service['title'] }}</h3>
                        <p class="service-description">{{ $service['summary'] }}</p>
                        <a href="{{ route('services.show', $service['slug']) }}" class="service-link">Descubrir experiencia <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="portfolio-featured" id="portafolio">
    <div class="portfolio-container">
        <header class="portfolio-header">
            <p class="section-tag">Galería Seleccionada</p>
            <h2 class="portfolio-title">Experiencias</h2>
        </header>
        <div class="portfolio-grid">
            @foreach($portfolioItems as $item)
                <a class="portfolio-item" href="{{ route('portfolio') }}" aria-label="Ver historia de {{ $item['names'] }}">
                    <img src="{{ asset($item['image']) }}" alt="{{ $item['tag'] }} de {{ $item['names'] }}" loading="lazy">
                    <div class="portfolio-overlay"><h3 class="portfolio-item-title">{{ $item['names'] }}</h3></div>
                </a>
            @endforeach
        </div>
        <div class="portfolio-footer"><a class="portfolio-main-btn" href="{{ route('portfolio') }}">Explorar Reportaje de Bodas</a></div>
    </div>
</section>

<section class="testimonials" aria-labelledby="testimonial-title">
    <div class="testimonials-container">
        <header class="testimonials-header">
            <p class="section-tag">Palabras del Corazón</p>
            <h2 class="testimonials-title" id="testimonial-title">Dedicatorias y Comentarios</h2>
        </header>
        <div class="testimonial-slider" aria-live="polite">
            @foreach($testimonials as $testimonial)
                <article class="testimonial-card {{ $loop->first ? 'active' : '' }}">
                    <span class="testimonial-quote-mark" aria-hidden="true">“</span>
                    <p class="testimonial-text">{{ $testimonial['quote'] }}</p>
                    <div class="testimonial-author">
                        <h3 class="author-name">{{ $testimonial['names'] }}</h3>
                        <span class="author-detail">{{ $testimonial['detail'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="testimonial-dots" aria-label="Seleccionar testimonio">
            @foreach($testimonials as $testimonial)
                <button class="t-dot {{ $loop->first ? 'active' : '' }}" type="button" aria-label="Testimonio {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
            @endforeach
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="cta-overlay"></div>
    <div class="cta-container">
        <p class="section-tag">Planifiquemos tu Boda</p>
        <h2 class="cta-title">Hagamos que tus recuerdos sean eternos</h2>
        <p class="cta-description">Las fechas para la temporada suelen reservarse con meses de anticipación. Cuéntame sobre tu boda, la locación y la visión que tienen juntos.</p>
        <div class="cta-buttons">
            <a class="cta-btn primary-btn" href="{{ route('contact') }}">Verificar Disponibilidad</a>
            <a class="cta-btn secondary-btn" href="https://wa.me/{{ config('site.phone_link') }}" target="_blank" rel="noopener noreferrer">Contáctanos al WhatsApp</a>
        </div>
    </div>
</section>
@endsection
