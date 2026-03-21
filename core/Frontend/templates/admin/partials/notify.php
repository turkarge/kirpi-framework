<div id="kirpiNotifyRoot" class="kirpi-notify-root" aria-live="polite" aria-atomic="true"></div>
<style>
    .kirpi-notify-root {
        position: fixed;
        right: 16px;
        bottom: 16px;
        display: grid;
        gap: 10px;
        z-index: 9999;
        width: min(360px, calc(100vw - 24px));
    }
    .kirpi-toast {
        border-radius: 12px;
        border: 1px solid var(--line);
        background: #fff;
        color: var(--ink);
        box-shadow: 0 10px 20px rgba(16, 42, 67, 0.15);
        padding: 10px 12px;
        transform: translateY(10px);
        opacity: 0;
        transition: opacity .18s ease, transform .18s ease;
    }
    .kirpi-toast.show {
        opacity: 1;
        transform: translateY(0);
    }
    .kirpi-toast-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 4px;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: var(--muted);
    }
    .kirpi-toast-message {
        margin: 0;
        font-size: 14px;
        line-height: 1.35;
    }
    .kirpi-toast-close {
        border: 0;
        background: transparent;
        font-size: 14px;
        color: inherit;
        cursor: pointer;
        opacity: .7;
    }
    .kirpi-toast-close:hover { opacity: 1; }
    .kirpi-toast-success { border-color: #9ae6b4; background: #f0fff4; }
    .kirpi-toast-error { border-color: #feb2b2; background: #fff5f5; }
    .kirpi-toast-info { border-color: #90cdf4; background: #ebf8ff; }
    .kirpi-toast-warning { border-color: #fbd38d; background: #fffaf0; }
</style>
<script>
    (() => {
        const root = document.getElementById('kirpiNotifyRoot');
        if (!root || window.kirpiNotify) return;

        const defaultDuration = 3500;

        function removeToast(node) {
            if (!node) return;
            node.classList.remove('show');
            setTimeout(() => node.remove(), 160);
        }

        function createToast(type, message, options = {}) {
            const toast = document.createElement('div');
            toast.className = 'kirpi-toast kirpi-toast-' + type;
            toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

            const header = document.createElement('div');
            header.className = 'kirpi-toast-header';

            const title = document.createElement('strong');
            title.textContent = options.title || type;

            const close = document.createElement('button');
            close.className = 'kirpi-toast-close';
            close.type = 'button';
            close.textContent = 'x';
            close.addEventListener('click', () => removeToast(toast));

            header.appendChild(title);
            header.appendChild(close);

            const content = document.createElement('p');
            content.className = 'kirpi-toast-message';
            content.textContent = String(message || '');

            toast.appendChild(header);
            toast.appendChild(content);
            root.appendChild(toast);

            requestAnimationFrame(() => toast.classList.add('show'));

            const duration = Number(options.duration ?? defaultDuration);
            if (duration > 0) {
                setTimeout(() => removeToast(toast), duration);
            }
        }

        window.kirpiNotify = {
            show(type, message, options = {}) {
                createToast(type, message, options);
            },
            success(message, options = {}) {
                createToast('success', message, options);
            },
            error(message, options = {}) {
                createToast('error', message, options);
            },
            info(message, options = {}) {
                createToast('info', message, options);
            },
            warning(message, options = {}) {
                createToast('warning', message, options);
            },
            clear() {
                root.querySelectorAll('.kirpi-toast').forEach(removeToast);
            },
        };
    })();
</script>
