<footer class="footer" data-editor-section="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <h2 class="footer-logo">{{ mb_strtoupper($site->get('branding.brand')) }}</h2>
            <p class="footer-tagline">{{ $site->get('footer.tagline') }}</p>
            <p class="footer-bio">{{ $site->get('footer.bio') }}</p>
        </div>

        <div class="footer-links">
            <h3 class="footer-heading">{{ $site->get('footer.navigation_heading') }}</h3>
            <ul>
                <li><a href="{{ route('home') }}">{{ $site->get('navigation.home') }}</a></li>
                <li><a href="{{ route('portfolio') }}">{{ $site->get('navigation.portfolio') }}</a></li>
                <li><a href="{{ route('services') }}">{{ $site->get('navigation.services') }}</a></li>
                <li><a href="{{ route('about') }}">{{ $site->get('navigation.about') }}</a></li>
                <li><a href="{{ route('contact') }}">{{ $site->get('navigation.contact') }}</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h3 class="footer-heading">{{ $site->get('footer.contact_heading') }}</h3>
            <p>{{ $site->get('branding.location') }} — {{ $site->get('footer.availability') }}</p>
            <p><a href="mailto:{{ $site->get('branding.email') }}">{{ $site->get('branding.email') }}</a></p>
            <div class="footer-socials">
                <a href="{{ $site->get('branding.instagram') }}" target="_blank" rel="noopener noreferrer">Instagram</a>
                <span class="social-divider">•</span>
                <a href="https://wa.me/{{ $site->get('branding.phone_link') }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <p class="copyright">© {{ date('Y') }} {{ $site->get('branding.brand') }} {{ $site->get('footer.copyright') }}</p>
            <p class="credits">Desarrollado por <span>{{ $site->get('footer.credits') }}</span></p>
        </div>
    </div>
</footer>
