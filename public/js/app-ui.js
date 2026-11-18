document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-auto-dismiss]').forEach((alert) => {
        window.setTimeout(() => {
            alert.classList.add('alert-hide');
            window.setTimeout(() => alert.remove(), 300);
        }, 3200);
    });
});
