<div class="booking-container">
    <div class="booking-intro">
        <p class="section-tag">{{ $site->get('booking.tag') }}</p>
        <{{ request()->routeIs('booking') ? 'h1' : 'h2' }} class="booking-title">{{ $site->get('booking.title') }}</{{ request()->routeIs('booking') ? 'h1' : 'h2' }}>
        <p>{{ $site->get('booking.description') }}</p>
    </div>
    @if($site->get('booking.url'))
        <div class="calendly-inline-widget" data-url="{{ $site->get('booking.url') }}?primary_color={{ ltrim($site->get('branding.gold'), '#') }}&background_color={{ ltrim($site->get('branding.background'), '#') }}&text_color={{ ltrim($site->get('branding.text'), '#') }}" style="min-width:280px;height:720px;"></div>
        <p class="booking-fallback">¿No se carga la agenda? <a href="{{ $site->get('booking.url') }}" target="_blank" rel="noopener noreferrer">{{ $site->get('booking.button') }}</a></p>
        @once
            @push('scripts')
                <script src="https://assets.calendly.com/assets/external/widget.js" async></script>
            @endpush
        @endonce
    @else
        <div class="booking-empty">
            <p>{{ $site->get('booking.empty_text') }}</p>
            <a class="cta-btn primary-btn" href="{{ route('contact') }}">{{ $site->get('booking.button') }}</a>
        </div>
    @endif
</div>
