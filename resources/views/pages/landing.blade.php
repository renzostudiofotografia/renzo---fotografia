@php
    $fonts = config('editor.fonts');
    $wedding = ($data['visual_theme'] ?? 'classic') === 'wedding';
    $hasVideo = static fn (array $item): bool => ($item['media_type'] ?? 'image') === 'video' && filled($item['video_url'] ?? '');
    $buttonIcon = $data['button_icon'] ?? 'arrow-right';
    $stories = collect($data['stories'] ?? [])->filter(fn (array $item): bool => ($item['enabled'] ?? true) && (filled($item['image'] ?? '') || $hasVideo($item)));
    $experiences = collect($data['experiences'] ?? [])->filter(fn (array $item): bool => ($item['enabled'] ?? true) && filled($item['name'] ?? ''));
    $metrics = collect($data['trust_metrics'] ?? [])->filter(fn (array $item): bool => ($item['enabled'] ?? true) && filled($item['value'] ?? '') && filled($item['label'] ?? ''));
    $processSteps = collect($data['process_steps'] ?? [])->filter(fn (array $item): bool => ($item['enabled'] ?? true) && filled($item['title'] ?? ''));
    $navigation = collect($data['nav_links'] ?? [])->filter(fn (array $item): bool => ($item['enabled'] ?? true) && filled($item['label'] ?? '') && filled($item['url'] ?? ''));
    $rates = collect($data['calculator_rates'] ?? [])->filter(fn (array $item): bool => ($item['enabled'] ?? true) && filled($item['label'] ?? '') && (float) ($item['amount'] ?? 0) >= 0 && ($item['category'] !== 'coverage' || (float) $item['amount'] > 0));
    $lines = static fn (string $text): array => array_values(array_filter(array_map('trim', preg_split('/\R/', $text))));
    $fontFamilies = array_unique([$fonts[$data['heading_font']], $fonts[$data['body_font']]]);
    $leadSection = collect($data['sections'])->first(fn (array $section): bool => $section['type'] === 'lead' && $section['enabled']);
    $primaryButtonUrl = app(\App\LandingContent::class)->primaryButtonUrl($data);
    $hasPrimaryDestination = $leadSection || $primaryButtonUrl !== '#contacto';
    $heroSection = collect($data['sections'])->first(fn (array $section): bool => $section['type'] === 'hero' && $section['enabled']);
    $heroImages = collect($data['hero_images'])->filter(fn (array $image): bool => filled($image['image']))->values();
    if ($heroImages->isEmpty() && !empty($heroSection['image'])) { $heroImages->push(['image' => $heroSection['image'], 'alt' => $heroSection['alt']]); }
    $galleryImages = collect($data['gallery'])->filter(fn (array $image): bool => filled($image['image']) || $hasVideo($image))->values();
    $testimonials = collect($data['testimonials'])->filter(fn (array $testimonial): bool => ($testimonial['enabled'] ?? true) && (filled($testimonial['quote']) || $hasVideo($testimonial)))->values();
    $heroPoster = $data['hero_video_poster'] ?: ($heroSection['image'] ?? '');
    $shareImage = $data['share_image'] ?: ($data['hero_media'] === 'video' ? $heroPoster : ($data['hero_media'] === 'carousel' ? ($heroImages->first()['image'] ?? '') : ($heroSection['image'] ?? '')));
    $shareImageAlt = $heroSection['alt'] ?? $data['brand_name'];
    $heroLayout = 'full';
    $conversion = session('landing_conversion');
    $trackingConfiguration = [
        'preview' => $preview,
        'pageId' => $landing->id,
        'pixelId' => $preview ? '' : $data['pixel_id'],
        'ga4Id' => $preview ? '' : ($data['ga4_id'] ?? ''),
        'viewContent' => ! $preview && (bool) $data['track_view_content'],
        'lead' => ! $preview && (bool) $data['track_lead'],
        'value' => (float) $data['lead_value'],
        'contentValue' => (float) $data['price'],
        'currency' => 'PEN',
        'contentName' => $data['page_title'],
        'conversion' => ! $preview && ($conversion['page_id'] ?? null) === $landing->id ? $conversion : null,
    ];
    $contactRendered = false;
    $heroRendered = false;
    $sectionAnchors = [];
    $structuredData = array_filter([
        '@context' => 'https://schema.org', '@type' => 'ProfessionalService',
        'name' => $data['brand_name'], 'url' => route('landing.show', $landing->slug),
        'description' => $data['meta_description'], 'image' => $shareImage ? asset($shareImage) : null,
        'logo' => !empty($data['brand_logo']) ? asset($data['brand_logo']) : null,
        'email' => $data['contact_email'] ?? null,
        'telephone' => $data['whatsapp'] ? '+'.$data['whatsapp'] : null,
        'areaServed' => $data['contact_location'] ?? null,
        'sameAs' => !empty($data['instagram_url']) ? [$data['instagram_url']] : null,
    ]);
