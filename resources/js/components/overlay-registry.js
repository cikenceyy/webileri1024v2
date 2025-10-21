const stack = [];
let escapeBound = false;

const bindEscape = () => {
    if (escapeBound) return;
    escapeBound = true;
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        for (let index = stack.length - 1; index >= 0; index -= 1) {
            const entry = stack[index];
            if (!entry.escClosable) continue;
            event.preventDefault();
            entry.close({ reason: 'escape', restore: true });
            break;
        }
    });
};

export const registerOverlay = ({ id, close, escClosable }) => {
    bindEscape();
    const entry = { id, close, escClosable, openedAt: Date.now() };
    stack.push(entry);
    stack.sort((a, b) => a.openedAt - b.openedAt);
    return () => {
        const index = stack.findIndex((item) => item === entry || item.id === id);
        if (index >= 0) {
            stack.splice(index, 1);
        }
    };
};

export const updateOverlayClosable = (id, escClosable) => {
    const entry = stack.find((item) => item.id === id);
    if (entry) {
        entry.escClosable = escClosable;
    }
};
