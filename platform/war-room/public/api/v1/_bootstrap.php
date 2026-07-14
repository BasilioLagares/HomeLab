<?php
declare(strict_types=1);

function configValue(string $name, string $fallback): string
{
    $value = getenv($name);
    return is_string($value) && trim($value) !== '' ? $value : $fallback;
}

$warRoomTimezone = configValue('WARROOM_TIMEZONE', 'UTC');
date_default_timezone_set($warRoomTimezone);

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
    return (new DateTimeImmutable('now'))->format(DATE_ATOM);
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
            'url' => configValue('WARROOM_SERVICE_WARROOM_URL', 'https://warroom.example.invalid'),
            'probe_url' => configValue('WARROOM_SERVICE_WARROOM_PROBE_URL', 'https://warroom.example.invalid'),
            'sensitive' => false,
            'default_state' => 'up',
        ],
        [
            'id' => 'homepage',
            'name' => 'Homepage',
            'url' => configValue('WARROOM_SERVICE_HOMEPAGE_URL', 'https://homepage.example.invalid'),
            'probe_url' => configValue('WARROOM_SERVICE_HOMEPAGE_PROBE_URL', 'https://homepage.example.invalid'),
            'sensitive' => false,
            'default_state' => 'up',
        ],
        [
            'id' => 'kuma',
            'name' => 'Uptime Kuma',
            'url' => configValue('WARROOM_SERVICE_KUMA_URL', 'https://status.example.invalid'),
            'probe_url' => configValue('WARROOM_SERVICE_KUMA_PROBE_URL', 'https://status.example.invalid'),
            'sensitive' => false,
            'default_state' => 'up',
        ],
        [
            'id' => 'php',
            'name' => 'PHP Demo',
            'url' => configValue('WARROOM_SERVICE_PHP_URL', 'https://demo.example.invalid'),
            'probe_url' => configValue('WARROOM_SERVICE_PHP_PROBE_URL', 'https://demo.example.invalid'),
            'sensitive' => false,
            'default_state' => 'local',
        ],
        [
            'id' => configValue('WARROOM_SERVICE_CUSTOM_ID', 'custom'),
            'name' => configValue('WARROOM_SERVICE_CUSTOM_NAME', 'Custom Service'),
            'url' => configValue('WARROOM_SERVICE_CUSTOM_URL', 'https://custom.example.invalid'),
            'probe_url' => configValue('WARROOM_SERVICE_CUSTOM_PROBE_URL', 'https://custom.example.invalid'),
            'sensitive' => false,
            'default_state' => 'local',
        ],
        [
            'id' => 'adminer',
            'name' => 'Adminer',
            'url' => configValue('WARROOM_SERVICE_ADMINER_URL', 'https://database.example.invalid'),
            'probe_url' => configValue('WARROOM_SERVICE_ADMINER_PROBE_URL', 'https://database.example.invalid'),
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