@endphp
<!DOCTYPE html>
<html lang="es-PE">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $data['meta_description'] }}">
    @if($preview)<meta name="robots" content="noindex, nofollow">@else<link rel="canonical" href="{{ route('landing.show', $landing->slug) }}">@endif
    <meta property="og:title" content="{{ $data['page_title'] }}">
    <meta property="og:description" content="{{ $data['meta_description'] }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_PE">
    @if(!$preview)<meta property="og:url" content="{{ route('landing.show', $landing->slug) }}"><script type="application/ld+json">{!! json_encode($structuredData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>@endif
    @if($shareImage)<meta property="og:image" content="{{ asset($shareImage) }}"><meta property="og:image:alt" content="{{ $shareImageAlt }}">@endif
    <meta name="twitter:card" content="{{ $shareImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $data['page_title'] }}">
    <meta name="twitter:description" content="{{ $data['meta_description'] }}">
    @if($shareImage)<meta name="twitter:image" content="{{ asset($shareImage) }}"><meta name="twitter:image:alt" content="{{ $shareImageAlt }}">@endif
    <meta name="theme-color" content="{{ $data['background_color'] }}">
    <title>{{ $data['page_title'] }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family={{ implode('&family=', $fontFamilies) }}&display=swap">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v={{ filemtime(public_path('css/landing.css')) }}">
    <style>
        :root {
            --landing-heading-font: '{{ $data['heading_font'] }}', serif;
            --landing-body-font: '{{ $data['body_font'] }}', sans-serif;
            --landing-heading-size: {{ $data['heading_size'] }}px;
            --landing-body-size: {{ $data['body_size'] }}px;
            --landing-background: {{ $data['background_color'] }};
            --landing-text: {{ $data['text_color'] }};
            --landing-accent: {{ $data['accent_color'] }};
            --landing-button-text: {{ $data['button_text_color'] }};
            --landing-button-radius: {{ $data['button_radius'] }}px;
            --landing-button-secondary: {{ $data['button_secondary_color'] ?? '#8b642e' }};
            --landing-button-speed: {{ $data['button_animation_speed'] ?? 6 }}s;
            --landing-spacing: {{ $data['section_spacing'] }}px;
            --hero-image-fit: {{ $data['hero_image_fit'] }};
            --hero-image-position: {{ $data['hero_focal_point'] }};
            --hero-framing-scale: {{ 1 + ($data['hero_crop_zoom'] ?? 4) / 100 }};
            --hero-mobile-image-position: {{ $data['hero_mobile_focal_point'] ?? 'center' }};
            --landing-light-color: {{ $data['light_color'] }};
            --landing-light-opacity: {{ $data['light_intensity'] / 100 }};
        }
    </style>
    <script id="landing-configuration" type="application/json">{!! json_encode($trackingConfiguration, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    <script src="{{ asset('js/landing.js') }}?v={{ filemtime(public_path('js/landing.js')) }}" defer></script>
</head>
<body class="landing-page {{ $wedding ? 'theme-wedding' : 'theme-classic' }} button-{{ $data['button_style'] }} button-size-{{ $data['button_size'] ?? 'regular' }} animation-{{ $data['animation'] }} button-animation-{{ $data['button_animation'] }} light-effect-{{ $data['light_effect'] }} image-motion-{{ $data['image_motion'] }} {{ $preview ? 'landing-preview' : '' }}" data-landing-id="{{ $landing->id }}">
    <a class="landing-skip" href="#main-content">Saltar al contenido</a>
    @if($data['light_effect'] !== 'none')<div class="landing-light" aria-hidden="true"><span></span><span></span></div>@endif
    <header class="landing-header">
        <a href="#main-content" class="landing-brand" aria-label="{{ $data['brand_name'] }} · inicio">@if(!empty($data['brand_logo']))<img class="landing-logo" src="{{ asset($data['brand_logo']) }}" alt="{{ $data['brand_name'] }}" width="220" height="65">@else{{ $data['brand_name'] }}@endif @if(!empty($data['brand_tagline']))<span>{{ $data['brand_tagline'] }}</span>@endif</a>
        @if($navigation->isNotEmpty())<nav class="landing-navigation" aria-label="Navegación principal">@foreach($navigation as $link)<a href="{{ $link['url'] }}">{{ $link['label'] }}</a>@endforeach</nav>@endif
        @if($hasPrimaryDestination)<a class="landing-header-link" href="{{ $primaryButtonUrl }}">{{ $data['button_label'] }} @include('pages.landing.icon', ['name' => $buttonIcon])</a>@endif
    </header>
    <main id="main-content">
    @foreach($data['sections'] as $sectionIndex => $section)
        @continue(!$section['enabled'])
        @continue($section['type'] === 'testimonials' && $testimonials->isEmpty())
        @continue($section['type'] === 'stories' && $stories->isEmpty())
        @continue($section['type'] === 'experiences' && $experiences->isEmpty())
        @continue($section['type'] === 'trust' && $metrics->isEmpty())
        @continue($section['type'] === 'process' && $processSteps->isEmpty())
        @continue($section['type'] === 'about' && empty($data['about_bio']) && empty($section['text']) && empty($section['image']) && !$hasVideo($section))
        @php
            $sectionId = $section['type'] === 'lead' && ! $contactRendered ? 'contacto' : 'section-'.$sectionIndex;
            $sectionAnchor = match ($section['type']) { 'stories' => 'historias', 'experiences' => 'experiencias', 'about' => 'renzo', 'process' => 'proceso', default => null };
            if ($sectionAnchor && in_array($sectionAnchor, $sectionAnchors, true)) { $sectionAnchor = null; }
            if ($sectionAnchor) { $sectionAnchors[] = $sectionAnchor; }
            $sectionButton = $section['button_label'] ?: $data['button_label'];
            $sectionButtonUrl = $section['button_url'] ?: $primaryButtonUrl;
            if ($sectionButtonUrl === '#contacto' && !$leadSection) { $sectionButtonUrl = $primaryButtonUrl; }
            $isHero = $section['type'] === 'hero';
            $globalAlignment = $data['text_alignment'] ?? 'section';
            $sectionAlignment = $section['alignment'] ?? 'inherit';
            $alignment = $globalAlignment !== 'section' ? $globalAlignment : ($sectionAlignment === 'inherit' ? 'left' : $sectionAlignment);
            $heroTextPosition = $globalAlignment !== 'section' ? $globalAlignment : ($sectionAlignment === 'inherit' ? ($data['hero_text_position'] ?? 'left') : $alignment);
            $sectionHasVideo = $hasVideo($section);
            $hasHeroMedia = $section['image'] || ($data['hero_media'] === 'carousel' && $heroImages->isNotEmpty()) || ($data['hero_media'] === 'video' && ($data['hero_video_url'] || $heroPoster));
            $headingTag = $isHero && ! $heroRendered ? 'h1' : 'h2';
            if ($isHero) { $heroRendered = true; }
            if ($section['type'] === 'lead') { $contactRendered = true; }
        @endphp
        <section id="{{ $sectionId }}" class="landing-section section-{{ $section['type'] }} align-{{ $alignment }} {{ $isHero ? 'hero-'.$heroLayout.' hero-media-'.$data['hero_media'].' hero-text-'.$heroTextPosition.' hero-mobile-'.($data['hero_mobile_layout'] ?? 'overlay') : '' }}" data-landing-section="{{ $sectionIndex }}" @if(in_array($section['type'], ['stories', 'gallery'])) data-analytics-section="portfolio" @elseif($section['type'] === 'experiences') data-analytics-section="experience" @endif aria-labelledby="heading-{{ $sectionIndex }}" style="--section-padding:{{ $section['padding'] }}px;@if($section['custom_background']) background-color:{{ $section['background'] }};@endif">
            @if($sectionAnchor)<span id="{{ $sectionAnchor }}" class="section-anchor" aria-hidden="true"></span>@endif
            <div class="landing-container {{ in_array($section['type'], ['hero', 'lead', 'text', 'about']) ? 'landing-split' : '' }} {{ ($isHero && !$hasHeroMedia) || (in_array($section['type'], ['text', 'about']) && !$section['image'] && !$sectionHasVideo) ? 'without-image' : '' }}">
                <div class="section-intro reveal">
                    @if($section['eyebrow'])<p class="landing-eyebrow"><span aria-hidden="true"></span>{{ $section['eyebrow'] }}</p>@endif
                    <{{ $headingTag }} id="heading-{{ $sectionIndex }}">{{ $section['title'] }}</{{ $headingTag }}>
                    @if($section['text'])<p class="landing-description">{{ $section['text'] }}</p>@endif
                    @if($section['type'] === 'about')
                        @if(!empty($data['about_bio']))<p class="landing-description about-biography">{{ $data['about_bio'] }}</p>@endif
                        @if(!empty($data['about_philosophy']))<p class="about-philosophy">{{ $data['about_philosophy'] }}</p>@endif
                        @if(!empty($data['about_signature']))<img class="about-signature" src="{{ asset($data['about_signature']) }}" alt="Firma de {{ $data['brand_name'] }}" width="200" height="80" loading="lazy">@endif
                    @endif
                    @if($isHero || in_array($section['type'], ['text', 'about', 'closing']))
                        @if($hasPrimaryDestination)<a class="landing-button" href="{{ $sectionButtonUrl }}">{{ $sectionButton }} @include('pages.landing.icon', ['name' => $buttonIcon])</a>@endif
                        @if($isHero)
                            @if(!empty($data['hero_secondary_label']) && !empty($data['hero_secondary_url']))<a class="hero-secondary-link" href="{{ $data['hero_secondary_url'] }}">{{ $data['hero_secondary_label'] }} @include('pages.landing.icon', ['name' => 'arrow-down'])</a>@endif
                            @if(count($data['trust_points']))<ul class="hero-trust" aria-label="Nuestra atención">@foreach($data['trust_points'] as $point)@if($point['text'])<li>@include('pages.landing.icon', ['name' => 'check']){{ $point['text'] }}</li>@endif @endforeach</ul>@endif
                            @if(!empty($data['brand_tagline']))<p class="hero-caption">{{ $data['brand_tagline'] }}</p>@endif
                        @endif
                    @endif
                    @if($section['type'] === 'lead')
                        @if(!empty($data['form_note']))<div class="contact-assurance">@include('pages.landing.icon', ['name' => $buttonIcon])<p>{{ $data['form_note'] }}</p></div>@endif
                        @if($data['whatsapp'])<a class="landing-text-link" href="https://wa.me/{{ $data['whatsapp'] }}" target="_blank" rel="noopener noreferrer">También puedes escribir por WhatsApp @include('pages.landing.icon', ['name' => $buttonIcon])</a>@endif
                        @if($section['image'])<img class="contact-image" src="{{ asset($section['image']) }}" alt="{{ $section['alt'] }}" loading="lazy" width="720" height="500">@endif
                    @endif
                </div>

                @if($isHero && $data['hero_media'] === 'carousel' && $heroImages->isNotEmpty())
                    <div class="hero-visual hero-carousel landing-carousel reveal" data-carousel data-autoplay="{{ $data['carousel_autoplay'] ? '1' : '0' }}" data-interval="{{ $data['carousel_interval'] }}" role="region" aria-roledescription="carrusel" aria-label="Fotografías de la portada">
                        <div class="carousel-track" id="hero-track-{{ $sectionIndex }}" data-carousel-track tabindex="0" aria-label="Fotografías de portada; usa las flechas para navegar">
                        @foreach($heroImages as $image)<figure class="carousel-slide" data-carousel-slide role="group" aria-roledescription="diapositiva" aria-label="{{ $loop->iteration }} de {{ $loop->count }}"><div class="carousel-image-frame"><img src="{{ asset($image['image']) }}" alt="{{ $image['alt'] }}" width="1200" height="1500" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif></div>@if($image['alt'])<figcaption>{{ $image['alt'] }}</figcaption>@endif</figure>@endforeach
                        </div>
                        @if($heroImages->count() > 1)<div class="carousel-controls" data-carousel-controls hidden><button type="button" class="carousel-arrow" data-carousel-prev aria-label="Fotografía anterior" aria-controls="hero-track-{{ $sectionIndex }}">@include('pages.landing.icon', ['name' => 'chevron-left'])</button><div class="carousel-dots">@foreach($heroImages as $image)<button type="button" data-carousel-dot="{{ $loop->index }}" aria-label="Mostrar fotografía {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-controls="hero-track-{{ $sectionIndex }}"><span></span></button>@endforeach</div><button type="button" class="carousel-pause" data-carousel-pause aria-label="Pausar reproducción automática" @if(!$data['carousel_autoplay']) hidden @endif><span data-carousel-pause-icon>@include('pages.landing.icon', ['name' => 'pause'])</span><span data-carousel-play-icon hidden>@include('pages.landing.icon', ['name' => 'play'])</span></button><button type="button" class="carousel-arrow" data-carousel-next aria-label="Siguiente fotografía" aria-controls="hero-track-{{ $sectionIndex }}">@include('pages.landing.icon', ['name' => 'chevron-right'])</button></div>@endif
                    </div>
                @elseif($isHero && $data['hero_media'] === 'video' && $data['hero_video_url'])
                    <figure class="hero-visual hero-video reveal"><div class="hero-video-frame">@include('pages.landing.video', ['videoUrl' => $data['hero_video_url'], 'videoPoster' => $heroPoster, 'videoLabel' => $section['alt'] ?: 'Video de presentación del estudio', 'autoplay' => $data['hero_video_autoplay'], 'deferred' => false])</div><figcaption><span>{{ $data['brand_name'] }}</span><span>UNA HISTORIA EN MOVIMIENTO</span></figcaption></figure>
                @elseif(!$isHero && in_array($section['type'], ['text', 'about', 'closing']) && $sectionHasVideo)
                    <figure class="hero-visual section-video reveal">@include('pages.landing.video', ['videoUrl' => $section['video_url'], 'videoPoster' => $section['video_poster'] ?: $section['image'], 'videoLabel' => $section['alt'] ?: $section['title'], 'autoplay' => false, 'deferred' => false])</figure>
                @elseif($isHero || in_array($section['type'], ['text', 'about', 'closing']))
                    @php $staticImage = $isHero && $data['hero_media'] === 'video' ? $heroPoster : $section['image']; @endphp
                    @if($staticImage)<figure class="hero-visual {{ $isHero && ($data['hero_media'] === 'flyer' || $data['hero_image_fit'] === 'contain') ? 'is-flyer' : '' }} reveal"><div class="hero-image-frame"><picture>@if($isHero && !empty($data['hero_mobile_image']))<source media="(max-width: 700px)" srcset="{{ asset($data['hero_mobile_image']) }}">@endif<img src="{{ asset($staticImage) }}" alt="{{ $section['alt'] }}" width="1200" height="1500" @if($isHero) fetchpriority="high" @else loading="lazy" @endif decoding="async"></picture></div><figcaption><span>{{ $data['brand_name'] }}</span><span aria-hidden="true">01 / HISTORIAS</span></figcaption></figure>@endif
                @elseif($section['type'] === 'trust')
                    <dl class="wedding-metrics">@foreach($metrics as $metric)<div class="wedding-metric reveal"><dt>{{ $metric['label'] }}</dt><dd>{{ $metric['value'] }}</dd></div>@endforeach</dl>
                @elseif($section['type'] === 'stories')
                    @include('pages.landing.stories')
                @elseif($section['type'] === 'experiences')
                    <div class="wedding-experiences">@foreach($experiences as $experienceIndex => $experience)<article class="wedding-experience {{ $experience['featured'] ? 'is-featured' : '' }} reveal" data-experience-index="{{ $experienceIndex }}">@if($hasVideo($experience))<figure>@include('pages.landing.video', ['videoUrl' => $experience['video_url'], 'videoPoster' => $experience['video_poster'] ?: $experience['image'], 'videoLabel' => $experience['alt'] ?: $experience['name'], 'autoplay' => false, 'deferred' => false])</figure>@elseif($experience['image'])<figure><img src="{{ asset($experience['image']) }}" alt="{{ $experience['alt'] }}" loading="lazy" decoding="async" width="800" height="1000"></figure>@endif<div class="experience-copy"><span class="experience-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $experience['name'] }}</h3>@if($experience['subtitle'])<p class="experience-subtitle">{{ $experience['subtitle'] }}</p>@endif @if($experience['description'])<p>{{ $experience['description'] }}</p>@endif @if(count($lines($experience['inclusions'])))<ul>@foreach($lines($experience['inclusions']) as $inclusion)<li>{{ $inclusion }}</li>@endforeach</ul>@endif @if($experience['hours'] > 0)<p class="experience-detail">{{ $experience['hours'] }} horas de cobertura</p>@endif @if($experience['show_price'] && (float) $experience['price'] > 0)<p class="experience-price">Desde S/ {{ number_format((float) $experience['price'], 2, '.', ',') }}</p>@endif @if($experience['button_label'])<a class="landing-text-link" href="{{ $experience['button_url'] ?: $primaryButtonUrl }}">{{ $experience['button_label'] }} @include('pages.landing.icon', ['name' => $buttonIcon])</a>@endif</div></article>@endforeach</div>
                @elseif($section['type'] === 'process')
                    <ol class="wedding-process">@foreach($processSteps as $step)<li class="reveal"><span class="process-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div><h3>{{ $step['title'] }}</h3>@if($step['text'])<p>{{ $step['text'] }}</p>@endif</div></li>@endforeach</ol>
                @elseif($section['type'] === 'benefits')
                    <div class="benefits-grid">@foreach($data['benefits'] as $benefit)<article class="benefit-item reveal"><span class="benefit-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $benefit['title'] }}</h3><p>{{ $benefit['text'] }}</p></article>@endforeach</div>
                @elseif($section['type'] === 'gallery')
                    @if($data['gallery_layout'] === 'carousel' && $galleryImages->isNotEmpty())
                    <div class="gallery-carousel landing-carousel reveal" data-carousel data-autoplay="{{ $data['carousel_autoplay'] ? '1' : '0' }}" data-interval="{{ $data['carousel_interval'] }}" role="region" aria-roledescription="carrusel" aria-label="Galería de historias">
                        <div class="carousel-track" id="gallery-track-{{ $sectionIndex }}" data-carousel-track tabindex="0" aria-label="Galería; usa las flechas para navegar">@foreach($galleryImages as $image)<figure class="carousel-slide" data-carousel-slide role="group" aria-roledescription="diapositiva" aria-label="{{ $loop->iteration }} de {{ $loop->count }}"><div class="carousel-image-frame">@if($hasVideo($image))@include('pages.landing.video', ['videoUrl' => $image['video_url'], 'videoPoster' => $image['video_poster'] ?: $image['image'], 'videoLabel' => $image['alt'] ?: 'Video de la galería', 'autoplay' => false, 'deferred' => false])@else<img src="{{ asset($image['image']) }}" alt="{{ $image['alt'] }}" loading="lazy" width="1200" height="900">@endif</div>@if($image['alt'])<figcaption>{{ $image['alt'] }}</figcaption>@endif</figure>@endforeach</div>
                        @if($galleryImages->count() > 1)<div class="carousel-controls" data-carousel-controls hidden><button type="button" class="carousel-arrow" data-carousel-prev aria-label="Fotografía anterior" aria-controls="gallery-track-{{ $sectionIndex }}">@include('pages.landing.icon', ['name' => 'chevron-left'])</button><div class="carousel-dots">@foreach($galleryImages as $image)<button type="button" data-carousel-dot="{{ $loop->index }}" aria-label="Mostrar fotografía {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-controls="gallery-track-{{ $sectionIndex }}"><span></span></button>@endforeach</div><button type="button" class="carousel-pause" data-carousel-pause aria-label="Pausar reproducción automática" @if(!$data['carousel_autoplay']) hidden @endif><span data-carousel-pause-icon>@include('pages.landing.icon', ['name' => 'pause'])</span><span data-carousel-play-icon hidden>@include('pages.landing.icon', ['name' => 'play'])</span></button><button type="button" class="carousel-arrow" data-carousel-next aria-label="Siguiente fotografía" aria-controls="gallery-track-{{ $sectionIndex }}">@include('pages.landing.icon', ['name' => 'chevron-right'])</button></div>@endif
                    </div>
                    @else<div class="landing-gallery">@foreach($galleryImages as $image)<figure class="gallery-image reveal"><div class="gallery-image-frame">@if($hasVideo($image))@include('pages.landing.video', ['videoUrl' => $image['video_url'], 'videoPoster' => $image['video_poster'] ?: $image['image'], 'videoLabel' => $image['alt'] ?: 'Video de la galería', 'autoplay' => false, 'deferred' => false])@else<img src="{{ asset($image['image']) }}" alt="{{ $image['alt'] }}" loading="lazy" width="900" height="1100">@endif</div>@if($image['alt'])<figcaption>{{ $image['alt'] }}</figcaption>@endif</figure>@endforeach</div>@endif
                @elseif($section['type'] === 'offer')
                    <div class="offer-panel reveal"><div><span class="landing-eyebrow">{{ $data['offer_label'] }}</span>@if(($data['price_visible'] ?? true) && (float) $data['price'] > 0)<p class="offer-price"><span>S/</span> {{ number_format((float) $data['price'], 2, '.', ',') }}</p>@else<p class="offer-price price-on-request">Una propuesta a su medida</p>@endif @if($data['price_note'])<p class="offer-note">{{ $data['price_note'] }}</p>@endif @if(!empty($data['offer_inclusions']))<ul class="offer-inclusions">@foreach($lines($data['offer_inclusions']) as $inclusion)<li>{{ $inclusion }}</li>@endforeach</ul>@endif @if(!empty($data['offer_extras']))<p class="offer-note">{{ $data['offer_extras'] }}</p>@endif</div><div class="offer-actions">@if($hasPrimaryDestination)<a class="landing-button" href="{{ $sectionButtonUrl }}">{{ $sectionButton }} @include('pages.landing.icon', ['name' => $buttonIcon])</a>@endif @if($data['whatsapp'])<a class="landing-text-link" href="https://wa.me/{{ $data['whatsapp'] }}" target="_blank" rel="noopener noreferrer">Conversar por WhatsApp @include('pages.landing.icon', ['name' => 'chat-bubble-left-right'])</a>@endif</div></div>
                    @if(($data['calculator_enabled'] ?? false) && $rates->where('category', 'coverage')->isNotEmpty())@include('pages.landing.calculator')@endif
                @elseif($section['type'] === 'testimonials')
                    <div class="testimonials-grid">@foreach($testimonials as $testimonial)<figure class="testimonial-item reveal">@if($hasVideo($testimonial))<div class="testimonial-media">@include('pages.landing.video', ['videoUrl' => $testimonial['video_url'], 'videoPoster' => $testimonial['video_poster'] ?: $testimonial['image'], 'videoLabel' => 'Testimonio de '.$testimonial['name'], 'autoplay' => false, 'deferred' => false])</div>@endif @if($testimonial['quote'])<span class="quote-mark" aria-hidden="true">“</span><blockquote>{{ $testimonial['quote'] }}</blockquote>@endif<figcaption class="testimonial-person">@if($testimonial['image'])<img class="testimonial-photo" src="{{ asset($testimonial['image']) }}" alt="{{ $testimonial['name'] }}" loading="lazy" width="96" height="96">@else<span class="testimonial-initial" aria-hidden="true">{{ mb_substr($testimonial['name'], 0, 1) }}</span>@endif<div><strong>{{ $testimonial['name'] }}</strong>@if($testimonial['role'])<span>{{ $testimonial['role'] }}</span>@endif @if(!empty($testimonial['location']))<span>{{ $testimonial['location'] }}@if(!empty($testimonial['date'])) · {{ $testimonial['date'] }}@endif</span>@endif @if(($testimonial['rating'] ?? 0) > 0)<span class="testimonial-rating" aria-label="{{ $testimonial['rating'] }} de 5 estrellas">@for($star = 0; $star < $testimonial['rating']; $star++)@include('pages.landing.icon', ['name' => 'star'])@endfor</span>@endif @if(!empty($testimonial['review_url']))<a class="testimonial-source" href="{{ $testimonial['review_url'] }}" target="_blank" rel="noopener noreferrer">Leer reseña original @include('pages.landing.icon', ['name' => 'arrow-up-right'])</a>@endif</div></figcaption></figure>@endforeach</div>
                @elseif($section['type'] === 'faq')
                    <div class="landing-faq">@foreach($data['faqs'] as $faq)<details class="faq-item reveal"><summary>{{ $faq['question'] }}@include('pages.landing.icon', ['name' => 'plus'])</summary><p>{{ $faq['answer'] }}</p></details>@endforeach</div>
                @elseif($section['type'] === 'lead')
                    @include('pages.landing.form')
                @endif
                @if(!in_array($section['type'], ['hero', 'text', 'about', 'closing', 'offer', 'lead']) && $section['button_label'])
                    <a class="landing-button section-extra-button reveal" href="{{ $sectionButtonUrl }}">{{ $sectionButton }} @include('pages.landing.icon', ['name' => $buttonIcon])</a>
                @endif
            </div>
        </section>
    @endforeach
    </main>
    <footer class="landing-footer"><div class="footer-brand"><a href="#main-content" class="landing-brand">@if(!empty($data['brand_logo']))<img class="landing-logo" src="{{ asset($data['brand_logo']) }}" alt="{{ $data['brand_name'] }}" width="220" height="65" loading="lazy">@else{{ $data['brand_name'] }}@endif</a>@if(!empty($data['contact_location']))<p>{{ $data['contact_location'] }}</p>@endif @if(!empty($data['brand_tagline']))<p>{{ $data['brand_tagline'] }}</p>@endif</div><nav class="footer-links" aria-label="Contacto y enlaces">@if($hasPrimaryDestination)<a class="landing-footer-cta" href="{{ $primaryButtonUrl }}">{{ $data['button_label'] }} @include('pages.landing.icon', ['name' => $buttonIcon])</a>@endif @if($data['whatsapp'])<a href="https://wa.me/{{ $data['whatsapp'] }}" target="_blank" rel="noopener noreferrer">WhatsApp @include('pages.landing.icon', ['name' => 'chat-bubble-left-right'])</a>@endif @if(!empty($data['instagram_url']))<a href="{{ $data['instagram_url'] }}" target="_blank" rel="noopener noreferrer">Instagram @include('pages.landing.icon', ['name' => 'arrow-up-right'])</a>@endif @if(!empty($data['contact_email']))<a href="mailto:{{ $data['contact_email'] }}">{{ $data['contact_email'] }}</a>@endif @foreach($navigation as $link)<a href="{{ $link['url'] }}">{{ $link['label'] }}</a>@endforeach @if(!empty($data['privacy_url']))<a href="{{ $data['privacy_url'] }}">Privacidad</a>@endif @if(!empty($data['terms_url']))<a href="{{ $data['terms_url'] }}">Términos legales</a>@endif</nav><small>© {{ now()->year }} {{ $data['brand_name'] }}<br>{{ $data['footer_note'] ?? 'Todos los precios en soles (PEN).' }}<span class="footer-credit">Desarrollado por <a href="https://odymcompany.com/" target="_blank" rel="noopener noreferrer">ODYMCOMPANY</a></span></small></footer>
    @if($data['sticky_cta'] && $hasPrimaryDestination)<div class="mobile-cta floating-cta" data-floating-cta><span class="floating-cta-note">{{ $data['brand_name'] }}</span><a class="landing-button" href="{{ $primaryButtonUrl }}">{{ $data['button_label'] }} @include('pages.landing.icon', ['name' => $buttonIcon])</a></div>@endif
</body>
</html>
