export function initDateRangeControls(root) {
    if (!root) {
        return;
    }

    const ranges = root.querySelectorAll('[data-tablekit-filter="date-range"]');

    ranges.forEach((wrapper) => {
        const inputs = wrapper.querySelectorAll('input[type="date"]');

        if (inputs.length !== 2) {
            return;
        }

        const [fromInput, toInput] = inputs;
        const rootLocale = typeof root.getAttribute === 'function' ? root.getAttribute('data-locale') : null;
        const locale = wrapper.getAttribute('data-locale')
            || rootLocale
            || document.documentElement.lang
            || 'en';

        if (locale) {
            fromInput.lang = locale;
            toInput.lang = locale;
        }

        const enforceOrder = () => {
            const fromValue = fromInput.value;
            const toValue = toInput.value;

            if (fromValue && toValue && fromValue > toValue) {
                toInput.value = fromValue;
            }
        };

        fromInput.addEventListener('change', enforceOrder);
        toInput.addEventListener('change', enforceOrder);
    });
}
