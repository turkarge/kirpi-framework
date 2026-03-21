<div class="ui-card">
    <h3><?= htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8') ?></h3>
    <p><?= htmlspecialchars((string) $body, ENT_QUOTES, 'UTF-8') ?></p>
</div>
<style>
    .ui-card {
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 14px;
        background: linear-gradient(145deg, #fff, #f7fbff);
    }
    .ui-card h3 { margin: 0 0 6px; font-size: 18px; }
    .ui-card p { margin: 0; color: var(--muted); }
</style>
