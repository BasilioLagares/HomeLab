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
                    ['label' => 'Docs antiguos sensibles', 'state' => 'pending_sanitization'],
                    ['label' => 'Checklist HomeLab', 'state' => 'updated'],
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
                'id' => 'recommended',
                'title' => 'Pendientes recomendados',
                'summary' => 'Siguientes pasos antes de habilitar operaciones controladas.',
                'items' => [
                    ['label' => 'Revisar scripts backup/update', 'state' => 'pending'],
                    ['label' => 'Sanear docs antiguos', 'state' => 'pending'],
                    ['label' => 'Probar restauración de backups', 'state' => 'pending'],
                    ['label' => 'Diseñar v0.9 con lista blanca', 'state' => 'pending_design'],
                    ['label' => 'Separar zona privada y demos públicas', 'state' => 'pending'],
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
