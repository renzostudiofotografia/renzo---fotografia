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
    let saving = false;
    const videoUploadForm = document.querySelector('#landing-video-upload-form');
    const videoUploadStatus = document.querySelector('#landing-video-upload-status');
    const videoLibrary = document.querySelector('#landing-video-library');
    const videoPicker = document.querySelector('#landing-video-picker');
    const videoPickerList = videoPicker?.querySelector('[data-video-picker-list]');
    const genericVideoUploadForm = document.querySelector('#landing-generic-video-upload-form');
    const genericVideoUploadStatus = document.querySelector('#landing-generic-video-upload-status');
    let activeVideoInput = null;
    let activeVideoTrigger = null;
    const announcement = document.querySelector('#landing-editor-announcement');
    let normalizedNames = new Map();

    const formPayload = () => {
        const tree = new Map();
        for (const [name, value] of new FormData(form)) {
            if (name === '_method' || typeof value !== 'string') continue;
            const parts = name.match(/[^\[\]]+/g) || [];
            let branch = tree;
            parts.forEach((part, index) => {
                if (index === parts.length - 1) {
                    branch.set(part, value);
                } else {
                    if (!(branch.get(part) instanceof Map)) branch.set(part, new Map());
                    branch = branch.get(part);
                }
            });
        }
        normalizedNames = new Map();
        const values = (branch, path = [], originalPath = []) => {
            if (!(branch instanceof Map)) {
                normalizedNames.set(path.join('.'), originalPath.reduce((name, key, index) => index ? `${name}[${key}]` : key, ''));
                return branch;
            }
            const isArray = branch.size > 0 && [...branch.keys()].every((key) => /^\d+$/.test(key));
            const entries = [...branch].map(([key, value], index) => {
                const normalizedKey = isArray ? index : key;
                return [normalizedKey, values(value, [...path, normalizedKey], [...originalPath, key])];
            });
            return isArray ? entries.map(([, value]) => value) : Object.fromEntries(entries);
        };
        return values(tree);
    };

    const jsonHeaders = () => ({
        Accept: 'application/json', 'Content-Type': 'application/json',
        'X-CSRF-TOKEN': form.elements.namedItem('_token').value,
        'X-Requested-With': 'XMLHttpRequest',
    });

    const showValidation = (errors = {}) => {
        form.querySelectorAll('[data-live-error]').forEach((error) => error.remove());
        form.querySelectorAll('[aria-invalid="true"]').forEach((input) => input.removeAttribute('aria-invalid'));
        for (const [path, messages] of Object.entries(errors)) {
            const name = normalizedNames.get(path) || path.split('.').reduce((value, segment, index) => index ? `${value}[${segment}]` : segment, '');
            const input = form.elements.namedItem(name);
            const field = [...form.querySelectorAll('[data-field-name]')].find((element) => element.dataset.fieldName === name);
            if (input instanceof HTMLElement) input.setAttribute('aria-invalid', 'true');
            if (!field) continue;
            const error = document.createElement('span');
            error.className = 'field-error';
            error.dataset.liveError = 'true';
            error.textContent = Array.isArray(messages) ? messages[0] : String(messages);
            field.append(error);
            field.hidden = false;
            let details = field.closest('details');
            while (details) {
                details.open = true;
                details = details.parentElement?.closest('details');
            }
        }
        if (Object.keys(errors).length === 0) updateSectionMediaFields();
    };

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
            const displayedRequest = preview.contentDocument?.documentElement.dataset.previewRequest;
            const requestedPreview = preview.dataset.previewRequest;
            if (requestedPreview && (requestedPreview !== String(requestNumber) || displayedRequest !== requestedPreview)) return;
            if (!requestedPreview && requestNumber > 0) return;
            previewStatus.textContent = 'Vista previa en tiempo real';
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
        const body = JSON.stringify({ data: formPayload().data });
        try {
            const response = await fetch(editor.dataset.previewUrl, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                signal: controller.signal,
                headers: jsonHeaders(),
            });
            if (currentRequest !== requestNumber) return;
            if (!response.ok || response.redirected) {
                if (response.status === 422) {
                    const result = await response.json();
                    showValidation(result.errors);
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
            preview.dataset.previewRequest = String(currentRequest);
            preview.srcdoc = html.replace(/<html(?=\s|>)/i, `<html data-preview-request="${currentRequest}"`);
            previewError.hidden = true;
            showValidation();
            previewStatus.textContent = 'Mostrando tus cambios…';
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
        previewStatus.textContent = 'Actualizando vista previa…';
        saveState.classList.add('unsaved');
        publishButton.disabled = true;
        publishButton.title = 'Guarda el borrador antes de publicarlo.';
        publishHelp.textContent = 'Guarda estos cambios para poder publicarlos.';
        clearTimeout(timer);
        timer = setTimeout(refreshPreview, 220);
    };

    form.addEventListener('input', markChanged);
    form.addEventListener('change', markChanged);
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (videoUploading) {
            videoUploadStatus.textContent = 'Espera a que termine la subida del video antes de guardar.';
            videoUploadStatus.hidden = false;
            return;
        }
        if (saving) return;
        saving = true;
        form.inert = true;
        form.setAttribute('aria-busy', 'true');
        publishButton.disabled = true;
        saveState.textContent = 'Guardando borrador…';
        const buttons = [...document.querySelectorAll('button[type="submit"]')].filter((button) => button.form === form);
        buttons.forEach((button) => { button.disabled = true; });
        try {
            const response = await fetch(form.action, {
                method: 'PUT', credentials: 'same-origin', headers: jsonHeaders(), body: JSON.stringify(formPayload()),
            });
            const isJson = (response.headers.get('content-type') || '').includes('application/json');
            const result = isJson ? await response.json() : {};
            if (!response.ok) {
                showValidation(result.errors);
                throw new Error(Object.values(result.errors || {}).flat()[0] || (response.status === 419 || response.status === 401
                    ? 'La sesión necesita renovarse. Conserva tus cambios antes de recargar.'
                    : 'No pudimos guardar el borrador. Tus cambios siguen aquí.'));
            }
            if (!response.redirected && !result.redirect) {
                throw new Error('No recibimos la confirmación del guardado. Recarga en otra pestaña para revisar el borrador antes de continuar.');
            }
            const destination = new URL(result.redirect || response.url, window.location.href);
            if (destination.origin !== window.location.origin || destination.pathname !== `${new URL(form.action).pathname.replace(/\/$/, '')}/edit`) {
                throw new Error('Tu sesión necesita renovarse. Conserva tus cambios antes de recargar.');
            }
            dirty = false;
            saving = false;
            clearTimeout(timer);
            requestNumber++;
            controller?.abort();
            window.location.assign(destination.href);
        } catch (error) {
            dirty = true;
            saveState.textContent = 'Borrador pendiente de guardar';
            saveState.classList.add('unsaved');
            previewError.textContent = error.message || 'Revisa tu conexión y vuelve a guardar. Tus cambios siguen aquí.';
            previewError.hidden = false;
            saving = false;
            form.inert = false;
            form.removeAttribute('aria-busy');
            buttons.forEach((button) => { button.disabled = false; });
        }
    });
    document.querySelector('#landing-publish-form').addEventListener('submit', (event) => {
        if (dirty || videoUploading || saving) event.preventDefault();
    });
    window.addEventListener('beforeunload', (event) => {
        if (!dirty && !videoUploading && !saving) return;
        event.preventDefault();
        event.returnValue = '';
    });
    document.addEventListener('landing:delete-confirmed', (event) => {
        if (videoUploading || saving) {
            event.preventDefault();
            videoUploadStatus.textContent = saving ? 'Espera a que termine el guardado antes de eliminar la landing.' : 'Espera a que termine la subida del video antes de eliminar la landing.';
            videoUploadStatus.hidden = false;
            return;
        }
        dirty = false;
        clearTimeout(timer);
        requestNumber++;
        controller?.abort();
    });

    const selectVideo = (input, url) => {
        if (!(input instanceof HTMLInputElement) || !form.contains(input)) {
            throw new Error('El contenido que estabas editando ya no está disponible. El video se conserva en la biblioteca.');
        }
        input.value = url;
        const mediaTypeName = input.name === 'data[hero_video_url]' ? 'data[hero_media]' : input.name.replace(/\[video_url\]$/, '[media_type]');
        const mediaType = form.elements.namedItem(mediaTypeName);
        if (mediaType instanceof HTMLSelectElement) mediaType.value = 'video';
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };

    form.addEventListener('input', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || !input.matches('[data-video-url-input]')) return;
        const url = input.value.trim();
        if (url && !/^(?:\/media\/\d+|https:\/\/[^\s<>"']+)$/.test(url)) return;
        const mediaTypeName = input.name === 'data[hero_video_url]' ? 'data[hero_media]' : input.name.replace(/\[video_url\]$/, '[media_type]');
        const mediaType = form.elements.namedItem(mediaTypeName);
        if (mediaType instanceof HTMLSelectElement) mediaType.value = url ? 'video' : 'image';
    });

    const addVideoToLibrary = (url, name) => {
        if (videoLibrary && ![...videoLibrary.options].some((option) => option.value === url)) {
            videoLibrary.add(new Option(name, url));
        }
        if (!videoPickerList || [...videoPickerList.querySelectorAll('[data-use-video]')].some((button) => button.dataset.useVideo === url)) return;
        videoPickerList.querySelector('[data-video-library-empty]')?.remove();
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'landing-video-library-item';
        button.dataset.useVideo = url;
        button.dataset.videoName = name;
        button.textContent = name;
        videoPickerList.prepend(button);
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-pick-video]');
        if (trigger && videoPicker) {
            if (videoUploading || saving) return;
            activeVideoTrigger = trigger;
            activeVideoInput = trigger.closest('.landing-video-control')?.querySelector('[data-video-url-input]');
            genericVideoUploadStatus.hidden = true;
            videoPicker.showModal();
        }
        if (event.target.closest('[data-close-video-picker]') && !videoUploading) videoPicker?.close();
        const choice = event.target.closest('[data-use-video]');
        if (choice && videoPicker?.open && !videoUploading) {
            try {
                selectVideo(activeVideoInput, choice.dataset.useVideo);
                videoPicker.close();
            } catch (error) {
                genericVideoUploadStatus.textContent = error.message;
                genericVideoUploadStatus.classList.add('is-error');
                genericVideoUploadStatus.hidden = false;
            }
        }
        const clear = event.target.closest('[data-clear-video]');
        if (clear) {
            const input = clear.closest('.landing-video-control')?.querySelector('[data-video-url-input]');
            if (input) {
                input.value = '';
                const mediaTypeName = input.name === 'data[hero_video_url]' ? 'data[hero_media]' : input.name.replace(/\[video_url\]$/, '[media_type]');
                const mediaType = form.elements.namedItem(mediaTypeName);
                if (mediaType instanceof HTMLSelectElement) mediaType.value = 'image';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    });
    videoPicker?.addEventListener('cancel', (event) => {
        if (videoUploading) event.preventDefault();
    });
    videoPicker?.addEventListener('close', () => activeVideoTrigger?.focus({ preventScroll: true }));
    document.querySelector('#landing-video-search')?.addEventListener('input', (event) => {
        const query = event.target.value.trim().toLocaleLowerCase();
        videoPickerList.querySelectorAll('[data-use-video]').forEach((button) => {
            button.hidden = !button.dataset.videoName.toLocaleLowerCase().includes(query);
        });
    });

    const uploadVideo = async (uploadForm, status, button, input) => {
        if (videoUploading || saving) return;
        const fileInput = uploadForm.elements.namedItem('video');
        const video = fileInput?.files[0];
        if (!video) return;
        status.hidden = false;
        status.classList.remove('is-error');
        if (video.size > Number(uploadForm.dataset.maxUploadBytes)) {
            status.textContent = `El video no debe superar ${uploadForm.dataset.maxUploadLabel}. Comprime el video o utiliza un enlace directo.`;
            status.classList.add('is-error');
            return;
        }
        const body = new FormData(uploadForm);
        const originalLabel = button.textContent;
        const previousPublishDisabled = publishButton.disabled;
        const saveButtons = [...document.querySelectorAll('button[type="submit"]')].filter((saveButton) => saveButton.form === form);
        const closeButton = videoPicker?.querySelector('[data-close-video-picker]');
        videoUploading = true;
        publishButton.disabled = true;
        saveButtons.forEach((saveButton) => { saveButton.disabled = true; });
        if (closeButton) closeButton.disabled = true;
        button.disabled = true;
        button.textContent = 'Subiendo video…';
        fileInput.disabled = true;
        uploadForm.setAttribute('aria-busy', 'true');
        status.textContent = 'Subiendo tu video. Espera a que termine antes de guardar o publicar.';
        try {
            const response = await fetch(uploadForm.action, {
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
            addVideoToLibrary(result.url, result.name || video.name);
            selectVideo(input, result.url);
            if (input.name === 'data[hero_video_url]' && videoLibrary) videoLibrary.value = result.url;
            status.textContent = result.warning || `Video «${result.name || video.name}» seleccionado. Elige su miniatura y guarda el borrador.`;
            fileInput.value = '';
        } catch (error) {
            status.textContent = error.message || 'No pudimos conectar. Tu selección y tus cambios siguen aquí.';
            status.classList.add('is-error');
        } finally {
            videoUploading = false;
            publishButton.disabled = dirty || previousPublishDisabled;
            saveButtons.forEach((saveButton) => { saveButton.disabled = false; });
            if (closeButton) closeButton.disabled = false;
            button.disabled = false;
            button.textContent = originalLabel;
            fileInput.disabled = false;
            uploadForm.removeAttribute('aria-busy');
        }
    };

    videoUploadForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        uploadVideo(videoUploadForm, videoUploadStatus, document.querySelector('#landing-video-upload-button'), form.elements.namedItem('data[hero_video_url]'));
    });
    genericVideoUploadForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        uploadVideo(genericVideoUploadForm, genericVideoUploadStatus, document.querySelector('#landing-generic-video-upload-button'), activeVideoInput);
    });

    videoLibrary?.addEventListener('change', () => {
        if (!/^\/media\/\d+$/.test(videoLibrary.value)) return;
        selectVideo(form.elements.namedItem('data[hero_video_url]'), videoLibrary.value);
        videoUploadStatus.textContent = 'Video de biblioteca seleccionado. Guarda el borrador para conservar el cambio.';
        videoUploadStatus.hidden = false;
        videoUploadStatus.classList.remove('is-error');
    });

    document.querySelector('#landing-group-jump')?.addEventListener('change', (event) => {
        const group = [...form.querySelectorAll('[data-group]')].find((item) => item.dataset.group === event.target.value);
        if (!group) return;
        group.open = true;
        group.scrollIntoView({ behavior: 'smooth', block: 'start' });
        group.querySelector(':scope > summary')?.focus({ preventScroll: true });
    });

    document.querySelector('#landing-wedding-form')?.addEventListener('submit', (event) => {
        if (dirty || videoUploading || saving) {
            event.preventDefault();
            previewError.textContent = 'Guarda el borrador antes de preparar el diseño de bodas. Así conservarás todos tus cambios.';
            previewError.hidden = false;
            return;
        }
        if (!window.confirm('¿Preparar el diseño de bodas en el borrador guardado? Tu contenido se conserva y la versión pública no cambiará hasta que publiques.')) {
            event.preventDefault();
        }
    });

    let draggedItem = null;
    let draggedList = null;
    let reordered = false;
    const finishDrag = () => {
        if (reordered && draggedList) {
            draggedList.dispatchEvent(new Event('input', { bubbles: true }));
            if (announcement) announcement.textContent = 'Orden actualizado en el borrador. Guarda para conservarlo.';
        }
        draggedItem?.classList.remove('is-dragging');
        draggedItem = null;
        draggedList = null;
        reordered = false;
    };
    form.addEventListener('dragstart', (event) => {
        const handle = event.target.closest('[data-item-drag]');
        if (!handle) return;
        draggedItem = handle.closest('.repeater-item');
        draggedList = draggedItem.parentElement;
        draggedItem.classList.add('is-dragging');
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', 'landing-block');
        event.dataTransfer.setDragImage(draggedItem, 25, 15);
    });
    form.addEventListener('dragover', (event) => {
        if (!draggedItem) return;
        const target = event.target.closest('.repeater-item');
        if (!target || target === draggedItem || target.parentElement !== draggedList) return;
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        const bounds = target.getBoundingClientRect();
        const before = event.clientY < bounds.top + bounds.height / 2;
        if ((before && target.previousElementSibling === draggedItem) || (!before && target.nextElementSibling === draggedItem)) return;
        draggedList.insertBefore(draggedItem, before ? target : target.nextElementSibling);
        reordered = true;
    });
    form.addEventListener('drop', (event) => {
        if (!draggedItem) return;
        event.preventDefault();
        finishDrag();
    });
    form.addEventListener('dragend', finishDrag);

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

    const updateSectionMediaFields = () => {
        form.querySelectorAll('[data-visible-for-sections]').forEach((field) => {
            const type = field.closest('.repeater-item')?.querySelector('select[name$="[type]"]')?.value;
            field.hidden = Boolean(type && !field.dataset.visibleForSections.split(',').includes(type) && !field.querySelector('.field-error:not(:empty)'));
        });
    };
    form.addEventListener('input', updateSectionMediaFields);
    form.addEventListener('change', updateSectionMediaFields);
    updateSectionMediaFields();

    form.addEventListener('input', (event) => {
        const item = event.target.closest('.repeater-item');
        if (!item) return;
        const inputs = [...item.querySelectorAll('input, select')].filter((input) => input.closest('.repeater-item') === item);
        const label = ['names', 'title', 'name', 'label', 'question'].map((key) => inputs.find((input) => input.name.endsWith(`[${key}]`) && input.value.trim())).find(Boolean);
        const type = inputs.find((input) => input.name.endsWith('[type]'));
        const heading = item.querySelector(':scope > .repeater-heading > [data-item-label]');
        if (heading) heading.textContent = label?.value.trim() || type?.selectedOptions[0]?.textContent || 'Nuevo elemento';
        const visible = inputs.find((input) => input.type === 'checkbox' && input.name.endsWith('[enabled]'));
        item.classList.toggle('is-hidden-item', Boolean(visible && !visible.checked));
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
            const sectionTypes = { buttons: 'hero', media: 'hero', motion: 'hero', trust: 'trust', stories: 'stories', about: 'about', experiences: 'experiences', process: 'process', benefits: 'benefits', gallery: 'gallery', offer: 'offer', testimonials: 'testimonials', faqs: 'faq', form: 'lead', conversion: 'lead', footer: 'closing' };
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
