<section data-editor-section="home_experience" class="experience" aria-label="Experiencia profesional" style="{{ $site->sectionStyle('home_experience') }}">
    <div class="experience-container">
        <div class="experience-stats">
            <div class="stat-item"><span class="stat-number">{{ $site->get('home_experience.weddings') }}</span><span class="stat-label">{{ $site->get('home_experience.field_1') }}</span></div>
            <div class="stat-divider"></div>
            <div class="stat-item"><span class="stat-number">{{ $site->get('home_experience.years') }}</span><span class="stat-label">{{ $site->get('home_experience.field_2') }}</span></div>
        </div>
        <div class="experience-separator"></div>
        <div class="experience-quote">
            <blockquote class="quote-text">{{ $site->get('home_experience.field_3') }}</blockquote>
            <cite class="quote-author">{{ $site->get('home_experience.field_4') }}</cite>
        </div>
    </div>
</section>
