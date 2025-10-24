export function tocObserver(element) {
    if (!element) {
        return;
    }

    const links = Array.from(element.querySelectorAll('a[href^="#"]'));
    if (!links.length) {
        return;
    }

    const targetSelector = element.dataset.tocTarget;
    const contentRoot = targetSelector ? document.querySelector(targetSelector) : element.nextElementSibling;

    if (!contentRoot) {
        return;
    }

    contentRoot.setAttribute('data-toc-bound', 'true');

    const sections = links
        .map((link) => document.getElementById(link.getAttribute('href').slice(1)))
        .filter(Boolean);

    if (!sections.length) {
        return;
    }

    const activateLink = (section) => {
        const activeLink = links.find((link) => link.getAttribute('href').slice(1) === section.id);
        if (!activeLink) {
            return;
        }

        links.forEach((link) => link.classList.toggle('is-active', link === activeLink));
        element.setAttribute('data-active-section', section.id);
    };

    if (!('IntersectionObserver' in window)) {
        activateLink(sections[0]);
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                activateLink(entry.target);
            }
        });
    }, {
        threshold: 0.4,
        rootMargin: '-30% 0px -50% 0px',
    });

    sections.forEach((section) => observer.observe(section));
}
