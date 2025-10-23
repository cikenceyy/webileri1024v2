const moduleInitialisers = {
    'lazy-media': lazyMedia,
    'reveal': revealElements,
    'map-on-demand': mapOnDemand,
    'contact-form': contactForm,
    'nav-toggle': navToggle,
    'nav-list': () => {},
    'sticky-header': stickyHeader,
    'light-gallery': lightGallery,
};

export function initModules(root = document) {
    const elements = root.querySelectorAll('[data-module]');
    elements.forEach((element) => {
        const modules = element.getAttribute('data-module')?.split(/\s+/) ?? [];
        modules.forEach((name) => {
            const init = moduleInitialisers[name];
            if (typeof init === 'function') {
                init(element);
            }
        });
    });
}

function lazyMedia(element) {
    const images = element.querySelectorAll('img');
    images.forEach((img) => {
        if (!img.hasAttribute('loading')) {
            img.loading = 'lazy';
        }
    });
}

function revealElements(element) {
    if (!('IntersectionObserver' in window)) {
        element.classList.add('is-visible');
        return;
    }

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: prefersReduced ? 0 : 0.2 });

    observer.observe(element);
}

function mapOnDemand(element) {
    const src = element.dataset.mapSrc;
    if (!src) return;
    const button = element.querySelector('[data-map-trigger]');
    const placeholder = element.querySelector('.map-placeholder');
    if (!button || !placeholder) return;

    button.addEventListener('click', () => {
        placeholder.innerHTML = src;
        placeholder.classList.add('is-loaded');
        button.remove();
    }, { once: true });
}

function contactForm(element) {
    const form = element.closest('form');
    if (!form) return;

    form.addEventListener('submit', () => {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.setAttribute('disabled', 'disabled');
        }
    });
}

function navToggle(button) {
    const targetId = button.getAttribute('aria-controls');
    const target = document.getElementById(targetId);
    if (!target) return;

    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!expanded));
        target.style.display = expanded ? 'none' : 'block';
    });
}

function stickyHeader(element) {
    let lastScroll = window.scrollY;
    window.addEventListener('scroll', () => {
        const current = window.scrollY;
        element.classList.toggle('is-condensed', current > 64 && current > lastScroll);
        lastScroll = current;
    });
}

function lightGallery(element) {
    const mainImage = element.querySelector('.gallery-main img');
    const buttons = element.querySelectorAll('.gallery-thumbs .thumb');
    if (!mainImage || !buttons.length) return;

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const src = button.dataset.gallerySrc;
            if (src) {
                mainImage.src = src;
                mainImage.srcset = `${src} 960w`;
            }
        });
    });
}
