<section data-editor-section="about_about_personal" class="about-personal" style="{{ $site->sectionStyle('about_about_personal') }}">
    <div class="personal-container">
        <div class="personal-text">
            <p class="section-tag">{{ $site->get('about_about_personal.field_3') }}</p>
            <h2 class="personal-title">{{ $site->get('about_about_personal.field_4') }}</h2>
            <ul class="personal-list">
                <li><strong>{{ $site->get('about_about_personal.field_5') }}</strong>{{ $site->get('about_about_personal.field_6') }}</li>
                <li><strong>{{ $site->get('about_about_personal.field_7') }}</strong>{{ $site->get('about_about_personal.field_8') }}</li>
                <li><strong>{{ $site->get('about_about_personal.field_9') }}</strong>{{ $site->get('about_about_personal.field_10') }}</li>
            </ul>
        </div>
        <div class="personal-image-frame"><img src="{{ asset($site->get('about_about_personal.field_1')) }}" alt="{{ $site->get('about_about_personal.field_2') }}"></div>
    </div>
</section>
