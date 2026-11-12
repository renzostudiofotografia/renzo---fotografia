<section data-editor-section="contact_contact_section" class="contact-section" style="{{ $site->sectionStyle('contact_contact_section') }}">
    <div class="contact-container">
        <div class="contact-form-wrapper">
            @if(session('success'))
                <div class="form-alert form-alert-success" role="status">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="form-alert form-alert-error" role="alert">{{ $site->get('contact_contact_section.field_6') }}</div>
            @endif

            <form class="contact-form" action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="couple_names">{{ $site->get('contact_contact_section.field_7') }}</label>
                        <input type="text" id="couple_names" name="couple_names" value="{{ old('couple_names') }}" placeholder="{{ $site->get('contact_contact_section.field_1') }}" required autocomplete="name">
                        @error('couple_names')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="email">{{ $site->get('contact_contact_section.field_8') }}</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="{{ $site->get('contact_contact_section.field_2') }}" required autocomplete="email">
                        @error('email')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">{{ $site->get('contact_contact_section.field_9') }}</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="{{ $site->get('branding.phone_display') }}" required autocomplete="tel">
                        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="event_date">{{ $site->get('contact_contact_section.field_10') }}</label>
                        <input type="date" id="event_date" name="event_date" value="{{ old('event_date') }}" min="{{ now()->toDateString() }}" required>
                        @error('event_date')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="location">{{ $site->get('contact_contact_section.field_11') }}</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}" placeholder="{{ $site->get('contact_contact_section.field_3') }}" required>
                        @error('location')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="service">{{ $site->get('contact_contact_section.field_12') }}</label>
                        <select id="service" name="service" required>
                            <option value="" disabled {{ old('service') ? '' : 'selected' }}>{{ $site->get('contact_contact_section.field_13') }}</option>
                            <option value="wedding" @selected(old('service') === 'wedding')>{{ $site->get('contact_contact_section.field_14') }}</option>
                            <option value="prewedding" @selected(old('service') === 'prewedding')>{{ $site->get('contact_contact_section.field_15') }}</option>
                            <option value="elopement" @selected(old('service') === 'elopement')>{{ $site->get('contact_contact_section.field_16') }}</option>
                            <option value="destination" @selected(old('service') === 'destination')>{{ $site->get('contact_contact_section.field_17') }}</option>
                        </select>
                        @error('service')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="form-group full-width">
                    <label for="message">{{ $site->get('contact_contact_section.field_18') }}</label>
                    <textarea id="message" name="message" rows="5" placeholder="{{ $site->get('contact_contact_section.field_4') }}" required>{{ old('message') }}</textarea>
                    @error('message')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="submit-btn">{{ $site->get('contact_contact_section.field_19') }}</button>
            </form>
        </div>

        <aside class="contact-info-wrapper">
            <div class="info-card">
                <h2 class="info-title">{{ $site->get('contact_contact_section.field_20') }}</h2>
                <p class="info-text">{{ $site->get('contact_contact_section.field_21') }}</p>
                <ul class="info-list">
                    <li><strong>{{ $site->get('contact_contact_section.field_22') }}</strong><a href="mailto:{{ $site->get('branding.email') }}">{{ $site->get('branding.email') }}</a></li>
                    <li><strong>{{ $site->get('contact_contact_section.field_23') }}</strong><a href="https://wa.me/{{ $site->get('branding.phone_link') }}" target="_blank" rel="noopener noreferrer">{{ $site->get('branding.phone_display') }}</a></li>
                    <li><strong>{{ $site->get('contact_contact_section.field_24') }}</strong><span>{{ $site->get('branding.location') }} — disponible para viajes.</span></li>
                </ul>
            </div>

            <div class="faq-card">
                <h2 class="info-title">{{ $site->get('contact_contact_section.field_25') }}</h2>
                <div class="faq-item"><h3 class="faq-question">{{ $site->get('contact_contact_section.field_26') }}</h3><p class="faq-answer">{{ $site->get('contact_contact_section.field_27') }}</p></div>
                <div class="faq-item"><h3 class="faq-question">{{ $site->get('contact_contact_section.field_28') }}</h3><p class="faq-answer">{{ $site->get('contact_contact_section.field_29') }}</p></div>
                <div class="faq-item"><h3 class="faq-question">{{ $site->get('contact_contact_section.field_30') }}</h3><p class="faq-answer">{{ $site->get('contact_contact_section.field_31') }}</p></div>
            </div>
        </aside>
    </div>
</section>
