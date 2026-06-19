// Order Lifecycles & Countdown Timers for SiAGRI
document.addEventListener('DOMContentLoaded', () => {
    const pad = (n) => String(n).padStart(2, '0');

    document.querySelectorAll('[id^="timer-"]').forEach(el => {
        const expiredTimeStr = el.dataset.expired;
        if (!expiredTimeStr) return;

        // Parse UNIX timestamp or SQL datetime string format safely
        let expired;
        if (/^\d+$/.test(expiredTimeStr)) {
            expired = parseInt(expiredTimeStr) * 1000;
        } else {
            expired = new Date(expiredTimeStr.replace(' ', 'T')).getTime();
        }

        if (isNaN(expired)) return;

        const orderId = el.dataset.order || el.id.replace('timer-', '');

        const interval = setInterval(() => {
            const timeDiff = expired - Date.now();

            if (timeDiff <= 0) {
                clearInterval(interval);
                el.textContent = 'WAKTU HABIS';
                el.classList.remove('text-orange-500');
                el.classList.add('text-red-500');

                // Trigger server-side order cancellation if we have order ID context
                if (orderId && el.dataset.order) {
                    fetch('cancel-order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ order_id: orderId })
                    }).then(() => {
                        setTimeout(() => location.reload(), 1500);
                    }).catch(() => {
                        setTimeout(() => location.reload(), 1500);
                    });
                } else {
                    // Simple page reload to let server-side lazy check handle status change
                    setTimeout(() => location.reload(), 2000);
                }
                return;
            }

            const hours = Math.floor(timeDiff / 3600000);
            const minutes = Math.floor((timeDiff % 3600000) / 60000);
            const seconds = Math.floor((timeDiff % 60000) / 1000);

            el.textContent = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;

            // Highlight red when less than 1 hour remains
            if (timeDiff < 3600000) {
                el.classList.add('text-red-500');
                el.classList.remove('text-orange-500');
            }
        }, 1000);
    });
});
