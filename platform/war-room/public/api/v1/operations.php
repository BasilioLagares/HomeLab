<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

rateLimit('operations', 60, 60);

if (!in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD'], true)) {
    jsonResponse([
        'ok' => false,
        'error' => 'method_not_allowed',
    ], 405);
}

if (count($_GET) > 0) {
    jsonResponse([
        'ok' => false,
        'error' => 'query_not_supported',
    ], 400);
}

jsonResponse([
    'ok' => true,
    'data' => [
        'operations_mode' => 'read_only',
        'status' => 'available',
        'last_update' => nowIso(),
        'sections' => [
            [
                'id' => 'repository',
                'title' => 'Repositorio/versionado',
                'summary' => 'Base Git preparada para versionar solo contenido saneado.',
                'items' => [
                    ['label' => 'Git seguro inicial', 'state' => 'closed'],
                    ['label' => '.gitignore seguro', 'state' => 'applied'],
                    ['label' => 'docker-compose real', 'state' => 'local_no_versioned'],
                    ['label' => 'docker-compose example', 'state' => 'updated'],
                ],
            ],
            [
                'id' => 'documentation',
                'title' => 'Documentación/manuales',
                'summary' => 'Manuales saneados disponibles como base documental read-only.',
                'items' => [
                    ['label' => 'docs/manuals', 'state' => 'mounted_read_only'],
                    ['label' => 'Lector online', 'state' => 'available'],
                    ['label' => 'Docs históricos internos', 'state' => 'local_no_versioned'],
                    ['label' => 'Proyección operativa del roadmap', 'state' => 'available'],
                ],
            ],
            [
                'id' => 'security',
                'title' => 'Seguridad',
                'summary' => 'War Room mantiene separación estricta entre lectura y operación.',
                'items' => [
                    ['label' => 'War Room', 'state' => 'read_only'],
                    ['label' => 'Comandos shell desde API', 'state' => 'disabled'],
                    ['label' => 'docker.sock', 'state' => 'not_mounted'],
                    ['label' => 'Operaciones administrativas', 'state' => 'disabled'],
                    ['label' => 'Secretos', 'state' => 'outside_git'],
                ],
            ],
            [
                'id' => 'planning',
                'title' => 'Planificación',
                'summary' => 'ROADMAP.md es la única fuente canónica del roadmap.',
                'items' => [
                    ['label' => 'ROADMAP.md canónico', 'state' => 'updated'],
                    ['label' => 'JSON como proyección no canónica', 'state' => 'available'],
                    ['label' => 'Backlog propio de War Room', 'state' => 'disabled'],
                    ['label' => 'Ejecución desde War Room', 'state' => 'disabled'],
                ],
            ],
        ],
        'safety' => [
            'read_only' => true,
            'shell_commands' => false,
            'docker_socket' => false,
            'admin_actions' => false,
            'free_form_commands' => false,
        ],
    ],
]);
