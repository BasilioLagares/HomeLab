<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

rateLimit('status', 90, 60);

$services = [];
$down = 0;
$degraded = 0;

foreach (serviceDefinitions() as $service) {
    $probe = checkService($service['probe_url']);
    $state = classifyServiceState($probe['http_code'], $service['default_state']);

    if ($state === 'down') {
        $down++;
    } elseif ($state === 'degraded') {
        $degraded++;
    }

    $services[] = [
        'id' => $service['id'],
        'state' => $state,
    ];
}

$overall = 'operational';
if ($down > 0) {
    $overall = 'degraded';
} elseif ($degraded > 0) {
    $overall = 'degraded';
}

jsonResponse([
    'state' => $overall,
    'mode' => 'dynamic',
    'telemetry' => [
        'services' => 'real',
        'resources' => 'pending',
    ],
    'summary' => [
        'services_total' => count($services),
        'services_down' => $down,
        'services_degraded' => $degraded,
        'containers' => null,
        'hosts' => null,
    ],
    'last_update' => nowIso(),
]);
