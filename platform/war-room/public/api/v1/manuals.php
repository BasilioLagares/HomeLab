<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

rateLimit('manuals', 90, 60);

if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    jsonResponse([
        'ok' => false,
        'error' => 'method_not_allowed',
    ], 405);
}

/**
 * Safe allowlist for public documentation metadata.
 */
function allowedManuals(): array
{
    return [
        'README' => [
            'title' => 'Manuales del HomeLab',
            'summary' => 'Índice general de la documentación operativa saneada.',
            'file' => 'README.md',
        ],
        'war-room' => [
            'title' => 'War Room',
            'summary' => 'Panel read-only, arquitectura, validación segura y roadmap.',
            'file' => 'war-room.md',
        ],
        'git-seguro' => [
            'title' => 'Git seguro',
            'summary' => 'Política de versionado, add selectivo y revisión antes de publicar.',
            'file' => 'git-seguro.md',
        ],
        'backups' => [
            'title' => 'Backups',
            'summary' => 'Política general de copias, restauración conceptual e integridad.',
            'file' => 'backups.md',
        ],
        'caddy-dns' => [
            'title' => 'Caddy y DNS',
            'summary' => 'Reverse proxy interno, DNS y diagnóstico seguro de acceso.',
            'file' => 'caddy-dns.md',
        ],
        'wireguard' => [
            'title' => 'WireGuard',
            'summary' => 'VPN privada, administración remota y advertencias de exposición.',
            'file' => 'wireguard.md',
        ],
        'backups-restauracion' => [
            'title' => 'Backups y restauración',
            'summary' => 'Política de copias, restauración conceptual e integridad.',
            'file' => 'backups-restauracion.md',
        ],
        'recuperacion-war-room' => [
            'title' => 'Recuperación de War Room',
            'summary' => 'Diagnóstico seguro si War Room o sus APIs no cargan.',
            'file' => 'recuperacion-war-room.md',
        ],
    ];
}

function manualsBaseDir(): ?string
{
    $candidates = [
        '/var/www/manuals',
        dirname(__DIR__, 5) . '/docs/manuals',
    ];

    foreach ($candidates as $candidate) {
        if (is_dir($candidate) && is_readable($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function isManualReadable(?string $baseDir, string $file): bool
{
    if ($baseDir === null) {
        return false;
    }

    $path = $baseDir . DIRECTORY_SEPARATOR . $file;
    $realBase = realpath($baseDir);
    $realFile = realpath($path);

    if ($realBase === false || $realFile === false) {
        return false;
    }

    return str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR)
        && is_file($realFile)
        && is_readable($realFile);
}

function readManualContent(?string $baseDir, array $manual): ?string
{
    if ($baseDir === null) {
        return null;
    }

    $path = $baseDir . DIRECTORY_SEPARATOR . $manual['file'];
    $realBase = realpath($baseDir);
    $realFile = realpath($path);

    if ($realBase === false || $realFile === false) {
        return null;
    }

    if (!str_starts_with($realFile, $realBase . DIRECTORY_SEPARATOR)) {
        return null;
    }

    if (!is_file($realFile) || !is_readable($realFile)) {
        return null;
    }

    $content = file_get_contents($realFile);
    if (!is_string($content)) {
        return null;
    }

    return $content;
}

$slug = $_GET['slug'] ?? null;
$manuals = allowedManuals();

if ($slug !== null && (!is_string($slug) || !array_key_exists($slug, $manuals))) {
    jsonResponse([
        'ok' => false,
        'error' => 'manual_not_allowed',
    ], 404);
}

$baseDir = manualsBaseDir();
$items = [];
$selected = null;
$selectedContent = null;

foreach ($manuals as $id => $manual) {
    $available = isManualReadable($baseDir, $manual['file']);
    $items[] = [
        'id' => $id,
        'title' => $manual['title'],
        'summary' => $manual['summary'],
        'read_only' => true,
        'available' => $available,
    ];

    if ($slug !== null && $slug === $id) {
        $selected = [
            'id' => $id,
            'title' => $manual['title'],
            'summary' => $manual['summary'],
            'read_only' => true,
            'available' => $available,
        ];
        $selectedContent = readManualContent($baseDir, $manual);
    }
}

$availableCount = count(array_filter($items, static fn (array $item): bool => $item['available']));
$state = 'unavailable';
$reason = null;

if ($availableCount === count($items) && count($items) > 0) {
    $state = 'available';
} elseif ($availableCount > 0) {
    $state = 'partial';
}

if ($baseDir === null) {
    $reason = 'manuals_not_mounted';
} elseif ($state !== 'available') {
    $reason = 'manuals_incomplete';
}

jsonResponse([
    'ok' => true,
    'data' => [
        'state' => $state,
        'read_only' => true,
        'manuals_available' => $state === 'available',
        'reason' => $reason,
        'items' => $items,
        'selected' => $selected,
        'manual' => $selected === null ? null : [
            'id' => $selected['id'],
            'title' => $selected['title'],
            'summary' => $selected['summary'],
            'read_only' => true,
            'available' => $selectedContent !== null,
            'content_type' => 'text/markdown',
            'content' => $selectedContent,
            'error' => $selectedContent === null ? ($baseDir === null ? 'manuals_not_mounted' : 'manual_not_readable') : null,
        ],
        'last_update' => nowIso(),
    ],
]);
