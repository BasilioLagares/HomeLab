<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

rateLimit('tasks', 90, 60);

if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    jsonResponse([
        'ok' => false,
        'error' => 'method_not_allowed',
    ], 405);
}

function taskSourcePath(): string
{
    $stateMountPath = '/var/warroom-state/homelab_tasks.json';
    if (is_readable($stateMountPath)) {
        return $stateMountPath;
    }

    $repoPath = dirname(__DIR__, 5) . '/state/homelab_tasks.example.json';
    if (is_readable($repoPath)) {
        return $repoPath;
    }

    return '/var/warroom-runtime/homelab_tasks.json';
}

function publicTaskError(string $error): never
{
    jsonResponse([
        'ok' => false,
        'error' => $error,
    ]);
}

function sanitizeTaskBlock(array $block, array $allowedStatuses, array $allowedPriorities): array
{
    if (!isset($block['id'], $block['title'], $block['items']) || !is_array($block['items'])) {
        publicTaskError('invalid_tasks_schema');
    }

    $items = [];
    foreach ($block['items'] as $item) {
        if (!is_array($item)) {
            publicTaskError('invalid_task_item');
        }

        foreach (['id', 'title', 'status', 'priority'] as $field) {
            if (!isset($item[$field]) || !is_string($item[$field]) || trim($item[$field]) === '') {
                publicTaskError('invalid_task_item');
            }
        }

        if (!in_array($item['status'], $allowedStatuses, true)) {
            publicTaskError('invalid_task_status');
        }

        if (!in_array($item['priority'], $allowedPriorities, true)) {
            publicTaskError('invalid_task_priority');
        }

        $items[] = [
            'id' => $item['id'],
            'title' => $item['title'],
            'status' => $item['status'],
            'priority' => $item['priority'],
        ];
    }

    return [
        'id' => (string) $block['id'],
        'title' => (string) $block['title'],
        'items' => $items,
    ];
}

$path = taskSourcePath();

if (!is_readable($path)) {
    publicTaskError('tasks_file_not_found');
}

$raw = file_get_contents($path);
if (!is_string($raw) || trim($raw) === '') {
    publicTaskError('tasks_file_empty');
}

$decoded = json_decode($raw, true);
if (!is_array($decoded)) {
    publicTaskError('tasks_file_invalid_json');
}

if (!isset($decoded['blocks']) || !is_array($decoded['blocks'])) {
    publicTaskError('invalid_tasks_schema');
}

if (($decoded['source'] ?? null) !== 'ROADMAP.md'
    || !array_key_exists('canonical', $decoded)
    || $decoded['canonical'] !== false
) {
    publicTaskError('invalid_tasks_projection');
}

if (!isset($decoded['version']) || !is_string($decoded['version']) || trim($decoded['version']) === '') {
    publicTaskError('invalid_tasks_schema');
}

$allowedBlockStatuses = [
    'active' => ['pending', 'doing', 'blocked'],
    'future' => ['later'],
];
$allowedPriorities = ['high', 'medium', 'low'];
$blocks = [];
$seenBlocks = [];
$seenItems = [];

foreach ($decoded['blocks'] as $block) {
    if (!is_array($block)) {
        publicTaskError('invalid_tasks_schema');
    }

    $blockId = $block['id'] ?? null;
    if (!is_string($blockId) || !array_key_exists($blockId, $allowedBlockStatuses) || isset($seenBlocks[$blockId])) {
        publicTaskError('invalid_tasks_projection');
    }

    $sanitizedBlock = sanitizeTaskBlock($block, $allowedBlockStatuses[$blockId], $allowedPriorities);
    foreach ($sanitizedBlock['items'] as $item) {
        if (isset($seenItems[$item['id']])) {
            publicTaskError('invalid_tasks_projection');
        }
        $seenItems[$item['id']] = true;
    }

    $seenBlocks[$blockId] = true;
    $blocks[] = $sanitizedBlock;
}

if (array_diff(array_keys($allowedBlockStatuses), array_keys($seenBlocks)) !== []) {
    publicTaskError('invalid_tasks_projection');
}

jsonResponse([
    'ok' => true,
    'data' => [
        'version' => $decoded['version'],
        'source' => 'ROADMAP.md',
        'canonical' => false,
        'last_update' => nowIso(),
        'blocks' => $blocks,
    ],
]);
