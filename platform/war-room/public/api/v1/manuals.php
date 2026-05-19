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
 *
 * The Docker deployment does not mount docs/manuals by default yet. When that
 * mount exists, this endpoint can report availability without exposing host
 * paths. Keep content serving behind this allowlist if it is added later.
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
    ];
}

function manualsBaseDir(): ?string
{
    $candidates = [
        '/var/warroom-manuals',
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

foreach ($manuals as $id => $manual) {
    if ($slug !== null && $slug !== $id) {
        continue;
    }

    $items[] = [
        'id' => $id,
        'title' => $manual['title'],
        'summary' => $manual['summary'],
        'read_only' => true,
        'available' => isManualReadable($baseDir, $manual['file']),
    ];
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
        'last_update' => nowIso(),
    ],
]);
