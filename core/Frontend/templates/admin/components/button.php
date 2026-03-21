<?php
$isPrimary = ($variant ?? 'primary') === 'primary';
$class = $isPrimary ? 'btn btn-primary' : 'btn btn-ghost';
?>
<button class="<?= $class ?>" type="button"><?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?></button>
<style>
    .btn {
        border-radius: 10px;
        border: 1px solid transparent;
        padding: 9px 14px;
        font-weight: 700;
        cursor: pointer;
    }
    .btn-primary {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
    }
    .btn-primary:hover { background: var(--brand-deep); border-color: var(--brand-deep); }
    .btn-ghost {
        background: #fff;
        border-color: var(--line);
        color: var(--ink);
    }
    .btn-ghost:hover { border-color: var(--brand); color: var(--brand); }
</style>
