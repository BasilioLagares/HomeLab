<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Madrid');

function jsonResponse(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function nowIso(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('Europe/Madrid')))->format(DATE_ATOM);
}

function clientIp(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return is_string($ip) ? $ip : 'unknown';
}

function rateLimit(string $scope, int $maxRequests, int $windowSeconds): void
{
    $ip = clientIp();
    $key = hash('sha256', $scope . '|' . $ip);
    $file = sys_get_temp_dir() . '/warroom_api_rate_' . $key . '.json';

    $now = time();
    $data = [
        'window_start' => $now,
        'count' => 0,
    ];

    if (is_file($file)) {
        $raw = @file_get_contents($file);
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded['window_start'], $decoded['count'])) {
                $data['window_start'] = (int) $decoded['window_start'];
                $data['count'] = (int) $decoded['count'];
            }
        }
    }

    if (($now - $data['window_start']) >= $windowSeconds) {
        $data['window_start'] = $now;
        $data['count'] = 0;
    }

    $data['count']++;

    @file_put_contents($file, json_encode($data), LOCK_EX);

    if ($data['count'] > $maxRequests) {
        jsonResponse([
            'ok' => false,
            'error' => 'rate_limited',
            'message' => 'Demasiadas peticiones. Reintenta en unos segundos.',
            'server_time' => nowIso(),
        ], 429);
    }
}

function serviceDefinitions(): array
{
    return [
        [
            'id' => 'warroom',
            'name' => 'War Room',
            'url' => 'https://warroom.homelab.home.arpa',
            'probe_url' => 'http://127.0.0.1/',
            'sensitive' => false,
            'default_state' => 'up',
        ],
        [
            'id' => 'homepage',
            'name' => 'Homepage',
            'url' => 'https://homepage.homelab.home.arpa',
            'probe_url' => 'http://homelab_homepage:3000',
            'sensitive' => false,
            'default_state' => 'up',
        ],
        [
            'id' => 'kuma',
            'name' => 'Uptime Kuma',
            'url' => 'https://kuma.homelab.home.arpa',
            'probe_url' => 'http://homelab_uptime_kuma:3001',
            'sensitive' => false,
            'default_state' => 'up',
        ],
        [
            'id' => 'php',
            'name' => 'PHP Demo',
            'url' => 'https://php.homelab.home.arpa',
            'probe_url' => 'http://homelab_php_demo:80',
            'sensitive' => false,
            'default_state' => 'local',
        ],
        [
            'id' => 'mariano',
            'name' => 'Mariano Limón',
            'url' => 'https://mariano.homelab.home.arpa',
            'probe_url' => 'http://homelab_mariano_limon_web:80',
            'sensitive' => false,
            'default_state' => 'local',
        ],
        [
            'id' => 'adminer',
            'name' => 'Adminer',
            'url' => 'https://adminer.homelab.home.arpa',
            'probe_url' => 'http://homelab_adminer:8080',
            'sensitive' => true,
            'default_state' => 'local',
        ],
    ];
}

function classifyServiceState(?int $httpCode, string $defaultState): string
{
    if ($httpCode === null) {
        return 'down';
    }

    if (($httpCode >= 200 && $httpCode < 400) || $httpCode === 401 || $httpCode === 403) {
        return $defaultState;
    }

    if ($httpCode >= 400 && $httpCode < 500) {
        return 'degraded';
    }

    return 'down';
}

function checkService(string $url): array
{
    if (!function_exists('curl_init')) {
        return [
            'http_code' => null,
            'latency_ms' => null,
            'error' => 'curl_extension_missing',
        ];
    }

    $start = microtime(true);

    $ch = curl_init($url);
    if ($ch === false) {
        return [
            'http_code' => null,
            'latency_ms' => null,
            'error' => 'curl_init_failed',
        ];
    }

    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT_MS => 1500,
        CURLOPT_TIMEOUT_MS => 2500,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $errno = curl_errno($ch);
    $latency = (int) round((microtime(true) - $start) * 1000);
    curl_close($ch);

    if ($errno !== 0 || $httpCode <= 0) {
        return [
            'http_code' => null,
            'latency_ms' => $latency,
            'error' => 'request_failed',
        ];
    }

    return [
        'http_code' => (int) $httpCode,
        'latency_ms' => $latency,
        'error' => null,
    ];
}
