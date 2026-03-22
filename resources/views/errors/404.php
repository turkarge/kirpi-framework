<?php
declare(strict_types=1);
/** @var int $statusForView */
/** @var string $messageForView */
/** @var string $requestIdForView */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars((string) $statusForView, ENT_QUOTES, 'UTF-8') ?> - Not Found</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f5f7fb; color: #182433; }
    .card { max-width: 560px; margin: 10vh auto; background: #fff; border: 1px solid #dce1e7; border-radius: 8px; padding: 24px; }
    h1 { margin: 0 0 8px; font-size: 40px; }
    p { margin: 0 0 12px; color: #667382; }
    .rid { font-family: monospace; background: #f6f8fb; border: 1px solid #dce1e7; padding: 6px 8px; border-radius: 6px; }
  </style>
</head>
<body>
  <div class="card">
    <h1><?= htmlspecialchars((string) $statusForView, ENT_QUOTES, 'UTF-8') ?></h1>
    <p><?= htmlspecialchars((string) $messageForView, ENT_QUOTES, 'UTF-8') ?></p>
    <p>Request ID: <span class="rid"><?= htmlspecialchars((string) $requestIdForView, ENT_QUOTES, 'UTF-8') ?></span></p>
    <p><a href="/">Back to home</a></p>
  </div>
</body>
</html>

