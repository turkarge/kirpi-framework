<div class="modal modal-blur fade" id="kirpiModalRoot" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kirpiModalTitle">Kirpi Modal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="kirpiModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-1" id="kirpiModalCancelBtn" data-bs-dismiss="modal">Vazgec</button>
                <button type="button" class="btn btn-primary" id="kirpiModalConfirmBtn">Tamam</button>
            </div>
        </div>
    </div>
</div>
<script>
    (() => {
        const modalNode = document.getElementById('kirpiModalRoot');
        if (!modalNode || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }

        const titleNode = document.getElementById('kirpiModalTitle');
        const bodyNode = document.getElementById('kirpiModalBody');
        const confirmBtn = document.getElementById('kirpiModalConfirmBtn');
        const cancelBtn = document.getElementById('kirpiModalCancelBtn');
        if (!titleNode || !bodyNode || !confirmBtn || !cancelBtn) {
            return;
        }

        const modal = new bootstrap.Modal(modalNode);
        let current = {
            onConfirm: null,
            onCancel: null,
            closeOnConfirm: true,
        };

        const resetButtons = () => {
            confirmBtn.className = 'btn btn-primary';
            cancelBtn.classList.remove('d-none');
            cancelBtn.textContent = 'Vazgec';
            confirmBtn.textContent = 'Tamam';
        };

        const show = (options = {}) => {
            resetButtons();

            titleNode.textContent = String(options.title || 'Kirpi Modal');
            if (options.bodyHtml) {
                bodyNode.innerHTML = String(options.bodyHtml);
            } else {
                bodyNode.textContent = String(options.message || '');
            }

            if (typeof options.cancelText === 'string' && options.cancelText !== '') {
                cancelBtn.textContent = options.cancelText;
            }
            if (typeof options.confirmText === 'string' && options.confirmText !== '') {
                confirmBtn.textContent = options.confirmText;
            }
            if (options.hideCancel === true) {
                cancelBtn.classList.add('d-none');
            }

            const variant = String(options.variant || 'primary');
            confirmBtn.className = 'btn btn-' + variant;

            current = {
                onConfirm: typeof options.onConfirm === 'function' ? options.onConfirm : null,
                onCancel: typeof options.onCancel === 'function' ? options.onCancel : null,
                closeOnConfirm: options.closeOnConfirm !== false,
            };

            modal.show();
        };

        confirmBtn.addEventListener('click', async () => {
            if (current.onConfirm) {
                const result = await current.onConfirm();
                if (result === false) {
                    return;
                }
            }
            if (current.closeOnConfirm) {
                modal.hide();
            }
        });

        cancelBtn.addEventListener('click', () => {
            if (current.onCancel) {
                current.onCancel();
            }
        });

        modalNode.addEventListener('hidden.bs.modal', () => {
            bodyNode.textContent = '';
            current = {
                onConfirm: null,
                onCancel: null,
                closeOnConfirm: true,
            };
            resetButtons();
        });

        window.kirpiModal = {
            show,
            alert(message, options = {}) {
                show({
                    title: options.title || 'Bilgi',
                    message,
                    confirmText: options.confirmText || 'Tamam',
                    hideCancel: true,
                    variant: options.variant || 'primary',
                });
            },
            confirm(message, options = {}) {
                return new Promise((resolve) => {
                    show({
                        title: options.title || 'Onay',
                        message,
                        confirmText: options.confirmText || 'Onayla',
                        cancelText: options.cancelText || 'Iptal',
                        variant: options.variant || 'primary',
                        onConfirm: () => {
                            resolve(true);
                        },
                        onCancel: () => {
                            resolve(false);
                        },
                    });
                });
            },
            hide() {
                modal.hide();
            },
        };

        window.dispatchEvent(new CustomEvent('kirpi:modal-ready'));
    })();
</script>
