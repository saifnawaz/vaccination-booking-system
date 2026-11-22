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

// Skeleton while a list reloads: filter forms (data-filter) and pagination links swap
// the page's <x-list-region> for placeholder rows until the next page arrives.
function showListSkeleton() {
    document.querySelectorAll('[data-list-region]').forEach((region) => {
        region.setAttribute('aria-busy', 'true');
        region.querySelector('[data-list-content]')?.setAttribute('hidden', '');
        region.querySelector('[data-list-skeleton]')?.removeAttribute('hidden');
    });
}

document.addEventListener('submit', (event) => {
    if (event.target instanceof HTMLFormElement && event.target.hasAttribute('data-filter') && !event.defaultPrevented) {
        showListSkeleton();
    }
});

document.addEventListener('click', (event) => {
    const link = event.target instanceof Element
        ? event.target.closest('nav[aria-label="Pagination"] a[href], a[data-filter-link]')
        : null;

    if (link && !event.ctrlKey && !event.metaKey && !event.shiftKey && event.button === 0) {
        showListSkeleton();
    }
});

// Restore buttons if the page is shown again from the back/forward cache.
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('[data-list-region][aria-busy="true"]').forEach((region) => {
        region.removeAttribute('aria-busy');
        region.querySelector('[data-list-content]')?.removeAttribute('hidden');
        region.querySelector('[data-list-skeleton]')?.setAttribute('hidden', '');
    });

    document.querySelectorAll('form[aria-busy="true"]').forEach((form) => {
        form.removeAttribute('aria-busy');
        form.querySelectorAll('button[data-loading]').forEach((button) => {
            button.disabled = false;
            button.removeAttribute('data-loading');
        });
    });
});
