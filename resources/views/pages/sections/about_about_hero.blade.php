<section data-editor-section="about_about_hero" class="about-hero" style="{{ $site->sectionStyle('about_about_hero') }}">
    <div class="about-hero-container">
        <div class="about-hero-image">
            <img src="{{ asset($site->get('about_about_hero.field_1')) }}" alt="{{ $site->get('branding.brand') }}, fotógrafo de bodas">
        </div>
        <div class="about-hero-text">
            <p class="section-tag">{{ $site->get('about_about_hero.field_2') }}</p>
            <h1 class="about-hero-title">{{ $site->get('about_about_hero.field_3') }}</h1>
            <p class="about-hero-subtitle">{{ $site->get('about_about_hero.field_4') }}</p>
            <div class="about-hero-divider"></div>
            <p class="about-hero-bio">{{ $site->get('about_about_hero.field_5') }}</p>
        </div>
    </div>
</section>
