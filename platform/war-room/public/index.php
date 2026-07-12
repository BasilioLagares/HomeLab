<?php
declare(strict_types=1);

function configValue(string $name, string $fallback): string
{
    $value = getenv($name);
    return is_string($value) && trim($value) !== '' ? $value : $fallback;
}

$warRoomTimezone = configValue('WARROOM_TIMEZONE', 'UTC');
date_default_timezone_set($warRoomTimezone);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$now = new DateTimeImmutable('now');

$overview = [
    'estado' => 'PANEL OPERATIVO LOCAL',
    'modo' => 'Modo dinámico (API)',
    'version' => 'WAR ROOM V0.4',
    'zona' => $warRoomTimezone,
];

$cards = [
    [
        'title' => 'Infraestructura',
        'status' => 'UP',
        'summary' => 'Mapa inicial del HomeLab, contenedores y red proxy.',
        'metric' => 'Datos reales progresivos',
    ],
    [
        'title' => 'Monitorización',
        'status' => 'LOCAL',
        'summary' => 'Panel preparado para enlazar métricas y salud de servicios.',
        'metric' => 'Telemetría pendiente',
    ],
    [
        'title' => 'Proyectos',
        'status' => 'PENDIENTE',
        'summary' => 'Inventario visual para demos, laboratorios y despliegues.',
        'metric' => 'v0.4',
    ],
    [
        'title' => 'Seguridad',
        'status' => 'RIESGO',
        'summary' => 'Vista futura para alertas, auditorias y superficie expuesta.',
        'metric' => 'Manual',
    ],
];

$warRoomUrl = configValue('WARROOM_SERVICE_WARROOM_URL', 'https://warroom.example.invalid');
$homepageUrl = configValue('WARROOM_SERVICE_HOMEPAGE_URL', 'https://homepage.example.invalid');
$kumaUrl = configValue('WARROOM_SERVICE_KUMA_URL', 'https://status.example.invalid');
$adminerUrl = configValue('WARROOM_SERVICE_ADMINER_URL', 'https://database.example.invalid');
$phpUrl = configValue('WARROOM_SERVICE_PHP_URL', 'https://demo.example.invalid');
$customUrl = configValue('WARROOM_SERVICE_CUSTOM_URL', 'https://custom.example.invalid');

$criticalServices = [
    ['name' => 'War Room', 'state' => 'UP', 'note' => $warRoomUrl, 'url' => $warRoomUrl],
    ['name' => 'Homepage', 'state' => 'UP', 'note' => $homepageUrl, 'url' => $homepageUrl],
    ['name' => 'Uptime Kuma', 'state' => 'UP', 'note' => $kumaUrl, 'url' => $kumaUrl],
    ['name' => 'Adminer', 'state' => 'LOCAL', 'note' => $adminerUrl . ' (protegido con Basic Auth)', 'url' => $adminerUrl],
    ['name' => 'PHP Demo', 'state' => 'LOCAL', 'note' => $phpUrl, 'url' => $phpUrl],
    ['name' => configValue('WARROOM_SERVICE_CUSTOM_NAME', 'Custom Service'), 'state' => 'LOCAL', 'note' => $customUrl, 'url' => $customUrl],
    ['name' => 'Caddy / Proxy', 'state' => 'UP', 'note' => 'HTTPS interno activo (80/443)', 'url' => null],
    ['name' => 'MariaDB', 'state' => 'LOCAL', 'note' => 'Puerto 3306 (sin URL directa)', 'url' => null],
];

$statusClasses = [
    'UP' => 'badge-up',
    'LOCAL' => 'badge-local',
    'PENDIENTE' => 'badge-pending',
    'RIESGO' => 'badge-risk',
];

$navItems = [
    'Dashboard',
    'Servicios',
    'Infraestructura',
    'Red',
    'Almacenamiento',
    'Monitoreo',
    'Manuales',
    'Operaciones',
    'Automatización',
    'Backups',
    'Configuración',
];

