<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

rateLimit('containers', 60, 60);

$path = '/var/warroom-runtime/docker-status.json';
$staleAfterSeconds = 45;

if (!is_readable($path)) {
    jsonResponse([
        'last_update' => nowIso(),
        'source' => 'runtime_mount',
        'state' => 'unavailable',
        'freshness' => 'unknown',
        'age_seconds' => null,
        'stale_after_seconds' => $staleAfterSeconds,
        'containers_total' => null,
        'containers_running' => null,
        'containers_exited' => null,
        'items' => [],
        'error' => 'docker_status_file_not_readable',
    ], 200);
}

$raw = file_get_contents($path);

if (!is_string($raw) || trim($raw) === '') {
    jsonResponse([
        'last_update' => nowIso(),
        'source' => 'runtime_mount',
        'state' => 'unavailable',
        'freshness' => 'unknown',
        'age_seconds' => null,
        'stale_after_seconds' => $staleAfterSeconds,
        'containers_total' => null,
        'containers_running' => null,
        'containers_exited' => null,
        'items' => [],
        'error' => 'docker_status_file_empty',
    ], 200);
}

$data = json_decode($raw, true);

if (!is_array($data)) {
    jsonResponse([
        'last_update' => nowIso(),
        'source' => 'runtime_mount',
        'state' => 'unavailable',
        'freshness' => 'unknown',
        'age_seconds' => null,
        'stale_after_seconds' => $staleAfterSeconds,
        'containers_total' => null,
        'containers_running' => null,
        'containers_exited' => null,
        'items' => [],
        'error' => 'docker_status_file_invalid_json',
    ], 200);
}

$allowedItemKeys = ['id', 'name', 'image', 'state', 'status', 'ports'];
$items = [];

if (is_array($data['items'] ?? null)) {
    foreach ($data['items'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $filteredItem = [];

        foreach ($allowedItemKeys as $key) {
            if (array_key_exists($key, $item)) {
                $filteredItem[$key] = $item[$key];
            }
        }

        $items[] = $filteredItem;
    }
}

$lastUpdate = $data['last_update'] ?? null;
$state = $data['state'] ?? 'unknown';
$freshness = 'unknown';
$ageSeconds = null;

if (is_string($lastUpdate) && trim($lastUpdate) !== '') {
    try {
        $updatedAt = new DateTimeImmutable($lastUpdate);
        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Madrid'));
        $ageSeconds = max(0, $now->getTimestamp() - $updatedAt->getTimestamp());

        if ($ageSeconds <= $staleAfterSeconds) {
            $freshness = 'fresh';
        } else {
            $freshness = 'stale';
            $state = 'stale';
        }
    } catch (Exception $e) {
        $state = 'unknown';
    }
} else {
    $state = 'unknown';
}

jsonResponse([
    'last_update' => $lastUpdate ?? nowIso(),
    'source' => $data['source'] ?? 'host_exporter',
    'state' => $state,
    'freshness' => $freshness,
    'age_seconds' => $ageSeconds,
    'stale_after_seconds' => $staleAfterSeconds,
    'containers_total' => $data['containers_total'] ?? null,
    'containers_running' => $data['containers_running'] ?? null,
    'containers_exited' => $data['containers_exited'] ?? null,
    'items' => $items,
]);
