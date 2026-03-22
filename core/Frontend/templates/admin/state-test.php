<div class="row g-3">
    <article class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Empty State</h3></div>
            <div class="card-body d-flex align-items-center">
                <div class="empty w-100">
                    <div class="empty-img">
                        <img src="/vendor/tabler/static/illustrations/undraw_printing_invoices_5r4r.svg" height="112" alt="">
                    </div>
                    <p class="empty-title">Kayit bulunamadi</p>
                    <p class="empty-subtitle text-secondary">
                        Filtre sonucunda kayit yok. Yeni kayit olustur veya filtreyi temizle.
                    </p>
                    <div class="empty-action">
                        <button type="button" class="btn btn-primary" data-kirpi-state-action="empty-create">Yeni Kayit</button>
                    </div>
                </div>
            </div>
        </div>
    </article>

    <article class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Loading State</h3></div>
            <div class="card-body">
                <div class="placeholder-glow mb-3">
                    <span class="placeholder col-7"></span>
                    <span class="placeholder col-4"></span>
                    <span class="placeholder col-10"></span>
                    <span class="placeholder col-8"></span>
                </div>
                <div class="d-flex align-items-center gap-2 text-secondary">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span>Veriler yukleniyor...</span>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-1" data-kirpi-state-action="loading-done">Yukleme Tamamla</button>
                </div>
            </div>
        </div>
    </article>

    <article class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-header"><h3 class="card-title">Error State</h3></div>
            <div class="card-body">
                <div class="alert alert-danger" role="alert">
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon icon-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 9v4" />
                                <path d="M12 16v.01" />
                                <path d="M5.07 19h13.86a2 2 0 0 0 1.75 -2.98l-6.93 -12.02a2 2 0 0 0 -3.5 0l-6.93 12.02a2 2 0 0 0 1.75 2.98" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="alert-title">Islem basarisiz</h4>
                            <div class="text-secondary">Servis gecici olarak cevap veremiyor. Daha sonra tekrar dene.</div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-danger" data-kirpi-state-action="error-retry">Tekrar Dene</button>
            </div>
        </div>
    </article>

    <article class="col-12">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Durum Simulasyonu</h3></div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap mb-3">
                    <button type="button" class="btn btn-1" data-kirpi-state-view="empty">Empty</button>
                    <button type="button" class="btn btn-1" data-kirpi-state-view="loading">Loading</button>
                    <button type="button" class="btn btn-1" data-kirpi-state-view="error">Error</button>
                    <button type="button" class="btn btn-primary" data-kirpi-state-view="success">Success</button>
                </div>
                <div id="kirpiStatePreview" class="border rounded p-3">
                    Empty state secildi.
                </div>
                <pre id="stateTestOutput" class="mt-3 mb-0 p-3 border bg-transparent" style="overflow:auto; max-height:240px;">Henuz aksiyon tetiklenmedi.</pre>
            </div>
        </div>
    </article>
</div>

<script>
    (() => {
        const preview = document.getElementById('kirpiStatePreview');
        const output = document.getElementById('stateTestOutput');
        if (!preview || !output) return;

        const renderState = (state) => {
            if (state === 'loading') {
                preview.innerHTML = '<div class="d-flex align-items-center gap-2 text-secondary"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span><span>Yukleniyor...</span></div>';
                return;
            }
            if (state === 'error') {
                preview.innerHTML = '<div class="alert alert-danger mb-0" role="alert">Hata: baglanti kurulamadı.</div>';
                return;
            }
            if (state === 'success') {
                preview.innerHTML = '<div class="alert alert-success mb-0" role="alert">Basarili: veri yuklendi.</div>';
                return;
            }

            preview.innerHTML = '<div class="empty py-4"><p class="empty-title mb-1">Kayit yok</p><p class="empty-subtitle text-secondary mb-0">Bu bolumde veri bulunmuyor.</p></div>';
        };

        const writeOutput = (payload) => {
            output.textContent = JSON.stringify(payload, null, 2);
        };

        document.querySelectorAll('[data-kirpi-state-view]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const state = String(btn.getAttribute('data-kirpi-state-view') || 'empty');
                renderState(state);
                writeOutput({action: 'view-change', state, timestamp: new Date().toISOString()});
            });
        });

        document.querySelectorAll('[data-kirpi-state-action]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const action = String(btn.getAttribute('data-kirpi-state-action') || '');
                if (window.kirpiNotify) {
                    window.kirpiNotify.info('State aksiyonu: ' + action, {title: 'State Test'});
                }
                writeOutput({action: 'button', name: action, timestamp: new Date().toISOString()});
            });
        });

        window.kirpiState = {
            render: renderState,
        };
    })();
</script>
