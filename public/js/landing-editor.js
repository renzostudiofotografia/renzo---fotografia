document.addEventListener('DOMContentLoaded', () => {
    const editor = document.querySelector('[data-landing-editor]');
    if (!editor) return;

    const form = document.querySelector('#landing-form');
    const preview = document.querySelector('#landing-preview');
    const stage = editor.querySelector('.landing-preview-stage');
    const canvas = editor.querySelector('.landing-preview-canvas');
    const saveState = document.querySelector('#landing-save-state');
    const publishButton = document.querySelector('#landing-publish');
    const publishHelp = document.querySelector('#landing-publish-help');
    const previewStatus = document.querySelector('#landing-preview-status');
    const previewError = document.querySelector('#landing-preview-error');
    let dirty = false;
    let timer;
    let controller;
    let requestNumber = 0;
    let pendingScroll = 0;
    let pendingSectionFocus = null;
    let device = 'desktop';
    let videoUploading = false;
    const videoUploadForm = document.querySelector('#landing-video-upload-form');
    const videoUploadStatus = document.querySelector('#landing-video-upload-status');
    const videoLibrary = document.querySelector('#landing-video-library');

    const resizePreview = () => {
        if (!stage.clientWidth || !stage.clientHeight) return;
        const widths = { desktop: 1280, tablet: 768, mobile: 390 };
        const width = widths[device];
        const scale = Math.min(stage.clientWidth / width, 1);
        canvas.style.width = `${width}px`;
        canvas.style.height = `${stage.clientHeight / scale}px`;
        canvas.style.left = `${Math.max(0, (stage.clientWidth - width * scale) / 2)}px`;
        canvas.style.transform = `scale(${scale})`;
    };

    const currentScroll = () => {
        try {
            return preview.contentWindow?.scrollY || 0;
        } catch {
            return 0;
        }
    };

    const focusPreviewSection = (target) => {
        try {
            const section = preview.contentDocument?.querySelector(target);
            if (!section) return;
            section.scrollIntoView({ block: 'start', behavior: 'instant' });
            pendingScroll = currentScroll();
        } catch {
            return;
        }
    };

    preview.addEventListener('load', () => {
        resizePreview();
        try {
            const sectionTarget = pendingSectionFocus;
            pendingSectionFocus = null;
            const restoreScroll = () => {
                if (sectionTarget) {
                    focusPreviewSection(sectionTarget);
                } else {
                    preview.contentWindow?.scrollTo({ top: pendingScroll, behavior: 'instant' });
                }
            };
            restoreScroll();
            preview.contentDocument?.fonts?.ready.then(restoreScroll);
        } catch {
            // A replaced iframe may finish loading after a newer preview.
        }
    });

    const refreshPreview = async () => {
        const currentRequest = ++requestNumber;
        controller?.abort();
        controller = new AbortController();
        previewStatus.textContent = 'Actualizando vista previa…';
        const body = new FormData(form);
        body.delete('_method');
        try {
            const response = await fetch(editor.dataset.previewUrl, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                signal: controller.signal,
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (currentRequest !== requestNumber) return;
            if (!response.ok || response.redirected) {
                if (response.status === 422) {
                    const result = await response.json();
                    const messages = Object.values(result.errors || {}).flat();
                    throw new Error(messages[0] || 'Revisa los valores antes de actualizar la vista previa.');
                }
                throw new Error(response.status === 419 || response.status === 401 || response.redirected
                    ? 'Tu sesión necesita renovarse. Guarda una copia de tus cambios antes de recargar.'
                    : 'Completa los campos marcados para actualizar la vista previa. Tu última vista sigue disponible.');
            }
            const html = await response.text();
            if (currentRequest !== requestNumber) return;
            pendingScroll = currentScroll();
            preview.srcdoc = html;
            previewError.hidden = true;
            previewStatus.textContent = 'Vista previa en tiempo real';
        } catch (error) {
            if (error.name === 'AbortError' || currentRequest !== requestNumber) return;
            previewError.textContent = error.message || 'No pudimos actualizar la vista previa. Vuelve a editar para reintentar.';
            previewError.hidden = false;
            previewStatus.textContent = 'Vista previa pendiente';
        }
    };

    const markChanged = (event) => {
        if (event?.target instanceof HTMLInputElement && event.target.form && event.target.form !== form) return;
        dirty = true;
        requestNumber++;
        controller?.abort();
        saveState.textContent = 'Tienes cambios sin guardar';
        saveState.classList.add('unsaved');
        publishButton.disabled = true;
        publishButton.title = 'Guarda el borrador antes de publicarlo.';
        publishHelp.textContent = 'Guarda estos cambios para poder publicarlos.';
        clearTimeout(timer);
        timer = setTimeout(refreshPreview, 220);
    };

    form.addEventListener('input', markChanged);
    form.addEventListener('change', markChanged);
    form.addEventListener('submit', (event) => {
        if (videoUploading) {
            event.preventDefault();
            videoUploadStatus.textContent = 'Espera a que termine la subida del video antes de guardar.';
            videoUploadStatus.hidden = false;
            return;
        }
        dirty = false;
    });
    document.querySelector('#landing-publish-form').addEventListener('submit', (event) => {
        if (dirty || videoUploading) event.preventDefault();
    });
    window.addEventListener('beforeunload', (event) => {
        if (!dirty && !videoUploading) return;
        event.preventDefault();
        event.returnValue = '';
    });
    document.addEventListener('landing:delete-confirmed', (event) => {
        if (videoUploading) {
            event.preventDefault();
            videoUploadStatus.textContent = 'Espera a que termine la subida del video antes de eliminar la landing.';
            videoUploadStatus.hidden = false;
            return;
        }
        dirty = false;
        clearTimeout(timer);
        requestNumber++;
        controller?.abort();
    });

    videoUploadForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (videoUploading) return;
        const fileInput = videoUploadForm.elements.namedItem('video');
        const video = fileInput.files[0];
        if (!video) return;
        videoUploadStatus.hidden = false;
        videoUploadStatus.classList.remove('is-error');
        if (video.size > Number(videoUploadForm.dataset.maxUploadBytes)) {
            videoUploadStatus.textContent = `El video no debe superar ${videoUploadForm.dataset.maxUploadLabel}. Comprime el archivo o utiliza un enlace directo.`;
            videoUploadStatus.classList.add('is-error');
            return;
        }
        const body = new FormData(videoUploadForm);
        const button = document.querySelector('#landing-video-upload-button');
        const previousPublishDisabled = publishButton.disabled;
        videoUploading = true;
        publishButton.disabled = true;
        button.disabled = true;
        button.textContent = 'Subiendo video…';
        fileInput.disabled = true;
        videoUploadForm.setAttribute('aria-busy', 'true');
        videoUploadStatus.textContent = 'Subiendo tu video. Puedes seguir editando mientras termina.';
        try {
            const response = await fetch(videoUploadForm.action, {
                method: 'POST', body, credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const contentType = response.headers.get('content-type') || '';
            const result = contentType.includes('application/json') ? await response.json() : {};
            if (!response.ok || response.redirected) {
                const validationMessage = Object.values(result.errors || {}).flat()[0];
                throw new Error(validationMessage || (response.status === 413
                    ? 'El servidor admite archivos más pequeños. Comprime el video o pega un enlace directo.'
                    : response.status === 419 || response.status === 401 || response.redirected
                        ? 'La sesión necesita renovarse. Conserva tus cambios antes de recargar.'
                        : 'No se pudo subir el video. Vuelve a intentarlo o utiliza un enlace directo.'));
            }
            if (typeof result.url !== 'string' || !/^\/media\/\d+$/.test(result.url)) {
                throw new Error('El servidor no devolvió un video válido. Inténtalo de nuevo.');
            }
            form.elements.namedItem('data[hero_video_url]').value = result.url;
            form.elements.namedItem('data[hero_media]').value = 'video';
            form.elements.namedItem('data[hero_video_url]').dispatchEvent(new Event('input', { bubbles: true }));
            if (videoLibrary && ![...videoLibrary.options].some((option) => option.value === result.url)) {
                videoLibrary.add(new Option(result.name || video.name, result.url));
            }
            if (videoLibrary) videoLibrary.value = result.url;
            videoUploadStatus.textContent = result.warning || `Video «${result.name || video.name}» listo. Elige su miniatura y guarda el borrador.`;
            fileInput.value = '';
        } catch (error) {
            videoUploadStatus.textContent = error.message || 'No pudimos conectar. Tu selección y tus cambios siguen aquí.';
            videoUploadStatus.classList.add('is-error');
        } finally {
            videoUploading = false;
            publishButton.disabled = dirty || previousPublishDisabled;
            button.disabled = false;
            button.textContent = 'Subir y usar este video ↑';
            fileInput.disabled = false;
            videoUploadForm.removeAttribute('aria-busy');
        }
    });

    videoLibrary?.addEventListener('change', () => {
        if (!/^\/media\/\d+$/.test(videoLibrary.value)) return;
        form.elements.namedItem('data[hero_video_url]').value = videoLibrary.value;
        form.elements.namedItem('data[hero_media]').value = 'video';
        form.elements.namedItem('data[hero_video_url]').dispatchEvent(new Event('input', { bubbles: true }));
        videoUploadStatus.textContent = 'Video de biblioteca seleccionado. Guarda el borrador para conservar el cambio.';
        videoUploadStatus.hidden = false;
        videoUploadStatus.classList.remove('is-error');
    });

    editor.querySelectorAll('[data-landing-device]').forEach((button) => {
        button.addEventListener('click', () => {
            device = button.dataset.landingDevice;
            editor.querySelectorAll('[data-landing-device]').forEach((item) => {
                const selected = item === button;
                item.classList.toggle('active', selected);
                item.setAttribute('aria-pressed', String(selected));
            });
            resizePreview();
        });
    });

    form.addEventListener('input', (event) => {
        const item = event.target.closest('.repeater-item');
        const title = item?.querySelector('input[name$="[title]"], input[name$="[name]"], input[name$="[question]"]');
        if (title && title.value.trim()) item.querySelector('.repeater-heading>span').textContent = title.value;
    });
    form.addEventListener('toggle', (event) => {
        const details = event.target;
        if (!(details instanceof HTMLDetailsElement) || !details.open) return;
        const group = details.closest('[data-group]');
        let target = null;
        if (group?.dataset.group === 'sections' && details.classList.contains('repeater-item')) {
            const index = [...details.parentElement.children].indexOf(details);
            target = `[data-landing-section="${index}"]`;
        } else if (details === group) {
            const sectionTypes = { media: 'hero', motion: 'hero', benefits: 'benefits', gallery: 'gallery', offer: 'offer', testimonials: 'testimonials', faqs: 'faq', conversion: 'lead' };
            const sectionType = sectionTypes[group.dataset.group];
            if (sectionType) target = `.section-${sectionType}`;
        }
        if (!target) return;
        focusPreviewSection(target);
        pendingSectionFocus = target;
    }, true);
    new ResizeObserver(resizePreview).observe(stage);
    resizePreview();
    if (editor.dataset.unsaved === 'true') markChanged();
});
