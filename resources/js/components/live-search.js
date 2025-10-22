const defaultNormalizer = (value) => (value || '').toString().trim().toLowerCase();

const createDebounce = (fn, delay = 150) => {
    let timer = null;
    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => fn(...args), delay);
    };
};

export const normalizeTerm = (value) => defaultNormalizer(value);

export const initLiveSearch = (scope, options = {}) => {
    const {
        debounce = 150,
        onLocal = () => {},
        onRemote = () => {},
        onTermChange = () => {},
        shouldUseRemote = () => false,
    } = options;

    if (!scope) {
        return null;
    }

    const form = scope.matches('form') ? scope : scope.querySelector('[data-search-form]') || scope.querySelector('form');
    const input = scope.querySelector('[data-search-input]') || scope.querySelector('input[type="search"]');

    if (!input) {
        return null;
    }

    const run = (term) => {
        const normalized = defaultNormalizer(term);
        onTermChange(normalized);

        if (shouldUseRemote(normalized)) {
            onRemote(normalized);
        } else {
            onLocal(normalized);
        }
    };

    if (form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            run(input.value);
        });
    }

    const debounced = createDebounce(() => run(input.value), debounce);
    input.addEventListener('input', debounced);

    return {
        run,
    };
};
