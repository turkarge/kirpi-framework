<article class="card">
    <div class="card-header"><h3 class="card-title">Backend Flash -> Toast Testi</h3></div>
    <div class="card-body">
        <p class="text-secondary mt-0">
            Asagidaki linkler backend tarafinda flash mesaji olusturur. Sayfa yuklenirken toast otomatik gorunur.
        </p>
        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-primary" href="/kirpi/notify-test?kind=success">Success Flash</a>
            <a class="btn btn-outline-secondary" href="/kirpi/notify-test?kind=info">Info Flash</a>
            <a class="btn btn-outline-secondary" href="/kirpi/notify-test?kind=warning">Warning Flash</a>
            <a class="btn btn-outline-secondary" href="/kirpi/notify-test?kind=error">Error Flash</a>
        </div>
        <?php if (($kind ?? '') !== ''): ?>
            <p class="mt-3 text-secondary mb-0">
                Son tetiklenen tur: <strong><?= htmlspecialchars((string) $kind, ENT_QUOTES, 'UTF-8') ?></strong>
            </p>
        <?php endif; ?>
    </div>
</article>
