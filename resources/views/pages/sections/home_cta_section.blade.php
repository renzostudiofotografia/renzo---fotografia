<section data-editor-section="home_cta_section" class="cta-section" style="{{ $site->sectionStyle('home_cta_section') }};background-image: url('{{ asset($site->get('home_cta_section.background')) }}')">
    <div class="cta-overlay"></div>
    <div class="cta-container">
        <p class="section-tag">{{ $site->get('home_cta_section.field_1') }}</p>
        <h2 class="cta-title">{{ $site->get('home_cta_section.field_2') }}</h2>
        <p class="cta-description">{{ $site->get('home_cta_section.field_3') }}</p>
        <div class="cta-buttons">
            <a class="cta-btn primary-btn" href="{{ $site->get('home_cta_section.field_6') }}">{{ $site->get('home_cta_section.field_4') }}</a>
            <a class="cta-btn secondary-btn" href="https://wa.me/{{ $site->get('branding.phone_link') }}" target="_blank" rel="noopener noreferrer">{{ $site->get('home_cta_section.field_5') }}</a>
        </div>
    </div>
</section>