$systemStats = [
    ['label' => 'Ubicación', 'value' => 'Cuartel General', 'detail' => 'War Room'],
    ['label' => 'Modo', 'value' => $overview['modo'], 'detail' => $overview['zona']],
    ['label' => 'Versión', 'value' => $overview['version'], 'detail' => 'MVP visual'],
    ['label' => 'Estado', 'value' => $overview['estado'], 'detail' => 'Telemetría pendiente'],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($overview['version']); ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <main class="warroom">
        <aside class="left-rail" aria-label="Navegación principal">
            <div class="brand">
                <span class="crest">W</span>
                <div>
                    <strong>WAR ROOM</strong>
                    <span>Plataforma personal</span>
                </div>
            </div>

            <nav class="nav-list" aria-label="Secciones">
                <?php foreach ($navItems as $index => $item): ?>
                    <?php
                    $href = match ($item) {
                        'Manuales' => '#manuales',
                        'Operaciones' => '#operaciones',
                        default => $index === 0 ? '#dashboard' : '#panel-principal',
                    };
                    ?>
                    <a class="<?= $index === 0 ? 'is-active' : ''; ?>" href="<?= e($href); ?>" data-nav-item="<?= e(strtolower($item)); ?>">
                        <span class="nav-icon" aria-hidden="true"></span>
                        <?= e($item); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="profile-card">
                <span class="crest crest-small">W</span>
                <div>
                    <strong>Lord Comandante</strong>
                    <span>Nivel de acceso: Supremo</span>
                </div>
            </div>
        </aside>

        <section class="main-deck" aria-labelledby="page-title">
            <header class="top-bar">
                <span class="top-line" aria-hidden="true"></span>
                <div class="top-title">
                    <span class="crest crest-tiny">W</span>
                    <strong><?= e($overview['version']); ?></strong>
                </div>
                <div class="top-clock">
                    <strong data-clock-time><?= e($now->format('H:i:s')); ?></strong>
                    <span data-clock-date><?= e($now->format('d M Y')); ?></span>
                </div>
            </header>

            <section class="stage">
                <aside class="stage-monitor">
                    <h2>Estado global</h2>
                    <div class="globe" aria-hidden="true">
                        <span></span>
                    </div>
                    <p><strong data-overview-state><?= e($overview['estado']); ?></strong></p>
                    <span data-overview-telemetry>Telemetría pendiente</span>
                    <div class="activity-bars" aria-hidden="true">
                        <?php for ($i = 0; $i < 24; $i++): ?>
                            <i style="--h: <?= e((string) (24 + (($i * 13) % 46))); ?>%"></i>
                        <?php endfor; ?>
                    </div>
                </aside>

                <div class="welcome-panel">
                    <span class="divider divider-top" aria-hidden="true"></span>
                    <h1 id="page-title">Bienvenido a la<br>War Room,<br>mi Lord Comandante</h1>
                    <span class="crest crest-center">W</span>
                    <p>Centro de mando personal del HomeLab</p>
                    <div class="command-actions" aria-label="Acciones de lectura">
                        <a href="#panel-principal">Entrar</a>
                        <a href="#estado-general">Ver estado</a>
                    </div>
                </div>

                <div class="cards-dock" aria-label="Áreas principales">
                    <?php foreach ($cards as $card): ?>
                        <?php $badgeClass = $statusClasses[$card['status']] ?? 'badge-local'; ?>
                        <article class="command-card">
                            <span class="card-glyph" aria-hidden="true"></span>
                            <div>
                                <h2><?= e($card['title']); ?></h2>
                                <p><?= e($card['summary']); ?></p>
                            </div>
                            <div class="card-bottom">
                                <span><?= e($card['metric']); ?></span>
                                <span class="badge <?= e($badgeClass); ?>"><?= e($card['status']); ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <footer class="system-strip" aria-label="Resumen del sistema">
                <?php foreach ($systemStats as $stat): ?>
                    <div>
                        <span><?= e($stat['label']); ?></span>
                        <strong><?= e($stat['value']); ?></strong>
                        <small><?= e($stat['detail']); ?></small>
                    </div>
                <?php endforeach; ?>
            </footer>

            <section class="manual-reader-view" id="manuales" aria-labelledby="manual-reader-title" data-manual-reader-view hidden>
                <div class="manual-reader-header">
                    <div>
                        <span class="manual-reader-kicker">Documentación segura</span>
                        <h2 id="manual-reader-title">Manuales</h2>
                        <p>Lectura online read-only. Sin ejecución de comandos, terminales ni acciones administrativas.</p>
                    </div>
                    <span class="badge badge-local">READ-ONLY</span>
                </div>

                <div class="manual-reader-grid">
                    <aside class="manual-catalog" aria-label="Catálogo de manuales">
                        <h3>Catálogo</h3>
                        <p data-manual-reader-status>Preparando catálogo documental.</p>
                        <ul class="manual-reader-list" data-manual-reader-list>
                            <li>
                                <button type="button" disabled>Cargando manuales</button>
                            </li>
                        </ul>
                    </aside>

                    <article class="manual-content-card" aria-live="polite">
                        <div class="manual-content-heading">
                            <span data-manual-content-state>Sin selección</span>
                            <h3 data-manual-content-title>Selecciona un manual</h3>
                            <p data-manual-content-summary>El contenido se cargará desde la API read-only cuando esté disponible.</p>
                        </div>
                        <div class="manual-content" data-manual-content>
                            <p>Elige un manual del catálogo para leerlo dentro de la War Room.</p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="operations-view" id="operaciones" aria-labelledby="operations-title" data-operations-view hidden>
                <div class="operations-header">
                    <div>
                        <span class="operations-kicker">Diagnóstico operativo</span>
                        <h2 id="operations-title">Estado Operativo</h2>
                        <p>Vista read-only para revisar estado de fase, seguridad y próximos pasos. Sin cambios en Docker ni acciones administrativas.</p>
                    </div>
                    <span class="badge badge-local">READ-ONLY</span>
                </div>

                <div class="operations-safety" aria-label="Restricciones de seguridad">
                    <span>Sin shell</span>
                    <span>Sin docker.sock</span>
                    <span>Sin comandos libres</span>
                    <span>Sin cambios administrativos</span>
                </div>

                <div class="operations-grid" data-operations-grid>
                    <article class="operations-card">
                        <h3>Cargando diagnóstico</h3>
                        <p>Consultando endpoint read-only de operaciones.</p>
                    </article>
                </div>
            </section>
        </section>

        <aside class="right-rail" id="panel-principal" aria-label="Estado operacional">
            <section class="panel-card state-panel" id="estado-general">
                <div class="panel-heading">
                    <h2>Estado general</h2>
                    <strong><span class="status-dot"></span> <span data-status-mode>Iniciando</span></strong>
                </div>
                <div class="status-ring">
                    <strong data-status-ring>ONLINE</strong>
                    <span data-status-ring-label>MODO API</span>
                </div>
                <ul class="metric-list">
                    <li><span>Servicios</span><strong data-status-services>Pendiente</strong></li>
                    <li><span>Contenedores</span><strong data-status-containers>Pendiente</strong></li>
                    <li><span>Hosts</span><strong data-status-hosts>Pendiente</strong></li>
                    <li><span>Última actualización</span><strong data-status-updated>Sin datos</strong></li>
                </ul>
            </section>

            <section class="panel-card resources-panel">
                <h2>Recursos del sistema</h2>
                <ul class="resource-list">
                    <li><span>CPU</span><strong data-resource-cpu>Pendiente</strong><i aria-hidden="true"></i></li>
                    <li><span>Memoria</span><strong data-resource-memory>Pendiente</strong><i aria-hidden="true"></i></li>
                    <li><span>Almacenamiento</span><strong data-resource-storage>Pendiente</strong><i aria-hidden="true"></i></li>
                </ul>
                <p class="resource-note" data-resource-note>Métricas reales básicas visibles desde el contenedor. Fuente declarada por API.</p>
            </section>

            <section class="panel-card services-panel">
                <div class="panel-heading">
                    <h2>Servicios críticos</h2>
                    <a href="#panel-principal">Ver todo</a>
                </div>
                <ul class="service-list" data-service-list>
                    <?php foreach ($criticalServices as $service): ?>
                        <?php $badgeClass = $statusClasses[$service['state']] ?? 'badge-local'; ?>
                        <li>
                            <div>
                                <strong>
                                    <?php if (!empty($service['url'])): ?>
                                        <a class="service-link" href="<?= e($service['url']); ?>" target="_blank" rel="noopener noreferrer">
                                            <?= e($service['name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?= e($service['name']); ?>
                                    <?php endif; ?>
                                </strong>
                                <span><?= e($service['note']); ?></span>
                            </div>
                            <span class="badge <?= e($badgeClass); ?>"><?= e($service['state']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="panel-card pending-panel" data-task-panel>
                <h2>Checklist</h2>
                <p class="tasks-error" data-task-error hidden>No se pudo cargar la checklist dinámica</p>
                <ul class="pending-list" data-pending-list>
                    <li class="tasks-loading">
                        <span>Cargando checklist dinámica</span>
                        <span class="badge badge-pending">PENDIENTE</span>
                    </li>
                </ul>
            </section>

            <section class="panel-card manuals-panel" data-manuals-panel>
                <div class="panel-heading">
                    <h2>Manuales</h2>
                    <strong><span class="status-dot"></span> <span data-manuals-state>Read-only</span></strong>
                </div>
                <p class="manuals-note" data-manuals-note>Base documental segura preparada para consulta. Sin acciones operativas.</p>
                <p class="manuals-error" data-manuals-error hidden>No se pudo cargar el catálogo de manuales</p>
                <ul class="manuals-list" data-manuals-list>
                    <li>
                        <div>
                            <strong>Cargando manuales</strong>
                            <span>Consulta read-only del catálogo documental.</span>
                        </div>
                        <span class="badge badge-pending">PENDIENTE</span>
                    </li>
                </ul>
            </section>
        </aside>
    </main>
    <script src="/assets/app.js" defer></script>
</body>
</html>
