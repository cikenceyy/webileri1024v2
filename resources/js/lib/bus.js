const listeners = new Map();

export const on = (event, callback) => {
    const list = listeners.get(event) ?? [];
    list.push(callback);
    listeners.set(event, list);
    return () => off(event, callback);
};

export const off = (event, callback) => {
    const list = listeners.get(event);
    if (!list) return;
    listeners.set(event, list.filter((item) => item !== callback));
};

export const emit = (event, detail = {}) => {
    const list = listeners.get(event) ?? [];
    list.forEach((callback) => callback(detail));
};

export default { on, off, emit };
