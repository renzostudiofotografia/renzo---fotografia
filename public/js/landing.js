document.addEventListener('DOMContentLoaded', () => {
    const configurationElement = document.querySelector('#landing-configuration');
    if (!configurationElement) {
        return;
    }

    const configuration = JSON.parse(configurationElement.textContent);
    const pixelId = String(configuration.pixelId || '');
    const hasPixel = !configuration.preview && /^\d{5,25}$/.test(pixelId);
    const ga4Id = String(configuration.ga4Id || '');
    const hasAnalytics = !configuration.preview && /^G-[A-Z0-9]{4,20}$/.test(ga4Id);
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

    if (hasAnalytics) {
        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
        window.gtag('js', new Date());
        window.gtag('config', ga4Id, {
            send_page_view: false,
            page_location: window.location.origin + window.location.pathname,
            page_referrer: '',
            allow_google_signals: false,
            allow_ad_personalization_signals: false,
        });
        if (!document.querySelector('script[data-landing-analytics]')) {
            const script = document.createElement('script');
            script.async = true;
            script.dataset.landingAnalytics = '1';
            script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ga4Id)}`;
            document.head.append(script);
        }
    }

    const trackAnalytics = (eventName, parameters = {}) => {
        if (configuration.preview) return;
        const safeParameters = { landing_id: String(configuration.pageId), ...parameters };
        try {
            if (hasAnalytics && typeof window.gtag === 'function') {
                window.gtag('event', eventName, { send_to: ga4Id, ...safeParameters });
            }
            if (hasPixel && eventName !== 'generate_lead') {
                sendPixelEvent('trackSingleCustom', pixelId, eventName, safeParameters);
            }
        } catch {
            // Analytics never prevent browsing or submitting an inquiry.
        }
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
        if (configuration.preview || !eventId) {
            return;
        }
        const storageKey = `landing-conversion:${configuration.pageId}:${eventId}`;
        if (trackedConversions.has(eventId) || storage.get(storageKey)) {
            return;
        }
        trackedConversions.add(eventId);
        storage.set(storageKey, '1');
        trackAnalytics('generate_lead', { currency: 'PEN', value: Number(tracking?.value) || 0 });
        if (hasPixel && configuration.lead && tracking?.lead && tracking.pixel_id === pixelId) {
            sendPixelEvent('trackSingle', pixelId, 'Lead', {
                content_name: pageTitle,
                currency: 'PEN',
                value: Number(tracking.value) || 0,
            }, { eventID: eventId });
        }
    };

    if (configuration.conversion) {
        trackLead(configuration.conversion);
    }

    trackAnalytics('view_landing');
    document.querySelectorAll('a[href]').forEach((link) => {
        try {
            const destination = new URL(link.href, window.location.href);
            if (['wa.me', 'api.whatsapp.com', 'web.whatsapp.com'].includes(destination.hostname)) {
                link.addEventListener('click', () => trackAnalytics('click_whatsapp'));
            }
        } catch {
            // Invalid external links do not interrupt the rest of the page.
        }
    });
    if ('IntersectionObserver' in window) {
        const contentObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                if (entry.target.matches('[data-experience-index]')) {
                    trackAnalytics('view_experience', { experience_id: entry.target.dataset.experienceIndex });
                } else {
                    trackAnalytics('view_portfolio');
                }
                contentObserver.unobserve(entry.target);
            });
        }, { threshold: 0.15 });
        document.querySelectorAll('[data-analytics-section="portfolio"]').forEach((section) => contentObserver.observe(section.querySelector('.section-intro') || section));
        document.querySelectorAll('[data-experience-index]').forEach((experience) => contentObserver.observe(experience));
    }

    const videoMotionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    const videoPlayers = new Map();
    const pauseVideos = (container) => {
        container.querySelectorAll('video[data-landing-video]').forEach((video) => video.pause());
    };
    const initializeVideos = (container) => {
        container.querySelectorAll('[data-media-player]').forEach((player) => {
            if (player.dataset.videoInitialized === '1') return;
            player.dataset.videoInitialized = '1';
            const video = player.querySelector('[data-landing-video]');
            const toggle = player.querySelector('[data-video-toggle]');
            const restart = player.querySelector('[data-video-restart]');
            const playIcon = player.querySelector('[data-video-play-icon]');
            const pauseIcon = player.querySelector('[data-video-pause-icon]');
            const status = player.querySelector('[data-video-status]');
            if (!video || !toggle || !restart) return;
            let visible = false;
            let manuallyPaused = false;
            let playPending = false;
            let manualPlayback = false;
            const autoplay = video.hasAttribute('data-autoplay-video');
            const renderPlayback = () => {
                const playing = !video.paused && !video.ended && !video.error;
                toggle.setAttribute('aria-label', playing ? 'Pausar video' : 'Reproducir video');
                toggle.setAttribute('aria-pressed', String(playing));
                if (playIcon) playIcon.hidden = playing;
                if (pauseIcon) pauseIcon.hidden = !playing;
                player.classList.toggle('is-playing', playing);
            };
            const playVideo = async (manual = false) => {
                if (playPending) return;
                if (manual) { manuallyPaused = false; manualPlayback = true; }
                if (!video.getAttribute('src') && video.dataset.videoSrc) {
                    video.src = video.dataset.videoSrc;
                    delete video.dataset.videoSrc;
                }
                if (!video.getAttribute('src')) return;
                if (video.error) video.load();
                if (status) status.hidden = true;
                playPending = true;
                try {
                    await video.play();
                } catch (error) {
                    if (manual && status && error.name !== 'AbortError') {
                        status.textContent = 'No pudimos reproducir este video. Vuelve a intentarlo.';
                        status.hidden = false;
                    }
                } finally {
                    playPending = false;
                    renderPlayback();
                }
            };
            toggle.addEventListener('click', () => {
                if (video.paused || video.ended) playVideo(true);
                else { manuallyPaused = true; video.pause(); }
            });
            restart.addEventListener('click', () => {
                manuallyPaused = true;
                video.pause();
                try { video.currentTime = 0; } catch { /* Unloaded videos already start at zero. */ }
                if (status) status.hidden = true;
                renderPlayback();
            });
            video.addEventListener('play', () => {
                videoPlayers.forEach((other) => { if (other !== video) other.pause(); });
                renderPlayback();
            });
            video.addEventListener('pause', renderPlayback);
            video.addEventListener('ended', renderPlayback);
            video.addEventListener('error', () => {
                if (status) { status.textContent = 'Este video no está disponible. Puedes volver a intentar reproducirlo.'; status.hidden = false; }
                renderPlayback();
            });
            video.addEventListener('contextmenu', (event) => event.preventDefault());
            video.addEventListener('dblclick', (event) => event.preventDefault());
            const updateVisibility = () => {
                const closedStory = player.closest('dialog') && !player.closest('dialog').open;
                const hiddenSlide = player.closest('[data-carousel-slide][aria-hidden="true"]');
                if (!visible || document.hidden || closedStory || hiddenSlide) { video.pause(); return; }
                if (autoplay && videoMotionPreference.matches && !manualPlayback) { video.pause(); return; }
                if (autoplay && !manuallyPaused && !videoMotionPreference.matches) {
                    video.muted = true;
                    playVideo();
                }
            };
            videoPlayers.set(player, video);
            document.addEventListener('visibilitychange', updateVisibility);
            videoMotionPreference.addEventListener('change', updateVisibility);
            if ('IntersectionObserver' in window) {
                new IntersectionObserver((entries) => { visible = entries[0].isIntersecting; updateVisibility(); }, { threshold: 0.15 }).observe(player);
            } else {
                const check = () => { const bounds = player.getBoundingClientRect(); visible = bounds.bottom > 0 && bounds.top < window.innerHeight; updateVisibility(); };
                window.addEventListener('scroll', check, { passive: true });
                check();
            }
            renderPlayback();
        });
    };
    initializeVideos(document);

    document.querySelectorAll('[data-story-open]').forEach((opener) => {
        const dialog = document.getElementById(opener.getAttribute('aria-controls'));
        if (!(dialog instanceof HTMLDialogElement)) return;
        const closeStory = () => {
            pauseVideos(dialog);
            dialog.close();
            opener.focus({ preventScroll: true });
        };
        opener.addEventListener('click', () => {
            pauseVideos(opener.closest('[data-story-index]') || document);
            const gallery = dialog.querySelector('[data-story-gallery]');
            const template = dialog.querySelector('[data-story-template]');
            if (gallery && template && !gallery.children.length) {
                const fragment = template.content.cloneNode(true);
                fragment.querySelectorAll('img[data-src]').forEach((image) => {
                    image.src = image.dataset.src;
                    delete image.dataset.src;
                });
                gallery.append(fragment);
                initializeVideos(gallery);
            }
            dialog.showModal();
            trackAnalytics('select_wedding_story', { story_id: opener.dataset.storyOpen });
        });
        dialog.addEventListener('close', () => pauseVideos(dialog));
        dialog.querySelector('[data-story-close]')?.addEventListener('click', closeStory);
        dialog.addEventListener('cancel', (event) => { event.preventDefault(); closeStory(); });
        dialog.querySelector('[data-story-inquire]')?.addEventListener('click', closeStory);
    });

    document.querySelectorAll('[data-wedding-calculator]').forEach((calculator) => {
        const output = calculator.querySelector('[data-calculator-total]');
        const coverage = calculator.querySelector('[data-rate-coverage]');
        const city = calculator.querySelector('[data-rate-city]');
        if (!coverage || !output) return;
        const initialMessage = output.textContent;
        const currency = new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN', minimumFractionDigits: 2 });
        const updateEstimate = () => {
            if (!coverage.value || (city && !city.value)) {
                output.textContent = initialMessage;
                return;
            }
            const amounts = [coverage.selectedOptions[0]?.dataset.amount, city?.selectedOptions[0]?.dataset.amount,
                ...[...calculator.querySelectorAll('[data-rate-extra]:checked')].map((input) => input.dataset.amount)];
            const estimate = amounts.reduce((total, amount) => total + (Number.isFinite(Number(amount)) ? Number(amount) : 0), 0);
            output.textContent = currency.format(estimate);
        };
        calculator.addEventListener('change', updateEstimate);
    });

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
        const steps = [...form.querySelectorAll('[data-inquiry-step]')];
        const twoStep = form.dataset.twoStep === '1' && steps.length === 2;
        let inquiryStarted = false;
        const showStep = (index, focus = true) => {
            if (!twoStep) return;
            steps.forEach((step, stepIndex) => { step.hidden = stepIndex !== index; });
            form.querySelectorAll('[data-inquiry-step-label]').forEach((label, stepIndex) => {
                if (stepIndex === index) label.setAttribute('aria-current', 'step');
                else label.removeAttribute('aria-current');
            });
            if (focus) {
                steps[index].querySelector('input, select, textarea, button')?.focus({ preventScroll: true });
                const headerHeight = document.querySelector('.landing-header.is-sticky')?.getBoundingClientRect().height || 0;
                window.scrollTo({
                    top: window.scrollY + form.getBoundingClientRect().top - headerHeight - 16,
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                });
            }
        };
        form.addEventListener('focusin', () => {
            if (inquiryStarted) return;
            inquiryStarted = true;
            trackAnalytics('begin_inquiry');
        });
        if (twoStep) {
            form.querySelector('[data-inquiry-progress]').hidden = false;
            const next = form.querySelector('[data-inquiry-next]');
            const back = form.querySelector('[data-inquiry-back]');
            next.hidden = false;
            back.hidden = false;
            const hasStepOneError = Boolean(steps[0].querySelector('.field-error:not(:empty)'));
            const hasStepTwoError = Boolean(steps[1].querySelector('.field-error:not(:empty)'));
            showStep(hasStepTwoError && !hasStepOneError ? 1 : 0, false);
            next.addEventListener('click', () => {
                const invalid = [...steps[0].querySelectorAll('input, select, textarea')].find((input) => !input.checkValidity());
                if (invalid) { invalid.reportValidity(); return; }
                showStep(1);
            });
            back.addEventListener('click', () => showStep(0));
            form.addEventListener('invalid', (event) => {
                const index = steps.findIndex((step) => step.contains(event.target));
                if (index >= 0) showStep(index, false);
            }, true);
        }

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
                        const invalidInput = form.querySelector('[aria-invalid="true"]');
                        const invalidStep = steps.findIndex((step) => step.contains(invalidInput));
                        if (invalidStep >= 0) showStep(invalidStep, false);
                        invalidInput?.focus();
                        invalidInput?.scrollIntoView({ block: 'center', behavior: 'smooth' });
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
                if (!responseType.includes('application/json') || typeof result.created !== 'boolean' ||
                    !/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(String(result.event_id || ''))) {
                    status.textContent = 'No recibimos la confirmación del envío. Tus datos siguen aquí; vuelve a intentarlo para confirmar tu consulta.';
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
                if (!selected) pauseVideos(slide);
            });
            dots.forEach((dot, dotIndex) => dot.setAttribute('aria-pressed', String(dotIndex === current)));
        };

        const schedule = () => {
            window.clearTimeout(timer);
            const temporarilyPaused = hovered || focused || !visible || document.hidden || motionPreference.matches;
            carousel.classList.toggle('is-paused', temporarilyPaused || manuallyPaused);
            if (pauseButton) {
                pauseButton.hidden = !autoplay || motionPreference.matches || slides.length < 2;
                const playIcon = pauseButton.querySelector('[data-carousel-play-icon]');
                const pauseIcon = pauseButton.querySelector('[data-carousel-pause-icon]');
                if (playIcon) playIcon.hidden = !manuallyPaused;
                if (pauseIcon) pauseIcon.hidden = manuallyPaused;
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

        carousel.addEventListener('play', () => { manuallyPaused = true; schedule(); }, true);
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
