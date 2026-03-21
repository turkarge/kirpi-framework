<?php
$isPrimary = ($variant ?? 'primary') === 'primary';
$class = $isPrimary ? 'btn btn-primary' : 'btn btn-ghost';
?>
<button class="<?= $class ?>" type="button"><?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?></button>
