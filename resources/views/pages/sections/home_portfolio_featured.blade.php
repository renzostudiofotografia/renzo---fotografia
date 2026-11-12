<section data-editor-section="home_portfolio_featured" class="portfolio-featured" id="portafolio" style="{{ $site->sectionStyle('home_portfolio_featured') }}">
    <div class="portfolio-container">
        <header class="portfolio-header">
            <p class="section-tag">{{ $site->get('home_portfolio_featured.field_1') }}</p>
            <h2 class="portfolio-title">{{ $site->get('home_portfolio_featured.field_2') }}</h2>
        </header>
        <div class="portfolio-grid">
            @foreach($portfolioItems as $item)
                <a class="portfolio-item" href="{{ $site->get('home_portfolio_featured.field_4') }}" aria-label="Ver historia de {{ $item['names'] }}">
                    <img src="{{ asset($item['image']) }}" alt="{{ $item['tag'] }} de {{ $item['names'] }}" loading="lazy">
                    <div class="portfolio-overlay"><h3 class="portfolio-item-title">{{ $item['names'] }}</h3></div>
                </a>
            @endforeach
        </div>
        <div class="portfolio-footer"><a class="portfolio-main-btn" href="{{ $site->get('home_portfolio_featured.field_5') }}">{{ $site->get('home_portfolio_featured.field_3') }}</a></div>
    </div>
</section>
