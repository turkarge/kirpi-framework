<?php
$kirpiFlashMessages = function_exists('flash_messages')
    ? flash_messages(true)
    : [];
?>
<div id="kirpiNotifyRoot" class="kirpi-notify-root" aria-live="polite" aria-atomic="true"></div>
<style>
    .kirpi-notify-root {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        display: grid;
        gap: .75rem;
        z-index: 1080;
        width: min(380px, calc(100vw - 1.5rem));
    }
    .kirpi-toast {
        border-radius: var(--tblr-border-radius, .5rem);
        border: var(--tblr-border-width, 1px) solid var(--tblr-border-color, #dce1e7);
        background: var(--tblr-bg-surface, #fff);
        color: var(--tblr-body-color, #182433);
        box-shadow: var(--tblr-box-shadow, 0 .25rem .75rem rgba(4, 32, 69, .1));
        padding: .625rem .75rem;
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
        gap: .625rem;
        margin-bottom: .25rem;
        font-size: .75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: var(--tblr-muted, #667382);
    }
    .kirpi-toast-message {
        margin: 0;
        font-size: .875rem;
        line-height: 1.4;
    }
    .kirpi-toast-close {
        border: 0;
        background: transparent;
        font-size: .875rem;
        color: inherit;
        cursor: pointer;
        opacity: .7;
        padding: 0;
        line-height: 1;
    }
    .kirpi-toast-close:hover { opacity: 1; }
    .kirpi-toast-success { border-color: color-mix(in srgb, var(--tblr-success, #2fb344) 42%, var(--tblr-border-color, #dce1e7)); background: color-mix(in srgb, var(--tblr-success, #2fb344) 14%, var(--tblr-bg-surface, #fff)); }
    .kirpi-toast-error { border-color: color-mix(in srgb, var(--tblr-danger, #d63939) 42%, var(--tblr-border-color, #dce1e7)); background: color-mix(in srgb, var(--tblr-danger, #d63939) 14%, var(--tblr-bg-surface, #fff)); }
    .kirpi-toast-info { border-color: color-mix(in srgb, var(--tblr-info, #4299e1) 42%, var(--tblr-border-color, #dce1e7)); background: color-mix(in srgb, var(--tblr-info, #4299e1) 14%, var(--tblr-bg-surface, #fff)); }
    .kirpi-toast-warning { border-color: color-mix(in srgb, var(--tblr-warning, #f59f00) 42%, var(--tblr-border-color, #dce1e7)); background: color-mix(in srgb, var(--tblr-warning, #f59f00) 16%, var(--tblr-bg-surface, #fff)); }
</style>
<script>
    (() => {
        const root = document.getElementById('kirpiNotifyRoot');
        if (!root) return;

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
            close.setAttribute('aria-label', 'Close notification');
            close.innerHTML = '&times;';
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
            fromApi(payload, options = {}) {
                const fallbackLevel = String(options.fallbackLevel || 'info');
                const fallbackTitle = options.fallbackTitle ? String(options.fallbackTitle) : undefined;

                if (!payload || typeof payload !== 'object') {
                    return false;
                }

                if (payload.notify && typeof payload.notify === 'object') {
                    const level = String(payload.notify.level || fallbackLevel);
                    const message = String(payload.notify.message || '');
                    const title = payload.notify.title ? String(payload.notify.title) : fallbackTitle;
                    if (message !== '') {
                        createToast(level, message, {title});
                        return true;
                    }
                }

                if (typeof payload.message === 'string' && payload.message !== '') {
                    const level = String(payload.level || fallbackLevel);
                    createToast(level, payload.message, {title: fallbackTitle});
                    return true;
                }

                if (typeof payload.error === 'string' && payload.error !== '') {
                    createToast('error', payload.error, {title: fallbackTitle || 'Error'});
                    return true;
                }

                if (payload.errors && typeof payload.errors === 'object') {
                    const firstField = Object.values(payload.errors)[0];
                    const firstError = Array.isArray(firstField) ? firstField[0] : firstField;
                    if (typeof firstError === 'string' && firstError !== '') {
                        createToast('warning', firstError, {title: fallbackTitle || 'Validation'});
                        return true;
                    }
                }

                return false;
            },
            clear() {
                root.querySelectorAll('.kirpi-toast').forEach(removeToast);
            },
        };

        window.kirpiApi = {
            async request(input, init = {}) {
                const notifyOnSuccess = init.notifyOnSuccess === true;
                const requestInit = {...init};
                delete requestInit.notifyOnSuccess;

                let response;
                try {
                    response = await fetch(input, requestInit);
                } catch (error) {
                    const notify = window.kirpiNotify;
                    const message = error instanceof Error ? error.message : 'Network error';
                    if (notify) {
                        notify.error(message, {title: 'Network'});
                    }

                    return {ok: false, status: 0, payload: {error: message}};
                }

                const contentType = String(response.headers.get('content-type') || '');
                const payload = contentType.includes('application/json')
                    ? await response.json()
                    : {message: await response.text()};

                const notify = window.kirpiNotify;
                if (notify && response.ok && notifyOnSuccess) {
                    notify.fromApi(payload, {fallbackLevel: 'success', fallbackTitle: 'Success'});
                }

                if (notify && !response.ok) {
                    notify.fromApi(payload, {fallbackLevel: 'error', fallbackTitle: 'Error'});
                }

                return {ok: response.ok, status: response.status, payload};
            },
        };

        window.dispatchEvent(new CustomEvent('kirpi:notify-ready'));

        const flashPayload = <?= json_encode($kirpiFlashMessages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        if (Array.isArray(flashPayload)) {
            flashPayload.forEach((item) => {
                const level = String(item?.level || 'info');
                const message = String(item?.message || '');
                const title = item?.title ? String(item.title) : undefined;
                createToast(level, message, {title});
            });
        }
    })();
</script>
