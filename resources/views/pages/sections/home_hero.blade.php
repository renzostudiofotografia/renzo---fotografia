<section data-editor-section="home_hero" class="hero" data-interval="{{ $site->get('home_hero.interval') }}" aria-label="Historias de boda destacadas" style="{{ $site->sectionStyle('home_hero') }}">
    <div class="hero-slides" aria-hidden="true">
        @foreach($site->get('home_hero.images') as $slide)
            <div class="hero-slide {{ $loop->first ? 'active' : '' }}" style="background-image: url('{{ asset($slide['image']) }}')"></div>
        @endforeach
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <p class="hero-subtitle" data-phrases="{{ json_encode($site->get('home_hero.phrases'), JSON_UNESCAPED_UNICODE) }}">{{ $site->get('home_hero.phrases.0') }}</p>
        <a href="{{ $site->get('home_hero.button_url') }}" class="hero-btn">{{ $site->get('home_hero.field_2') }}</a>
    </div>
    <div class="hero-dots" aria-label="Seleccionar fotografía del carrusel">
        @foreach($site->get('home_hero.images') as $slide)
            <button class="dot {{ $loop->first ? 'active' : '' }}" type="button" aria-label="Fotografía {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"></button>
        @endforeach
    </div>
</section>
