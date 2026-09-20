// Enough JavaScript to dismiss a flash message and confirm a destructive form.
// A dashboard that needs a framework to delete a row does not need a framework.

document.addEventListener('click', (event) => {
    const dismiss = event.target.closest('[data-dismiss]');

    if (dismiss) {
        dismiss.closest('[data-flash]')?.remove();
    }
});

document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});
