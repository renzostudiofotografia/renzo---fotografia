document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('#section-form');
    const saveState = document.querySelector('#save-state');
    let dirty = false;
    const markDirty = () => {
        if (!form) return;
        dirty = true;
        if (saveState) {
            saveState.textContent = 'Tienes cambios sin guardar';
            saveState.classList.add('unsaved');
        }
    };
    form?.addEventListener('input', markDirty);
    form?.addEventListener('change', markDirty);
    form?.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', (event) => {
        if (dirty) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    const deleteDialog = document.querySelector('#landing-delete-dialog');
    const deleteForm = document.querySelector('#landing-delete-form');
    const deleteError = deleteDialog?.querySelector('[data-delete-landing-error]');
    let deleteTrigger = null;
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-delete-landing]');
        if (trigger && deleteDialog && deleteForm) {
            deleteTrigger = trigger;
            deleteForm.action = trigger.dataset.deleteUrl;
            deleteForm.elements.namedItem('revision').value = trigger.dataset.deleteRevision;
            deleteDialog.querySelector('[data-delete-landing-name]').textContent = trigger.dataset.deleteName;
            deleteError.hidden = true;
            deleteDialog.showModal();
        }
        if (event.target.closest('[data-cancel-landing-delete]')) deleteDialog?.close();
    });
    deleteDialog?.addEventListener('close', () => deleteTrigger?.focus({ preventScroll: true }));
    deleteForm?.addEventListener('submit', (event) => {
        if (!document.dispatchEvent(new CustomEvent('landing:delete-confirmed', { cancelable: true }))) {
            event.preventDefault();
            deleteError.textContent = 'Espera a que termine la subida del video antes de eliminar la landing.';
            deleteError.hidden = false;
            return;
        }
        const confirmButton = deleteForm.querySelector('[type="submit"]');
        confirmButton.disabled = true;
        confirmButton.textContent = 'Moviendo a papelera…';
    });

    const picker = document.querySelector('#media-picker');
    let activeImageControl = null;
    document.addEventListener('click', (event) => {
        const pickButton = event.target.closest('[data-pick-image]');
        if (pickButton) {
            activeImageControl = pickButton.closest('.image-control');
            picker.showModal();
        }
        if (event.target.closest('[data-close-dialog]')) picker?.close();
        const imageButton = event.target.closest('[data-image]');
        if (imageButton && activeImageControl) {
            activeImageControl.querySelector('input').value = imageButton.dataset.image;
            const thumbnail = activeImageControl.querySelector('img');
            thumbnail.src = imageButton.dataset.src;
            thumbnail.classList.remove('placeholder-image');
            activeImageControl.querySelector('input').dispatchEvent(new Event('input', { bubbles: true }));
            markDirty();
            picker.close();
        }
        const clearButton = event.target.closest('[data-clear-image]');
        if (clearButton) {
            const control = clearButton.closest('.image-control');
            control.querySelector('input').value = '';
            control.querySelector('img').classList.add('placeholder-image');
            control.querySelector('input').dispatchEvent(new Event('input', { bubbles: true }));
            markDirty();
        }

        const addButton = event.target.closest('[data-add-item]');
        if (addButton) {
            const repeater = addButton.closest('.repeater');
            const list = repeater.querySelector(':scope > .repeater-items');
            if (list.children.length >= Number(repeater.dataset.max)) return;
            const index = Number(repeater.dataset.nextIndex);
            repeater.dataset.nextIndex = String(index + 1);
            const container = document.createElement('div');
            container.innerHTML = repeater.querySelector(':scope > template').innerHTML.replaceAll(repeater.dataset.itemToken || '__INDEX__', String(index));
            list.append(container.firstElementChild);
            list.dispatchEvent(new Event('input', { bubbles: true }));
            markDirty();
        }
        const itemButton = event.target.closest('[data-remove-item], [data-item-up], [data-item-down]');
        if (itemButton) {
            event.preventDefault();
            const item = itemButton.closest('.repeater-item');
            const list = item.parentElement;
            if (itemButton.hasAttribute('data-remove-item')) {
                if (list.children.length <= Number(item.closest('.repeater').dataset.min)) return;
                if (item.closest('[data-landing-editor]') && !window.confirm('¿Quitar este contenido del borrador? El cambio se aplicará al público después de guardar y publicar.')) return;
                item.remove();
            } else if (itemButton.hasAttribute('data-item-up') && item.previousElementSibling) {
                list.insertBefore(item, item.previousElementSibling);
            } else if (itemButton.hasAttribute('data-item-down') && item.nextElementSibling) {
                list.insertBefore(item.nextElementSibling, item);
            }
            list.dispatchEvent(new Event('input', { bubbles: true }));
            markDirty();
        }
    });

    document.querySelector('#media-search')?.addEventListener('input', (event) => {
        const query = event.target.value.toLocaleLowerCase();
        document.querySelectorAll('.picker-image').forEach((button) => {
            button.hidden = !button.dataset.name.toLocaleLowerCase().includes(query);
        });
    });

    const stage = document.querySelector('.preview-stage');
    const canvas = document.querySelector('.preview-canvas');
    const preview = document.querySelector('#site-preview');
    let mode = 'desktop';
    const resizePreview = () => {
        if (!stage || !canvas || !stage.clientWidth || !stage.clientHeight) return;
        const width = mode === 'mobile' ? 390 : 1280;
        const scale = Math.min(stage.clientWidth / width, 1);
        canvas.style.width = `${width}px`;
        canvas.style.height = `${stage.clientHeight / scale}px`;
        canvas.style.transform = `scale(${scale})`;
        canvas.style.left = `${(stage.clientWidth - width * scale) / 2}px`;
    };
    if (stage) new ResizeObserver(resizePreview).observe(stage);
    document.querySelectorAll('[data-device]').forEach((button) => button.addEventListener('click', () => {
        mode = button.dataset.device;
        document.querySelectorAll('[data-device]').forEach((item) => item.classList.toggle('active', item === button));
        resizePreview();
    }));
    let selecting = false;
    const selectButton = document.querySelector('#select-section');
    selectButton?.addEventListener('click', () => {
        selecting = !selecting;
        selectButton.textContent = selecting ? '⌖ Haz clic en una sección del sitio' : '⌖ Seleccionar una sección en la vista';
        preview.contentWindow.postMessage({ type: 'editor:select', enabled: selecting }, window.location.origin);
    });
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.source !== preview?.contentWindow || event.data?.type !== 'editor:section') return;
        const section = event.data.section;
        if (typeof section !== 'string' || !/^[a-z_]+$/.test(section)) return;
        window.location.href = `${document.querySelector('[data-editor-root]').dataset.editorRoot}/${section}`;
    });
    preview?.addEventListener('load', () => {
        selecting = false;
        if (selectButton) selectButton.textContent = '⌖ Seleccionar una sección en la vista';
        resizePreview();
        preview.contentWindow.postMessage({ type: 'editor:focus', section: preview.dataset.sectionTarget }, window.location.origin);
    });
    resizePreview();
});
