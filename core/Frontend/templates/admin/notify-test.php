<article class="panel">
    <h2>Backend Flash -> Toast Testi</h2>
    <p style="color:var(--muted); margin-top:0;">
        Asagidaki linkler backend tarafinda flash mesaji olusturur. Sayfa yuklenirken toast otomatik gorunur.
    </p>
    <div class="inline">
        <a class="btn btn-primary" href="/kirpi/notify-test?kind=success">Success Flash</a>
        <a class="btn btn-ghost" href="/kirpi/notify-test?kind=info">Info Flash</a>
        <a class="btn btn-ghost" href="/kirpi/notify-test?kind=warning">Warning Flash</a>
        <a class="btn btn-ghost" href="/kirpi/notify-test?kind=error">Error Flash</a>
    </div>
    <?php if (($kind ?? '') !== ''): ?>
        <p style="margin-top:12px; color:var(--muted);">
            Son tetiklenen tur: <strong><?= htmlspecialchars((string) $kind, ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
    <?php endif; ?>
</article>
