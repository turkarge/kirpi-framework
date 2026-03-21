<div class="row g-3">
    <article class="col-12 col-xl-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">PWA Durum Kontrolu</h3></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <tbody>
                        <tr>
                            <td>Manifest Baglantisi</td>
                            <td><code>/manifest.webmanifest</code></td>
                            <td><span id="pwaManifestStatus" class="badge bg-secondary-lt">Kontrol ediliyor</span></td>
                        </tr>
                        <tr>
                            <td>Service Worker</td>
                            <td><code>/sw.js</code></td>
                            <td><span id="pwaSwStatus" class="badge bg-secondary-lt">Kontrol ediliyor</span></td>
                        </tr>
                        <tr>
                            <td>Display Mode</td>
                            <td>Browser / Standalone</td>
                            <td><span id="pwaDisplayMode" class="badge bg-secondary-lt">Tespit ediliyor</span></td>
                        </tr>
                        <tr>
                            <td>Connection</td>
                            <td><code>navigator.onLine</code></td>
                            <td><span id="pwaOnlineStatus" class="badge bg-secondary-lt">Tespit ediliyor</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <button type="button" id="pwaInstallBtn" class="btn btn-primary" data-kirpi-pwa-install disabled>
                        Install App
                    </button>
                    <a href="/offline.html" class="btn btn-1" target="_blank" rel="noopener">Offline Fallback Ac</a>
                    <button type="button" id="pwaRefreshBtn" class="btn btn-1">Durum Yenile</button>
                </div>
            </div>
        </div>
    </article>

    <article class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Beklenen Davranis</h3></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 text-secondary">
                    <li class="mb-2">1. Manifest ve SW dosyalari 200 donmeli.</li>
                    <li class="mb-2">2. Service worker kaydi tamamlanmali.</li>
                    <li class="mb-2">3. Offline durumda /offline.html fallback sayfasi gorunmeli.</li>
                    <li class="mb-2">4. Uygun tarayicida install butonu aktif hale gelmeli.</li>
                </ul>
            </div>
        </div>
    </article>
</div>

<script>
    (() => {
        const manifestBadge = document.getElementById('pwaManifestStatus');
        const swBadge = document.getElementById('pwaSwStatus');
        const modeBadge = document.getElementById('pwaDisplayMode');
        const onlineBadge = document.getElementById('pwaOnlineStatus');
        const installBtn = document.getElementById('pwaInstallBtn');

        if (!manifestBadge || !swBadge || !modeBadge || !onlineBadge || !installBtn) {
            return;
        }

        const setBadge = (node, text, cls) => {
            node.className = 'badge ' + cls;
            node.textContent = text;
        };

        const refresh = async () => {
            try {
                const manifestResponse = await fetch('/manifest.webmanifest', {method: 'GET', cache: 'no-store'});
                setBadge(manifestBadge, manifestResponse.ok ? 'up' : 'down', manifestResponse.ok ? 'bg-green-lt' : 'bg-red-lt');
            } catch (_e) {
                setBadge(manifestBadge, 'down', 'bg-red-lt');
            }

            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || (window.navigator.standalone === true);
            setBadge(modeBadge, isStandalone ? 'standalone' : 'browser', isStandalone ? 'bg-blue-lt' : 'bg-secondary-lt');

            const online = navigator.onLine;
            setBadge(onlineBadge, online ? 'online' : 'offline', online ? 'bg-green-lt' : 'bg-yellow-lt');

            const hasSw = 'serviceWorker' in navigator;
            if (!hasSw) {
                setBadge(swBadge, 'unsupported', 'bg-yellow-lt');
                return;
            }

            try {
                const registration = await navigator.serviceWorker.getRegistration('/');
                setBadge(swBadge, registration ? 'registered' : 'pending', registration ? 'bg-green-lt' : 'bg-secondary-lt');
            } catch (_e) {
                setBadge(swBadge, 'down', 'bg-red-lt');
            }
        };

        document.getElementById('pwaRefreshBtn')?.addEventListener('click', () => {
            refresh().catch(() => {});
        });

        window.addEventListener('kirpi:pwa-install-ready', () => {
            installBtn.removeAttribute('disabled');
        });

        window.addEventListener('online', () => refresh().catch(() => {}));
        window.addEventListener('offline', () => refresh().catch(() => {}));

        refresh().catch(() => {});
    })();
</script>
