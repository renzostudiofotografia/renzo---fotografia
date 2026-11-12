@php
    $fonts = config('editor.fonts');
    $fontFamilies = array_unique([$fonts[$data['heading_font']], $fonts[$data['body_font']]]);
    $leadSection = collect($data['sections'])->first(fn (array $section): bool => $section['type'] === 'lead' && $section['enabled']);
    $conversion = session('landing_conversion');
    $trackingConfiguration = [
        'preview' => $preview,
        'pageId' => $landing->id,
        'pixelId' => $preview ? '' : $data['pixel_id'],
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
    @php $heroImage = collect($data['sections'])->first(fn (array $section): bool => $section['type'] === 'hero' && $section['enabled']); @endphp
    @if(!empty($heroImage['image']))<meta property="og:image" content="{{ asset($heroImage['image']) }}">@endif
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
            --landing-spacing: {{ $data['section_spacing'] }}px;
        }
    </style>
    <script id="landing-configuration" type="application/json">{!! json_encode($trackingConfiguration, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) !!}</script>
    <script src="{{ asset('js/landing.js') }}?v={{ filemtime(public_path('js/landing.js')) }}" defer></script>
</head>
<body class="landing-page button-{{ $data['button_style'] }} animation-{{ $data['animation'] }} {{ $preview ? 'landing-preview' : '' }}" data-landing-id="{{ $landing->id }}">
    <a class="landing-skip" href="#main-content">Saltar al contenido</a>
    <header class="landing-header">
        <a href="#main-content" class="landing-brand" aria-label="{{ $data['brand_name'] }} · inicio">{{ $data['brand_name'] }}<span>FOTOGRAFÍA CON HISTORIA</span></a>
        @if($leadSection)<a class="landing-header-link" href="#contacto">{{ $data['button_label'] }} <span aria-hidden="true">↗</span></a>@endif
    </header>
    <main id="main-content">
    @foreach($data['sections'] as $sectionIndex => $section)
        @continue(!$section['enabled'])
        @continue($section['type'] === 'testimonials' && count($data['testimonials']) === 0)
        @php
            $sectionId = $section['type'] === 'lead' && ! $contactRendered ? 'contacto' : 'section-'.$sectionIndex;
            $sectionButton = $section['button_label'] ?: $data['button_label'];
            $sectionButtonUrl = $section['button_url'] ?: '#contacto';
            $isHero = $section['type'] === 'hero';
            $headingTag = $isHero && ! $heroRendered ? 'h1' : 'h2';
            if ($isHero) { $heroRendered = true; }
            if ($section['type'] === 'lead') { $contactRendered = true; }
        @endphp
        <section id="{{ $sectionId }}" class="landing-section section-{{ $section['type'] }} {{ $isHero ? 'hero-'.$data['hero_layout'] : '' }}" data-landing-section="{{ $sectionIndex }}" aria-labelledby="heading-{{ $sectionIndex }}" style="--section-padding:{{ $section['padding'] }}px;@if($section['custom_background']) background-color:{{ $section['background'] }};@endif">
            <div class="landing-container {{ in_array($section['type'], ['hero', 'lead', 'text']) ? 'landing-split' : '' }} {{ in_array($section['type'], ['hero', 'text']) && !$section['image'] ? 'without-image' : '' }}">
                <div class="section-intro reveal">
                    @if($section['eyebrow'])<p class="landing-eyebrow"><span aria-hidden="true"></span>{{ $section['eyebrow'] }}</p>@endif
                    <{{ $headingTag }} id="heading-{{ $sectionIndex }}">{{ $section['title'] }}</{{ $headingTag }}>
                    @if($section['text'])<p class="landing-description">{{ $section['text'] }}</p>@endif
                    @if($isHero || $section['type'] === 'text')
                        @if($leadSection || $section['button_url'])<a class="landing-button" href="{{ $sectionButtonUrl }}">{{ $sectionButton }} <span aria-hidden="true">↗</span></a>@endif
                        @if($isHero)<p class="hero-caption">FOTOGRAFÍAS PARA VOLVER A SENTIR.</p>@endif
                    @endif
                    @if($section['type'] === 'lead')
                        <div class="contact-assurance"><span aria-hidden="true">↗</span><p>Cuéntanos lo que tienes en mente.<br>Conversemos sobre los detalles de tu evento.</p></div>
                        @if($data['whatsapp'])<a class="landing-text-link" href="https://wa.me/{{ $data['whatsapp'] }}" target="_blank" rel="noopener noreferrer">También puedes escribir por WhatsApp <span aria-hidden="true">↗</span></a>@endif
                        @if($section['image'])<img class="contact-image" src="{{ asset($section['image']) }}" alt="{{ $section['alt'] }}" loading="lazy" width="720" height="500">@endif
                    @endif
                </div>

                @if($isHero || $section['type'] === 'text')
                    @if($section['image'])<figure class="hero-visual reveal"><img src="{{ asset($section['image']) }}" alt="{{ $section['alt'] }}" width="1200" height="1500" @if($isHero) fetchpriority="high" @else loading="lazy" @endif><figcaption><span>{{ $data['brand_name'] }}</span><span aria-hidden="true">01 / HISTORIAS</span></figcaption></figure>@endif
                @elseif($section['type'] === 'benefits')
                    <div class="benefits-grid">@foreach($data['benefits'] as $benefit)<article class="benefit-item reveal"><span class="benefit-number" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3>{{ $benefit['title'] }}</h3><p>{{ $benefit['text'] }}</p></article>@endforeach</div>
                @elseif($section['type'] === 'gallery')
                    <div class="landing-gallery">@foreach($data['gallery'] as $image)<figure class="gallery-image reveal"><img src="{{ asset($image['image']) }}" alt="{{ $image['alt'] }}" loading="lazy" width="900" height="1100">@if($image['alt'])<figcaption>{{ $image['alt'] }}</figcaption>@endif</figure>@endforeach</div>
                @elseif($section['type'] === 'offer')
                    <div class="offer-panel reveal"><div><span class="landing-eyebrow">{{ $data['offer_label'] }}</span>@if((float) $data['price'] > 0)<p class="offer-price"><span>S/</span> {{ number_format((float) $data['price'], 2, '.', ',') }}</p>@else<p class="offer-price price-on-request">Una propuesta a su medida</p>@endif @if($data['price_note'])<p class="offer-note">{{ $data['price_note'] }}</p>@endif</div>@if($leadSection || $section['button_url'])<a class="landing-button" href="{{ $sectionButtonUrl }}">{{ $sectionButton }} <span aria-hidden="true">↗</span></a>@endif</div>
                @elseif($section['type'] === 'testimonials')
                    <div class="testimonials-grid">@foreach($data['testimonials'] as $testimonial)<figure class="testimonial-item reveal"><span class="quote-mark" aria-hidden="true">“</span><blockquote>{{ $testimonial['quote'] }}</blockquote><figcaption>{{ $testimonial['name'] }}</figcaption></figure>@endforeach</div>
                @elseif($section['type'] === 'faq')
                    <div class="landing-faq">@foreach($data['faqs'] as $faq)<details class="faq-item reveal"><summary>{{ $faq['question'] }}<span aria-hidden="true">+</span></summary><p>{{ $faq['answer'] }}</p></details>@endforeach</div>
                @elseif($section['type'] === 'lead')
                    <form class="landing-form reveal" action="{{ route('landing.lead', $landing->slug) }}" method="POST" data-lead-form @if($preview) data-preview-form @endif>
                        @csrf
                        <input type="hidden" name="submission_id" value="{{ old('submission_id', (string) \Illuminate\Support\Str::uuid()) }}">
                        @foreach(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid'] as $attribution)<input type="hidden" name="{{ $attribution }}" value="{{ old($attribution, '') }}" data-attribution="{{ $attribution }}">@endforeach
                        <div class="form-honeypot" aria-hidden="true"><label for="company-{{ $sectionIndex }}">Empresa<input type="text" id="company-{{ $sectionIndex }}" name="company" autocomplete="off" tabindex="-1"></label></div>
                        <div class="form-status {{ session('landing_success') ? 'is-success' : '' }}" data-form-status role="status" aria-live="polite" @if(!session('landing_success') && !$errors->any()) hidden @endif>@if(session('landing_success')){{ session('landing_success') }}@elseif($errors->any())Revisa los datos del formulario e inténtalo de nuevo.@endif</div>
                        <div class="landing-form-fields">
                            <label for="name-{{ $sectionIndex }}">Tu nombre <span aria-hidden="true">*</span><input type="text" id="name-{{ $sectionIndex }}" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" placeholder="¿Cómo te llamas?" required aria-describedby="error-name-{{ $sectionIndex }}"><span id="error-name-{{ $sectionIndex }}" class="field-error" data-error-for="name">@error('name'){{ $message }}@enderror</span></label>
                            <label for="phone-{{ $sectionIndex }}">WhatsApp o teléfono <span aria-hidden="true">*</span><input type="tel" id="phone-{{ $sectionIndex }}" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" maxlength="30" placeholder="+51 999 999 999" required aria-describedby="error-phone-{{ $sectionIndex }}"><span id="error-phone-{{ $sectionIndex }}" class="field-error" data-error-for="phone">@error('phone'){{ $message }}@enderror</span></label>
                            <label for="email-{{ $sectionIndex }}">Correo electrónico <span class="optional">Opcional</span><input type="email" id="email-{{ $sectionIndex }}" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="160" placeholder="tu@correo.com" aria-describedby="error-email-{{ $sectionIndex }}"><span id="error-email-{{ $sectionIndex }}" class="field-error" data-error-for="email">@error('email'){{ $message }}@enderror</span></label>
                            <label for="event-date-{{ $sectionIndex }}">Fecha de tu evento <span class="optional">Opcional</span><input type="date" id="event-date-{{ $sectionIndex }}" name="event_date" value="{{ old('event_date') }}" min="{{ now()->toDateString() }}" aria-describedby="error-event-date-{{ $sectionIndex }}"><span id="error-event-date-{{ $sectionIndex }}" class="field-error" data-error-for="event_date">@error('event_date'){{ $message }}@enderror</span></label>
                            <label class="form-full" for="message-{{ $sectionIndex }}">Cuéntanos un poco más <span class="optional">Opcional</span><textarea id="message-{{ $sectionIndex }}" name="message" rows="3" maxlength="2000" placeholder="Lugar, tipo de celebración y lo que imaginas…" aria-describedby="error-message-{{ $sectionIndex }}">{{ old('message') }}</textarea><span id="error-message-{{ $sectionIndex }}" class="field-error" data-error-for="message">@error('message'){{ $message }}@enderror</span></label>
                        </div>
                        <label class="consent-label"><input type="checkbox" name="consent" value="1" @checked(old('consent')) required aria-describedby="error-consent-{{ $sectionIndex }}"><span>{{ $data['privacy_text'] }}</span></label>
                        <span id="error-consent-{{ $sectionIndex }}" class="field-error" data-error-for="consent">@error('consent'){{ $message }}@enderror</span>
                        <button class="landing-button form-submit" type="submit" @disabled($preview)><span data-submit-label>{{ $data['submit_label'] }}</span><span aria-hidden="true">↗</span></button>
                        <p class="form-footnote">{{ $preview ? 'Vista previa · El formulario y el seguimiento están desactivados.' : 'Tus datos se usarán para responder a tu consulta.' }}</p>
                    </form>
                @endif
                @if(!in_array($section['type'], ['hero', 'text', 'offer', 'lead']) && $section['button_label'])
                    <a class="landing-button section-extra-button reveal" href="{{ $sectionButtonUrl }}">{{ $sectionButton }} <span aria-hidden="true">↗</span></a>
                @endif
            </div>
        </section>
    @endforeach
    </main>
    <footer class="landing-footer"><a href="#main-content" class="landing-brand">{{ $data['brand_name'] }}</a><span>Historias que se sienten. Recuerdos que permanecen.</span><small>© {{ now()->year }} · Todos los precios en soles (PEN).</small></footer>
    @if($data['sticky_cta'] && $leadSection)<div class="mobile-cta"><a class="landing-button" href="#contacto">{{ $data['button_label'] }} <span aria-hidden="true">↗</span></a></div>@endif
</body>
</html>
