<div class="row g-3">
    <article class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Kirpi Modal Testi</h3></div>
            <div class="card-body">
                <p class="text-secondary">
                    Bu sayfa `window.kirpiModal` API'sini dogrulamak icin kullanilir.
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary" data-kirpi-modal-open="alert">Alert Modal</button>
                    <button type="button" class="btn btn-1" data-kirpi-modal-open="confirm">Confirm Modal</button>
                    <button type="button" class="btn btn-outline-secondary" data-kirpi-modal-open="custom">Custom Modal</button>
                </div>
                <div class="mt-3">
                    <pre id="modalTestOutput" class="mb-0 p-3 border bg-transparent" style="overflow:auto; max-height:240px;">Henuz modal islemi tetiklenmedi.</pre>
                </div>
            </div>
        </div>
    </article>
</div>
<script>
    (() => {
        const output = document.getElementById('modalTestOutput');
        if (!output) return;

        const write = (payload) => {
            output.textContent = JSON.stringify(payload, null, 2);
        };

        let bound = false;
        const bind = () => {
            if (bound) return;
            if (!window.kirpiModal) return;
            bound = true;

            document.querySelector('[data-kirpi-modal-open="alert"]')?.addEventListener('click', () => {
                window.kirpiModal.alert('Bu bir bilgilendirme modalidir.', {title: 'Alert Test'});
                write({type: 'alert', status: 'opened'});
            });

            document.querySelector('[data-kirpi-modal-open="confirm"]')?.addEventListener('click', async () => {
                const confirmed = await window.kirpiModal.confirm('Bu kaydi onaylamak istiyor musun?', {
                    title: 'Confirm Test',
                    confirmText: 'Evet',
                    cancelText: 'Hayir',
                });
                write({type: 'confirm', confirmed});
            });

            document.querySelector('[data-kirpi-modal-open="custom"]')?.addEventListener('click', () => {
                window.kirpiModal.show({
                    title: 'Custom Modal',
                    bodyHtml: '<div class="alert alert-info mb-0">Custom icerik ile render edildi.</div>',
                    confirmText: 'Kapat',
                    hideCancel: true,
                    variant: 'primary',
                });
                write({type: 'custom', status: 'opened'});
            });
        };

        bind();
        window.addEventListener('kirpi:modal-ready', bind, {once: true});
    })();
</script>
