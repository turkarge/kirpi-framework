<?php
$isPrimary = ($variant ?? 'primary') === 'primary';
$class = $isPrimary ? 'btn btn-primary' : 'btn btn-outline-secondary';
?>
<button class="<?= $class ?>" type="button"><?= htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8') ?></button>
