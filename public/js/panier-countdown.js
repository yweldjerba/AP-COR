document.addEventListener('DOMContentLoaded', () => {
    const countdown = document.querySelector('[data-cart-countdown]');
    if (!countdown) return;

    const expiration = new Date(countdown.dataset.expiration).getTime();
    const label = countdown.querySelector('[data-countdown-label]');

    const update = () => {
        const remaining = expiration - Date.now();
        if (remaining <= 0) {
            label.textContent = 'Expiré — actualisation…';
            countdown.classList.add('is-expired');
            window.setTimeout(() => window.location.reload(), 1200);
            return;
        }

        const totalSeconds = Math.floor(remaining / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        label.textContent = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        if (remaining <= 15 * 60 * 1000) countdown.classList.add('is-warning');
    };

    update();
    window.setInterval(update, 1000);
});
