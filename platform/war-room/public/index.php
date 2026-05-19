<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Madrid');

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$now = new DateTimeImmutable('now', new DateTimeZone('Europe/Madrid'));

$overview = [
    'estado' => 'PANEL OPERATIVO LOCAL',
    'modo' => 'Modo dinámico (API)',
    'version' => 'WAR ROOM V0.4',
    'zona' => 'Europe/Madrid',
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

$criticalServices = [
    ['name' => 'War Room', 'state' => 'UP', 'note' => 'https://warroom.homelab.home.arpa', 'url' => 'https://warroom.homelab.home.arpa'],
    ['name' => 'Homepage', 'state' => 'UP', 'note' => 'https://homepage.homelab.home.arpa', 'url' => 'https://homepage.homelab.home.arpa'],
    ['name' => 'Uptime Kuma', 'state' => 'UP', 'note' => 'https://kuma.homelab.home.arpa', 'url' => 'https://kuma.homelab.home.arpa'],
    ['name' => 'Adminer', 'state' => 'LOCAL', 'note' => 'https://adminer.homelab.home.arpa (protegido con Basic Auth)', 'url' => 'https://adminer.homelab.home.arpa'],
    ['name' => 'PHP Demo', 'state' => 'LOCAL', 'note' => 'https://php.homelab.home.arpa', 'url' => 'https://php.homelab.home.arpa'],
    ['name' => 'Mariano Limón', 'state' => 'LOCAL', 'note' => 'https://mariano.homelab.home.arpa', 'url' => 'https://mariano.homelab.home.arpa'],
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
                    <a class="<?= $index === 0 ? 'is-active' : ''; ?>" href="#panel-principal">
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
        </aside>
    </main>
    <script src="/assets/app.js" defer></script>
</body>
</html>
