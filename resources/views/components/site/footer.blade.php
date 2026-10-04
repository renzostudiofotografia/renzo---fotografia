<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <h2 class="footer-logo">{{ mb_strtoupper(config('site.brand')) }}</h2>
            <p class="footer-tagline">Fotografía Editorial de Bodas</p>
            <p class="footer-bio">Capturando la esencia espontánea, atemporal y emotiva de tu historia de amor en Perú y destinos internacionales.</p>
        </div>

        <div class="footer-links">
            <h3 class="footer-heading">Navegación</h3>
            <ul>
                <li><a href="{{ route('home') }}">Inicio</a></li>
                <li><a href="{{ route('portfolio') }}">Portafolio</a></li>
                <li><a href="{{ route('services') }}">Servicios</a></li>
                <li><a href="{{ route('about') }}">Sobre Mí</a></li>
                <li><a href="{{ route('contact') }}">Contáctanos</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h3 class="footer-heading">Contacto</h3>
            <p>{{ config('site.location') }} — Disponible Worldwide</p>
            <p><a href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a></p>
            <div class="footer-socials">
                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer">Instagram</a>
                <span class="social-divider">•</span>
                <a href="https://wa.me/{{ config('site.phone_link') }}" target="_blank" rel="noopener noreferrer">WhatsApp</a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <p class="copyright">© {{ date('Y') }} {{ config('site.brand') }} Photography. Todos los derechos reservados.</p>
            <p class="credits">Desarrollado por <span>Grupo JHALE</span></p>
        </div>
    </div>
</footer>
