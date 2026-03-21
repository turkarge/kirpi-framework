<?php
$variant = (string) ($variant ?? 'primary');
$class = match ($variant) {
    'secondary' => 'btn btn-secondary',
    'ghost' => 'btn btn-outline-secondary',
    default => 'btn btn-primary',
};
?>
<button class="<?= $class ?>" type="button"><?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?></button>
