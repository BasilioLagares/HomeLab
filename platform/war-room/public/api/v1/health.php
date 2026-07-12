<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

rateLimit('health', 120, 60);

jsonResponse([
    'ok' => true,
    'version' => 'v1',
    'server_time' => nowIso(),
]);
