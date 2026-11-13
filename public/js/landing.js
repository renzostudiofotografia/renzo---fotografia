document.addEventListener('DOMContentLoaded', () => {
    const configurationElement = document.querySelector('#landing-configuration');
    if (!configurationElement) {
        return;
    }

    const configuration = JSON.parse(configurationElement.textContent);
    const pixelId = String(configuration.pixelId || '');
    const hasPixel = !configuration.preview && /^\d{5,25}$/.test(pixelId);
    const trackedConversions = new Set();
    const pageTitle = String(configuration.contentName || '').slice(0, 300);
    const sendPixelEvent = (...parameters) => {
        try {
            if (typeof window.fbq === 'function') {
                window.fbq(...parameters);
            }
        } catch {
            // A blocked tracker must not interrupt the contact form.
        }
    };
    const storage = {
        get(key) {
            try {
                return window.sessionStorage.getItem(key);
            } catch {
                return null;
            }
        },
        set(key, value) {
            try {
                window.sessionStorage.setItem(key, value);
            } catch {
                // Submission and tracking also work when browser storage is unavailable.
            }
        },
    };

    if (hasPixel) {
        if (!window.fbq) {
            const pixel = function (...parameters) {
                if (pixel.callMethod) {
                    pixel.callMethod(...parameters);
                } else {
                    pixel.queue.push(parameters);
                }
            };
            window.fbq = pixel;
            window._fbq = window._fbq || pixel;
            pixel.push = pixel;
            pixel.loaded = true;
            pixel.version = '2.0';
            pixel.queue = [];
            const script = document.createElement('script');
            script.async = true;
            script.src = 'https://connect.facebook.net/en_US/fbevents.js';
            document.head.append(script);
        }

        sendPixelEvent('set', 'autoConfig', false, pixelId);
        sendPixelEvent('init', pixelId);
        sendPixelEvent('trackSingle', pixelId, 'PageView');
        if (configuration.viewContent) {
            sendPixelEvent('trackSingle', pixelId, 'ViewContent', {
                content_name: pageTitle,
                content_ids: [String(configuration.pageId)],
                content_type: 'product',
                currency: 'PEN',
                value: Number(configuration.contentValue) || 0,
            });
        }
    }

    const trackLead = (conversion) => {
        const eventId = conversion?.event_id;
        const tracking = conversion?.tracking;
        if (!hasPixel || !configuration.lead || !tracking?.lead || tracking.pixel_id !== pixelId || !eventId) {
            return;
        }
        const storageKey = `landing-conversion:${configuration.pageId}:${eventId}`;
        if (trackedConversions.has(eventId) || storage.get(storageKey)) {
            return;
        }
        trackedConversions.add(eventId);
        storage.set(storageKey, '1');
        sendPixelEvent('trackSingle', pixelId, 'Lead', {
            content_name: pageTitle,
            currency: 'PEN',
            value: Number(tracking.value) || 0,
        }, { eventID: eventId });
    };

    if (configuration.conversion) {
        trackLead(configuration.conversion);
    }

    const attributionFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'fbclid'];
    const attributionKey = `landing-attribution:${configuration.pageId}`;
    const query = new URLSearchParams(window.location.search);
    let attribution = {};
    try {
        attribution = JSON.parse(storage.get(attributionKey) || '{}');
    } catch {
        attribution = {};
    }
    if (attribution === null || typeof attribution !== 'object' || Array.isArray(attribution)) {
        attribution = {};
    }
    attributionFields.forEach((field) => {
        if (query.has(field)) {
            attribution[field] = query.get(field).slice(0, field === 'fbclid' ? 500 : 200);
        }
        document.querySelectorAll(`[data-attribution="${field}"]`).forEach((input) => {
            input.value = attribution[field] || input.value || '';
        });
    });
    if (!configuration.preview) {
        storage.set(attributionKey, JSON.stringify(attribution));
    }

    document.querySelectorAll('[data-lead-form]').forEach((form) => {
        let submitting = false;
        let completed = false;
        const status = form.querySelector('[data-form-status]');
        const button = form.querySelector('[type="submit"]');
        const buttonLabel = button.querySelector('[data-submit-label]');
        const originalLabel = buttonLabel.textContent;

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (configuration.preview || submitting || completed) {
                return;
            }
            if (!form.reportValidity()) {
                return;
            }
            submitting = true;
            button.disabled = true;
            buttonLabel.textContent = 'Enviando…';
            form.setAttribute('aria-busy', 'true');
            status.hidden = true;
            status.classList.remove('is-success');
            form.querySelectorAll('[data-error-for]').forEach((element) => { element.textContent = ''; });
            form.querySelectorAll('[aria-invalid]').forEach((element) => { element.removeAttribute('aria-invalid'); });

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form),
                    credentials: 'same-origin',
                });
                const responseType = response.headers.get('content-type') || '';
                const result = responseType.includes('application/json') ? await response.json() : {};
                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        Object.entries(result.errors).forEach(([field, messages]) => {
                            const error = [...form.querySelectorAll('[data-error-for]')].find((element) => element.dataset.errorFor === field);
                            const input = form.elements.namedItem(field);
                            if (error) {
                                error.textContent = Array.isArray(messages) ? messages[0] : String(messages);
                            }
                            if (input instanceof HTMLElement) {
                                input.setAttribute('aria-invalid', 'true');
                            }
                        });
                        status.textContent = 'Revisa los campos señalados para enviar tu consulta.';
                        form.querySelector('[aria-invalid="true"]')?.focus();
                    } else if (response.status === 429) {
                        status.textContent = 'Has enviado varias consultas. Espera unos minutos y vuelve a intentarlo.';
                    } else if (response.status === 419) {
                        status.textContent = 'Tu sesión venció. Recarga la página antes de volver a enviar tu consulta.';
                    } else {
                        status.textContent = 'No pudimos guardar tu consulta. Vuelve a intentarlo en un momento.';
                    }
                    status.hidden = false;
                    return;
                }
                completed = true;
                status.textContent = result.message || 'Recibimos tu consulta. Nos pondremos en contacto contigo.';
                status.classList.add('is-success');
                status.hidden = false;
                buttonLabel.textContent = 'Consulta enviada';
                if (result.created === true) {
                    trackLead(result);
                }
                status.tabIndex = -1;
                status.focus({ preventScroll: true });
                status.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch {
                status.textContent = 'No pudimos conectar. Revisa tu conexión y vuelve a intentarlo; tus datos siguen aquí.';
                status.hidden = false;
            } finally {
                submitting = false;
                form.removeAttribute('aria-busy');
                button.disabled = completed;
                if (!completed) {
                    buttonLabel.textContent = originalLabel;
                }
            }
        });
    });

    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    const updatePageVisibility = () => document.body.classList.toggle('page-is-hidden', document.hidden);
    document.addEventListener('visibilitychange', updatePageVisibility);
    updatePageVisibility();

    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const slides = [...carousel.querySelectorAll('[data-carousel-slide]')];
        if (!track || slides.length === 0) return;

        const controls = carousel.querySelector('[data-carousel-controls]');
        const dots = [...carousel.querySelectorAll('[data-carousel-dot]')];
        const pauseButton = carousel.querySelector('[data-carousel-pause]');
        const autoplay = carousel.dataset.autoplay === '1';
        const interval = Math.min(12, Math.max(3, Number(carousel.dataset.interval) || 5)) * 1000;
        const announcement = document.createElement('p');
        announcement.className = 'visually-hidden';
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        carousel.append(announcement);
        let current = 0;
        let timer;
        let manuallyPaused = false;
        let hovered = false;
        let focused = false;
        let visible = false;
        let scrollFrame;

        const selectCurrent = (index) => {
            current = Math.min(slides.length - 1, Math.max(0, index));
            slides.forEach((slide, slideIndex) => {
                const selected = slideIndex === current;
                slide.classList.toggle('is-current', selected);
                slide.setAttribute('aria-hidden', String(!selected));
            });
            dots.forEach((dot, dotIndex) => dot.setAttribute('aria-pressed', String(dotIndex === current)));
        };

        const schedule = () => {
            window.clearTimeout(timer);
            const temporarilyPaused = hovered || focused || !visible || document.hidden || motionPreference.matches;
            carousel.classList.toggle('is-paused', temporarilyPaused || manuallyPaused);
            if (pauseButton) {
                pauseButton.hidden = !autoplay || motionPreference.matches || slides.length < 2;
                pauseButton.textContent = manuallyPaused ? '▶' : 'Ⅱ';
                pauseButton.setAttribute('aria-label', manuallyPaused ? 'Reanudar reproducción automática' : 'Pausar reproducción automática');
            }
            if (!autoplay || slides.length < 2 || temporarilyPaused || manuallyPaused) return;
            timer = window.setTimeout(() => navigate(current + 1, false), interval);
        };

        const navigate = (index, manual = true) => {
            const destination = (index + slides.length) % slides.length;
            const wraps = index < 0 || index >= slides.length;
            if (manual) manuallyPaused = true;
            selectCurrent(destination);
            track.scrollTo({ left: destination * track.clientWidth, behavior: motionPreference.matches || wraps ? 'auto' : 'smooth' });
            if (manual) announcement.textContent = `Fotografía ${destination + 1} de ${slides.length}`;
            schedule();
        };

        if (controls) controls.hidden = slides.length < 2;
        carousel.querySelector('[data-carousel-prev]')?.addEventListener('click', () => navigate(current - 1));
        carousel.querySelector('[data-carousel-next]')?.addEventListener('click', () => navigate(current + 1));
        dots.forEach((dot, index) => dot.addEventListener('click', () => navigate(index)));
        pauseButton?.addEventListener('click', () => {
            manuallyPaused = !manuallyPaused;
            schedule();
        });
        track.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            navigate(event.key === 'Home' ? 0 : event.key === 'End' ? slides.length - 1 : current + (event.key === 'ArrowLeft' ? -1 : 1));
        });
        const pauseForInteraction = () => {
            manuallyPaused = true;
            schedule();
        };
        track.addEventListener('pointerdown', pauseForInteraction, { passive: true });
        track.addEventListener('wheel', pauseForInteraction, { passive: true });
        track.addEventListener('scroll', () => {
            window.cancelAnimationFrame(scrollFrame);
            scrollFrame = window.requestAnimationFrame(() => {
                if (track.clientWidth) selectCurrent(Math.round(track.scrollLeft / track.clientWidth));
            });
        }, { passive: true });
        carousel.addEventListener('pointerenter', () => { hovered = true; schedule(); });
        carousel.addEventListener('pointerleave', () => { hovered = false; schedule(); });
        carousel.addEventListener('focusin', () => { focused = true; schedule(); });
        carousel.addEventListener('focusout', () => {
            window.requestAnimationFrame(() => { focused = carousel.contains(document.activeElement); schedule(); });
        });
        document.addEventListener('visibilitychange', schedule);
        motionPreference.addEventListener('change', schedule);
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((entries) => {
                visible = entries[0].isIntersecting;
                schedule();
            }, { threshold: 0.15 }).observe(carousel);
        } else {
            const checkVisibility = () => {
                const bounds = carousel.getBoundingClientRect();
                visible = bounds.bottom > 0 && bounds.top < window.innerHeight;
                schedule();
            };
            window.addEventListener('scroll', checkVisibility, { passive: true });
            checkVisibility();
        }
        const preserveSlideOnResize = () => track.scrollTo({ left: current * track.clientWidth, behavior: 'auto' });
        if ('ResizeObserver' in window) new ResizeObserver(preserveSlideOnResize).observe(track);
        else window.addEventListener('resize', preserveSlideOnResize);
        selectCurrent(0);
        schedule();
    });

    document.querySelectorAll('video[data-autoplay-video]').forEach((video) => {
        let visible = false;
        let userPaused = false;
        const updatePlayback = () => {
            if (!visible || document.hidden || motionPreference.matches) {
                video.pause();
                return;
            }
            if (userPaused) return;
            video.muted = true;
            video.play().catch(() => {});
        };
        video.addEventListener('pause', () => {
            if (visible && !document.hidden && !motionPreference.matches) userPaused = true;
        });
        video.addEventListener('play', () => { userPaused = false; });
        document.addEventListener('visibilitychange', updatePlayback);
        motionPreference.addEventListener('change', updatePlayback);
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((entries) => {
                visible = entries[0].isIntersecting;
                updatePlayback();
            }, { threshold: 0.3 }).observe(video);
        } else {
            const checkVisibility = () => {
                const bounds = video.getBoundingClientRect();
                visible = bounds.bottom > 0 && bounds.top < window.innerHeight;
                updatePlayback();
            };
            window.addEventListener('scroll', checkVisibility, { passive: true });
            checkVisibility();
        }
    });

    const floatingCta = document.querySelector('[data-floating-cta]');
    if (floatingCta) {
        let formVisible = false;
        let footerVisible = false;
        const updateFloatingCta = () => {
            const editingForm = document.activeElement?.matches('input:not([type=hidden]), textarea, select');
            floatingCta.hidden = formVisible || footerVisible || Boolean(editingForm);
        };
        document.addEventListener('focusin', updateFloatingCta);
        document.addEventListener('focusout', () => window.requestAnimationFrame(updateFloatingCta));
        if ('IntersectionObserver' in window) {
            const form = document.querySelector('[data-lead-form]');
            const footer = document.querySelector('.landing-footer');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.target === form) formVisible = entry.isIntersecting;
                    if (entry.target === footer) footerVisible = entry.isIntersecting;
                });
                updateFloatingCta();
            }, { threshold: 0.08 });
            if (form) observer.observe(form);
            if (footer) observer.observe(footer);
        }
        updateFloatingCta();
    }

    if (!motionPreference.matches && 'IntersectionObserver' in window && !document.body.classList.contains('animation-none')) {
        document.body.classList.add('motion-enabled');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.05 });
        document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));
    }
});
