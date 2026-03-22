<?php
$kirpiFlashMessages = function_exists('flash_messages')
    ? flash_messages(true)
    : [];
?>
<div id="kirpiNotifyRoot" class="kirpi-notify-root" aria-live="polite" aria-atomic="true"></div>
<script>
    (() => {
        const root = document.getElementById('kirpiNotifyRoot');
        if (!root) return;

        const defaultDuration = 3500;
        const ToastClass = window.bootstrap?.Toast || window.tabler?.bootstrap?.Toast;
        const typeStyleMap = {
            success: { toastClass: 'bg-success-lt', dotClass: 'bg-green', title: 'Success' },
            error: { toastClass: 'bg-danger-lt', dotClass: 'bg-red', title: 'Error' },
            info: { toastClass: 'bg-azure-lt', dotClass: 'bg-azure', title: 'Info' },
            warning: { toastClass: 'bg-warning-lt', dotClass: 'bg-yellow', title: 'Warning' },
        };
        root.className = 'toast-container position-fixed bottom-0 end-0 p-3 kirpi-notify-root';

        function removeToast(node, immediate = false) {
            if (!node) return;

            if (immediate) {
                node.remove();
                return;
            }

            if (ToastClass) {
                const instance = ToastClass.getInstance(node);
                if (instance) {
                    instance.hide();
                    return;
                }
            }

            node.remove();
        }

        function createToast(type, message, options = {}) {
            const resolvedType = String(type || 'info').toLowerCase();
            const style = typeStyleMap[resolvedType] || typeStyleMap.info;
            const toast = document.createElement('div');
            toast.className = 'toast kirpi-toast border-0 ' + style.toastClass;
            toast.setAttribute('role', resolvedType === 'error' ? 'alert' : 'status');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            const header = document.createElement('div');
            header.className = 'toast-header';

            const dot = document.createElement('span');
            dot.className = 'status-dot d-block me-2 ' + style.dotClass;

            const title = document.createElement('strong');
            title.className = 'me-auto';
            title.textContent = options.title || style.title;

            const time = document.createElement('small');
            time.className = 'text-secondary';
            time.textContent = 'now';

            const close = document.createElement('button');
            close.className = 'ms-2 btn-close';
            close.type = 'button';
            close.setAttribute('data-bs-dismiss', 'toast');
            close.setAttribute('aria-label', 'Close notification');
            close.addEventListener('click', () => removeToast(toast));

            header.appendChild(dot);
            header.appendChild(title);
            header.appendChild(time);
            header.appendChild(close);

            const content = document.createElement('div');
            content.className = 'toast-body';
            content.textContent = String(message || '');

            toast.appendChild(header);
            toast.appendChild(content);
            root.appendChild(toast);

            const duration = Number(options.duration ?? defaultDuration);
            if (ToastClass) {
                const instance = ToastClass.getOrCreateInstance(toast, {
                    autohide: duration > 0,
                    delay: duration > 0 ? duration : defaultDuration,
                });

                toast.addEventListener('hidden.bs.toast', () => toast.remove(), {once: true});
                instance.show();
            } else {
                toast.classList.add('show');
                if (duration > 0) {
                    setTimeout(() => removeToast(toast), duration);
                }
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
                root.querySelectorAll('.kirpi-toast').forEach((node) => removeToast(node, true));
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
