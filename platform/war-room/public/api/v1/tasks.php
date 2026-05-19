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
    $hostPath = '/home/basilio/HomeLab/state/homelab_tasks.json';
    if (is_readable($hostPath)) {
        return $hostPath;
    }

    $repoPath = dirname(__DIR__, 5) . '/state/homelab_tasks.json';
    if (is_readable($repoPath)) {
        return $repoPath;
    }

    $stateMountPath = '/var/warroom-state/homelab_tasks.json';
    if (is_readable($stateMountPath)) {
        return $stateMountPath;
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

$allowedStatuses = ['pending', 'doing', 'done', 'blocked', 'later'];
$allowedPriorities = ['high', 'medium', 'low'];
$blocks = [];

foreach ($decoded['blocks'] as $block) {
    if (!is_array($block)) {
        publicTaskError('invalid_tasks_schema');
    }

    $blocks[] = sanitizeTaskBlock($block, $allowedStatuses, $allowedPriorities);
}

jsonResponse([
    'ok' => true,
    'data' => [
        'version' => is_string($decoded['version'] ?? null) ? $decoded['version'] : '0.5',
        'last_update' => nowIso(),
        'blocks' => $blocks,
    ],
]);
