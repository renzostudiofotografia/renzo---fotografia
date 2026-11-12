<section data-editor-section="home_testimonials" class="testimonials" aria-labelledby="testimonial-title" style="{{ $site->sectionStyle('home_testimonials') }}">
    <div class="testimonials-container">
        <header class="testimonials-header">
            <p class="section-tag">{{ $site->get('home_testimonials.field_1') }}</p>
            <h2 class="testimonials-title" id="testimonial-title">{{ $site->get('home_testimonials.field_2') }}</h2>
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
