@extends('layouts.app')

@section('title', 'Contacto')

@section('content')
<section class="page-header">
    <div class="page-header-container">
        <p class="section-tag">Disponibilidad & Cotizaciones</p>
        <h1 class="page-title">Inmortalicemos su Historia</h1>
        <p class="page-subtitle">Cada boda es un universo único. Tómense un momento para compartir sus planes y me pondré en contacto con ustedes en menos de 24 horas.</p>
    </div>
</section>

<section class="contact-section">
    <div class="contact-container">
        <div class="contact-form-wrapper">
            @if(session('success'))
                <div class="form-alert form-alert-success" role="status">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="form-alert form-alert-error" role="alert">Revisa los campos marcados antes de enviar la consulta.</div>
            @endif

            <form class="contact-form" action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="couple_names">Nombres de la pareja *</label>
                        <input type="text" id="couple_names" name="couple_names" value="{{ old('couple_names') }}" placeholder="Ej. Lucía & Mateo" required autocomplete="name">
                        @error('couple_names')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="email">Correo electrónico *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="hola@ejemplo.com" required autocomplete="email">
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Teléfono / WhatsApp *</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="{{ config('site.phone_display') }}" required autocomplete="tel">
                        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="event_date">Fecha de la boda / evento *</label>
                        <input type="date" id="event_date" name="event_date" value="{{ old('event_date') }}" min="{{ now()->toDateString() }}" required>
                        @error('event_date')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Ciudad / Lugar de la celebración *</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}" placeholder="Ej. Valle Sagrado, Cusco" required>
                        @error('location')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="service">Tipo de experiencia *</label>
                        <select id="service" name="service" required>
                            <option value="" disabled {{ old('service') ? '' : 'selected' }}>Selecciona una opción</option>
                            <option value="wedding" @selected(old('service') === 'wedding')>Cobertura Completa de Boda</option>
                            <option value="prewedding" @selected(old('service') === 'prewedding')>Sesión Preboda / Postboda</option>
                            <option value="elopement" @selected(old('service') === 'elopement')>Elopement / Boda Íntima</option>
                            <option value="destination" @selected(old('service') === 'destination')>Boda de Destino</option>
                        </select>
                        @error('service')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="message">Cuéntame sobre ustedes y su visión de ese día *</label>
                    <textarea id="message" name="message" rows="5" placeholder="Estilo de la boda, qué valoran de la fotografía o cualquier detalle especial..." required>{{ old('message') }}</textarea>
                    @error('message')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="submit-btn">Enviar Consulta de Reserva</button>
            </form>
        </div>

        <aside class="contact-info-wrapper">
            <div class="info-card">
                <h2 class="info-title">Contacto Directo</h2>
                <p class="info-text">Si prefieren una atención inmediata o tienen dudas generales, pueden comunicarse directamente:</p>
                <ul class="info-list">
                    <li><strong>Email</strong><a href="mailto:{{ config('site.email') }}">{{ config('site.email') }}</a></li>
                    <li><strong>WhatsApp</strong><a href="https://wa.me/{{ config('site.phone_link') }}" target="_blank" rel="noopener noreferrer">{{ config('site.phone_display') }}</a></li>
                    <li><strong>Ubicación Base</strong><span>{{ config('site.location') }} — disponible para viajes.</span></li>
                </ul>
            </div>

            <div class="faq-card">
                <h2 class="info-title">Preguntas Frecuentes</h2>
                <div class="faq-item"><h3 class="faq-question">¿Con cuánta anticipación debemos reservar?</h3><p class="faq-answer">Se recomienda reservar entre 6 y 12 meses antes, especialmente para fechas de temporada alta.</p></div>
                <div class="faq-item"><h3 class="faq-question">¿Realizas coberturas fuera de Arequipa?</h3><p class="faq-answer">Sí. Estoy disponible para viajar por todo el Perú y documentar bodas de destino en el extranjero.</p></div>
                <div class="faq-item"><h3 class="faq-question">¿Cuándo entregas las fotografías?</h3><p class="faq-answer">Entrego un adelanto de 20 a 30 fotografías en 48 horas y la galería completa en 4 a 6 semanas.</p></div>
            </div>
        </aside>
    </div>
</section>
@endsection
