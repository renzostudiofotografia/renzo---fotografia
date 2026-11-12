<section data-editor-section="home_about_philosophy" class="about-philosophy" style="{{ $site->sectionStyle('home_about_philosophy') }}">
    <div class="about-container">
        <div class="about-text">
            <p class="section-tag">{{ $site->get('home_about_philosophy.field_7') }}</p>
            <h2 class="about-title">{{ $site->get('home_about_philosophy.field_8') }}</h2>
            <p class="about-description">{{ $site->get('home_about_philosophy.field_9') }}</p>
            <p class="about-description">{{ $site->get('home_about_philosophy.field_10') }}</p>
            <a class="about-link" href="{{ $site->get('home_about_philosophy.field_12') }}">{{ $site->get('home_about_philosophy.field_11') }}<span aria-hidden="true">→</span></a>
        </div>
        <div class="about-collage" aria-label="Detalles de boda">
            <figure class="collage-frame frame-1"><img src="{{ asset($site->get('home_about_philosophy.field_1')) }}" alt="{{ $site->get('home_about_philosophy.field_4') }}"></figure>
            <figure class="collage-frame frame-2"><img src="{{ asset($site->get('home_about_philosophy.field_2')) }}" alt="{{ $site->get('home_about_philosophy.field_5') }}"></figure>
            <figure class="collage-frame frame-3"><img src="{{ asset($site->get('home_about_philosophy.field_3')) }}" alt="{{ $site->get('home_about_philosophy.field_6') }}"></figure>
        </div>
    </div>
</section>
