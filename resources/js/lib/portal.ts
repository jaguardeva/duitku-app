/**
 * Svelte action to portal an element to document.body or a target selector.
 * Ensures overlays, modals, and dialogs escape local layout stacking contexts
 * and render above all fixed layout elements (such as bottom navigation bars and headers).
 */
export function portal(
    node: HTMLElement,
    target: string | HTMLElement = 'body',
) {
    let targetEl: HTMLElement | null = null;

    function mount() {
        if (typeof document === 'undefined') return;
        targetEl =
            typeof target === 'string'
                ? document.querySelector(target)
                : target;
        if (targetEl && node.parentNode !== targetEl) {
            targetEl.appendChild(node);
        }
    }

    mount();

    return {
        update(newTarget: string | HTMLElement) {
            target = newTarget;
            mount();
        },
        destroy() {
            if (node.parentNode) {
                node.parentNode.removeChild(node);
            }
        },
    };
}
