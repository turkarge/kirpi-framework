<div class="row g-3">
    <article class="col-12 col-xl-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Klavye Kisayollari</h3></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter mb-0">
                        <thead><tr><th>Kisayol</th><th>Hedef</th><th>Aciklama</th></tr></thead>
                        <tbody>
                        <tr><td><kbd>Alt+1</kbd></td><td>Dashboard</td><td>Ana demo sayfasina gider.</td></tr>
                        <tr><td><kbd>Alt+2</kbd></td><td>UI Kit</td><td>UI bilesen test sayfasina gider.</td></tr>
                        <tr><td><kbd>Alt+3</kbd></td><td>Notify Test</td><td>Toast/flash testine gider.</td></tr>
                        <tr><td><kbd>Alt+4</kbd></td><td>State Test</td><td>State bilesen testine gider.</td></tr>
                        <tr><td><kbd>Alt+5</kbd></td><td>A11y Test</td><td>Bu sayfaya geri doner.</td></tr>
                        <tr><td><kbd>Ctrl+K</kbd></td><td>Shortcut Help</td><td>Kisayol yardim modalini acar.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </article>

    <article class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Focus ve Aria Testi</h3></div>
            <div class="card-body">
                <p class="text-secondary">TAB ile gezerken focus-visible cizgisi gorunmelidir.</p>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="button">Primary Action</button>
                    <button class="btn btn-1" type="button">Secondary Action</button>
                    <a href="/kirpi/ui-kit" class="btn btn-outline-secondary" aria-label="UI Kit sayfasina git">UI Kit Link</a>
                </div>
                <div class="mt-3">
                    <label for="a11yInput" class="form-label">Input Focus Testi</label>
                    <input id="a11yInput" type="text" class="form-control" placeholder="Klavye ile odaklan">
                </div>
                <pre id="a11yTestOutput" class="mt-3 mb-0 p-3 border bg-transparent" style="overflow:auto; max-height:220px;">Henuz kisayol tetiklenmedi.</pre>
            </div>
        </div>
    </article>
</div>

<script>
    (() => {
        const output = document.getElementById('a11yTestOutput');
        if (!output) return;

        window.addEventListener('kirpi:shortcut', (event) => {
            const detail = event.detail || {};
            output.textContent = JSON.stringify({
                action: 'shortcut-triggered',
                detail,
                timestamp: new Date().toISOString(),
            }, null, 2);
        });
    })();
</script>
