<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

rateLimit('services', 90, 60);

$items = [];
foreach (serviceDefinitions() as $service) {
    $probe = checkService($service['probe_url']);
    $state = classifyServiceState($probe['http_code'], $service['default_state']);

    $items[] = [
        'id' => $service['id'],
        'name' => $service['name'],
        'url' => $service['url'],
        'state' => $state,
        'http_code' => $probe['http_code'],
        'latency_ms' => $probe['latency_ms'],
        'sensitive' => (bool) $service['sensitive'],
    ];
}

jsonResponse([
    'last_update' => nowIso(),
    'items' => $items,
]);
