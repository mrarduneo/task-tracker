// Client-side convenience layer for Task Tracker.
// Every rule here is checked again on the server, so switching JavaScript off
// never bypasses validation.

// 1. Task form: block an empty title and show a live character counter.
const titleInput = document.getElementById('title');
if (titleInput) {
    const error = document.getElementById('title-error');
    const count = document.getElementById('title-count');

    const updateCount = () => {
        count.textContent = titleInput.value.length;
    };

    titleInput.addEventListener('input', () => {
        updateCount();
        if (titleInput.value.trim() !== '') {
            error.hidden = true;
        }
    });

    titleInput.form.addEventListener('submit', (event) => {
        if (titleInput.value.trim() === '') {
            event.preventDefault();
            error.textContent = 'Title is required.';
            error.hidden = false;
            titleInput.focus();
        }
    });

    updateCount();
}

// 2. Ask for confirmation before any form that carries a data-confirm message (delete).
document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

// 3. Submit the filter form as soon as a dropdown changes.
document.querySelectorAll('form[data-autosubmit] select').forEach((select) => {
    select.addEventListener('change', () => select.form.submit());
});
