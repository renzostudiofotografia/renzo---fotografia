<section data-editor-section="home_services_featured" class="services-featured" style="{{ $site->sectionStyle('home_services_featured') }}">
    <div class="services-container">
        <header class="services-header">
            <p class="section-tag">{{ $site->get('home_services_featured.field_1') }}</p>
            <h2 class="services-title">{{ $site->get('home_services_featured.field_2') }}</h2>
        </header>
        <div class="services-grid">
            @foreach($services as $service)
                <article class="service-card">
                    <div class="service-img-wrapper">
                        <img src="{{ asset($service['image']) }}" alt="{{ $service['image_alt'] }}">
                        <span class="service-number">{{ $service['number'] }}</span>
                    </div>
                    <div class="service-content">
                        <h3 class="service-card-title">{{ $service['title'] }}</h3>
                        <p class="service-description">{{ $service['summary'] }}</p>
                        <a href="{{ route('services.show', $service['slug']) }}" class="service-link">{{ $site->get('home_services_featured.field_3') }}<span aria-hidden="true">→</span></a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
