document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    const closeMenu = () => {
        if (!menuButton || !navMenu) return;
        menuButton.classList.remove('active');
        navMenu.classList.remove('active');
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.setAttribute('aria-label', 'Abrir menú');
        document.body.classList.remove('menu-open');
    };

    menuButton?.addEventListener('click', () => {
        const isOpen = !navMenu.classList.contains('active');
        menuButton.classList.toggle('active', isOpen);
        navMenu.classList.toggle('active', isOpen);
        menuButton.setAttribute('aria-expanded', String(isOpen));
        menuButton.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
        document.body.classList.toggle('menu-open', isOpen);
    });

    navMenu?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeMenu));
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) closeMenu();
    });

    const initSlider = ({ slideSelector, dotSelector, interval = 6500 }) => {
        const slides = [...document.querySelectorAll(slideSelector)];
        const dots = [...document.querySelectorAll(dotSelector)];
        if (slides.length < 2 || slides.length !== dots.length) return;

        let activeIndex = 0;
        let timer;

        const show = (index) => {
            activeIndex = (index + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => slide.classList.toggle('active', slideIndex === activeIndex));
            dots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === activeIndex;
                dot.classList.toggle('active', isActive);
                dot.setAttribute('aria-pressed', String(isActive));
            });
        };

        const start = () => {
            window.clearInterval(timer);
            timer = window.setInterval(() => show(activeIndex + 1), interval);
        };

        dots.forEach((dot, index) => dot.addEventListener('click', () => {
            show(index);
            start();
        }));

        show(0);
        start();
    };

    initSlider({ slideSelector: '.hero-slide', dotSelector: '.hero-dots .dot', interval: 7000 });
    initSlider({ slideSelector: '.testimonial-card', dotSelector: '.testimonial-dots .t-dot', interval: 8000 });

    const filterButtons = [...document.querySelectorAll('.filter-btn')];
    const galleryCards = [...document.querySelectorAll('.gallery-card')];

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            filterButtons.forEach((item) => {
                const isActive = item === button;
                item.classList.toggle('active', isActive);
                item.setAttribute('aria-pressed', String(isActive));
            });
            galleryCards.forEach((card) => {
                card.hidden = filter !== 'all' && card.dataset.category !== filter;
            });
        });
    });

    const lightbox = document.querySelector('#lightbox');
    const lightboxImage = lightbox?.querySelector('.lightbox-image');
    const lightboxCaption = lightbox?.querySelector('.lightbox-caption');
    let currentImageIndex = 0;
    let triggerElement = null;

    const visibleCards = () => galleryCards.filter((card) => !card.hidden);

    const renderLightbox = () => {
        const cards = visibleCards();
        if (!cards.length || !lightboxImage || !lightboxCaption) return;
        currentImageIndex = (currentImageIndex + cards.length) % cards.length;
        const image = cards[currentImageIndex].querySelector('img');
        lightboxImage.src = image.src;
        lightboxImage.alt = image.alt;
        lightboxCaption.textContent = cards[currentImageIndex].dataset.caption || image.alt;
    };

    const openLightbox = (card) => {
        if (!lightbox) return;
        const cards = visibleCards();
        currentImageIndex = cards.indexOf(card);
        triggerElement = card;
        renderLightbox();
        lightbox.classList.add('active');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lightbox-open');
        lightbox.querySelector('.lightbox-close')?.focus();
    };

    const closeLightbox = () => {
        if (!lightbox) return;
        lightbox.classList.remove('active');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lightbox-open');
        triggerElement?.focus();
    };

    galleryCards.forEach((card) => card.addEventListener('click', () => openLightbox(card)));
    lightbox?.querySelector('.lightbox-close')?.addEventListener('click', closeLightbox);
    lightbox?.querySelector('.lightbox-prev')?.addEventListener('click', () => {
        currentImageIndex -= 1;
        renderLightbox();
    });
    lightbox?.querySelector('.lightbox-next')?.addEventListener('click', () => {
        currentImageIndex += 1;
        renderLightbox();
    });
    lightbox?.addEventListener('click', (event) => {
        if (event.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', (event) => {
        if (!lightbox?.classList.contains('active')) return;
        if (event.key === 'Escape') closeLightbox();
        if (event.key === 'ArrowLeft') {
            currentImageIndex -= 1;
            renderLightbox();
        }
        if (event.key === 'ArrowRight') {
            currentImageIndex += 1;
            renderLightbox();
        }
    });
});
