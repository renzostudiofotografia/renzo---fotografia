<section data-editor-section="services_services_featured" class="services-featured services-page" style="{{ $site->sectionStyle('services_services_featured') }}">
    <div class="services-container">
        <div class="services-grid">
            @foreach($services as $service)
                <article class="service-card">
                    <div class="service-img-wrapper">
                        <img src="{{ asset($service['image']) }}" alt="{{ $service['image_alt'] }}">
                        <span class="service-number">{{ $service['number'] }}</span>
                    </div>
                    <div class="service-content">
                        <p class="section-tag">{{ $service['tag'] }}</p>
                        <h2 class="service-card-title">{{ $service['title'] }}</h2>
                        <p class="service-description">{{ $service['hero'] }}</p>
                        <a class="service-link" href="{{ route('services.show', $service['slug']) }}">{{ $site->get('services_services_featured.field_1') }}<span aria-hidden="true">→</span></a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
