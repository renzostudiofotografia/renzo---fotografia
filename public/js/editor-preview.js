document.addEventListener('DOMContentLoaded', () => {
    let selecting = false;
    let selectedSection = new URL(window.location.href).searchParams.get('editor_section');
    document.documentElement.classList.add('editor-preview');

    const focusSelectedSection = () => {
        if (typeof selectedSection !== 'string' || !/^[a-z_]+$/.test(selectedSection)) return;
        const section = [...document.querySelectorAll('[data-editor-section]')]
            .find((element) => element.dataset.editorSection === selectedSection);
        if (!section) return;

        document.querySelectorAll('.editor-section-selected').forEach((element) => element.classList.remove('editor-section-selected'));
        section.classList.add('editor-section-selected');
        const headerHeight = document.querySelector('.header')?.getBoundingClientRect().height || 0;
        const sectionTop = section.getBoundingClientRect().top + window.scrollY - headerHeight - 16;
        window.scrollTo({ top: Math.max(0, sectionTop), behavior: 'auto' });
    };
    const scheduleSectionFocus = () => window.requestAnimationFrame(focusSelectedSection);

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.source !== window.parent) return;
        if (event.data?.type === 'editor:focus') {
            selectedSection = event.data.section;
            scheduleSectionFocus();
        }
        if (event.data?.type === 'editor:select') {
            selecting = Boolean(event.data.enabled);
            document.body.classList.toggle('editor-selecting', selecting);
        }
    });
    document.addEventListener('click', (event) => {
        const section = event.target.closest('[data-editor-section]');
        if (selecting && section) {
            event.preventDefault();
            event.stopImmediatePropagation();
            window.parent.postMessage({ type: 'editor:section', section: section.dataset.editorSection }, window.location.origin);
        }
    }, true);
    document.querySelectorAll('a[href]').forEach((link) => {
        const url = new URL(link.href, window.location.href);
        if (url.origin === window.location.origin && !url.pathname.startsWith('/admin') && !link.getAttribute('href').startsWith('#')) {
            url.searchParams.set('editor_preview', '1');
            link.href = url.toString();
        }
    });
    document.querySelectorAll('form').forEach((form) => form.addEventListener('submit', (event) => event.preventDefault()));
    scheduleSectionFocus();
    window.addEventListener('load', scheduleSectionFocus, { once: true });
    window.addEventListener('resize', scheduleSectionFocus);
    document.fonts?.ready.then(scheduleSectionFocus);
});
