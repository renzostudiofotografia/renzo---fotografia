<section data-editor-section="portfolio_portfolio_page_section" class="portfolio-page-section" style="{{ $site->sectionStyle('portfolio_portfolio_page_section') }}">
    <div class="portfolio-page-container">
        <div class="portfolio-filter-bar" role="group" aria-label="Filtrar portafolio">
            <button class="filter-btn active" type="button" data-filter="all" aria-pressed="true">{{ $site->get('portfolio_portfolio_page_section.field_1') }}</button>
            <button class="filter-btn" type="button" data-filter="wedding" aria-pressed="false">{{ $site->get('portfolio_portfolio_page_section.field_2') }}</button>
            <button class="filter-btn" type="button" data-filter="prewedding" aria-pressed="false">{{ $site->get('portfolio_portfolio_page_section.field_3') }}</button>
            <button class="filter-btn" type="button" data-filter="elopement" aria-pressed="false">{{ $site->get('portfolio_portfolio_page_section.field_4') }}</button>
        </div>

        <div class="portfolio-gallery-grid" id="portfolio-grid">
            @foreach($portfolioItems as $item)
                <button class="gallery-card" type="button" data-category="{{ $item['category'] }}" data-caption="{{ $item['names'] }} — {{ $item['location'] }}">
                    <img src="{{ asset($item['image']) }}" alt="{{ $item['tag'] }} de {{ $item['names'] }}" loading="lazy">
                    <span class="gallery-card-overlay">
                        <span class="gallery-tag">{{ $item['tag'] }}</span>
                        <span class="gallery-card-title">{{ $item['names'] }}</span>
                        <span class="gallery-card-loc">{{ $item['location'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </div>
</section>
