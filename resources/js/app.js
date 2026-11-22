import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Loading state for every form: once a form is submitted, disable its submit
// buttons and show their spinner. Prevents double submits (e.g. booking twice).
// The browser only fires `submit` after native validation passes.
// Opt out with <form data-no-loading>.
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-no-loading') || event.defaultPrevented) {
        return;
    }

    form.setAttribute('aria-busy', 'true');

    // Deferred: the browser builds the form data after this event and skips
    // disabled controls, so disabling now would drop the clicked button's name/value.
    setTimeout(() => {
        form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
            button.disabled = true;
            button.setAttribute('data-loading', '');
        });
    }, 0);
});

// Restore buttons if the page is shown again from the back/forward cache.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('form[aria-busy="true"]').forEach((form) => {
        form.removeAttribute('aria-busy');
        form.querySelectorAll('button[data-loading]').forEach((button) => {
            button.disabled = false;
            button.removeAttribute('data-loading');
        });
    });
});
