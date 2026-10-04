@extends('layouts.app')

@section('title', 'Sobre Mí')

@section('content')
<section class="about-hero">
    <div class="about-hero-container">
        <div class="about-hero-image">
            <img src="{{ asset('assets/images/portfolio/isabella-thomas.webp') }}" alt="{{ config('site.brand') }}, fotógrafo de bodas">
        </div>
        <div class="about-hero-text">
            <p class="section-tag">Detrás del Lente</p>
            <h1 class="about-hero-title">Hola, soy Renzo</h1>
            <p class="about-hero-subtitle">Fotógrafo de bodas enfocado en capturar emociones reales con un lenguaje visual honesto, atemporal y profundamente elegante.</p>
            <div class="about-hero-divider"></div>
            <p class="about-hero-bio">Con base en Arequipa y disponible para documentar historias de amor por todo el Perú y destinos internacionales.</p>
        </div>
    </div>
</section>

<section class="about-story">
    <div class="about-story-container">
        <h2 class="story-title">Mi Filosofía Fotográfica</h2>
        <p class="story-paragraph">Entiendo la fotografía de boda no como una lista de posados obligatorios, sino como un testimonio visual con alma. Mi objetivo es que dentro de 20 o 30 años, cuando vuelvan a abrir su álbum, no solo recuerden cómo se veían, sino exactamente cómo se sentían.</p>
        <p class="story-paragraph">Busco la luz natural, la complicidad en las miradas fugaces y el valor de los instantes no planificados: el abrazo apretado de un abuelo, las lágrimas contenidas antes de caminar al altar o las risas espontáneas a mitad del baile.</p>
    </div>
</section>

<section class="about-pillars">
    <div class="pillars-container">
        <header class="pillars-header">
            <p class="section-tag">Forma de Trabajo</p>
            <h2 class="pillars-title">Los Pilares de mi Enfoque</h2>
        </header>
        <div class="pillars-grid">
            <article class="pillar-card"><span class="pillar-number">01</span><h3 class="pillar-name">Sutileza</h3><p class="pillar-desc">Trabajo de forma discreta, integrándome como un invitado más para que vivan su día con serenidad y sin la presión de la cámara.</p></article>
            <article class="pillar-card"><span class="pillar-number">02</span><h3 class="pillar-name">Atemporalidad</h3><p class="pillar-desc">Evito modas pasajeras. Apuesto por colores fieles, blancos y negros expresivos y tonos cálidos que trasciendan las décadas.</p></article>
            <article class="pillar-card"><span class="pillar-number">03</span><h3 class="pillar-name">Conexión</h3><p class="pillar-desc">Construyo una relación cercana antes del gran día para que frente al lente exista confianza genuina y fluidez absoluta.</p></article>
        </div>
    </div>
</section>

<section class="about-personal">
    <div class="personal-container">
        <div class="personal-text">
            <p class="section-tag">Un Poco Más Sobre Mí</p>
            <h2 class="personal-title">Fuera del Lente</h2>
            <ul class="personal-list">
                <li><strong>Amante de los viajes:</strong> recorrer paisajes andinos y costas buscando nuevas perspectivas de luz.</li>
                <li><strong>Detallista por naturaleza:</strong> encuentro belleza en las texturas, la arquitectura clásica y el diseño limpio.</li>
                <li><strong>Buen café:</strong> nada supera una conversación tranquila antes de iniciar una sesión.</li>
            </ul>
        </div>
        <div class="personal-image-frame"><img src="{{ asset('assets/images/portfolio/sofia-gabriel.webp') }}" alt="Paisaje y fotografía de boda"></div>
    </div>
</section>

<section class="portfolio-cta">
    <div class="portfolio-cta-container">
        <h2 class="portfolio-cta-title">¿Sienten que conectamos con esta visión?</h2>
        <p class="portfolio-cta-desc">Escríbanme y conversemos sobre los detalles de su boda con un buen café o videollamada.</p>
        <a class="cta-btn primary-btn" href="{{ route('contact') }}">Iniciar Conversación</a>
    </div>
</section>
@endsection
