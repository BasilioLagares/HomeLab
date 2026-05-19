<?php
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

rateLimit('resources', 60, 60);

function metric(float|null $value, string $status, string $source, string $note): array
{
    return [
        'value' => $value,
        'unit' => '%',
        'status' => $status,
        'source' => $source,
        'note' => $note,
    ];
}

function readCpuMetric(): array
{
    $load = sys_getloadavg();

    if ($load === false || !isset($load[0])) {
        return metric(null, 'pending', 'none', 'No se pudo leer load average.');
    }

    $cpuCount = 1;
    $cpuInfo = @file('/proc/cpuinfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (is_array($cpuInfo)) {
        $count = 0;

        foreach ($cpuInfo as $line) {
            if (str_starts_with($line, 'processor')) {
                $count++;
            }
        }

        $cpuCount = max(1, $count);
    }

    $value = min(100, max(0, ($load[0] / $cpuCount) * 100));

    return metric(
        round($value, 1),
        'real',
        'proc_loadavg',
        'Estimación basada en load average de 1 minuto visible desde el contenedor.'
    );
}

function readMemoryMetric(): array
{
    $raw = @file('/proc/meminfo', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (!is_array($raw)) {
        return metric(null, 'pending', 'none', 'No se pudo leer /proc/meminfo.');
    }

    $mem = [];

    foreach ($raw as $line) {
        if (preg_match('/^([A-Za-z_()]+):\s+(\d+)\s+kB$/', $line, $matches) === 1) {
            $mem[$matches[1]] = (int) $matches[2];
        }
    }

    $total = $mem['MemTotal'] ?? 0;
    $available = $mem['MemAvailable'] ?? 0;

    if ($total <= 0 || $available <= 0) {
        return metric(null, 'pending', 'none', 'MemTotal/MemAvailable no disponibles.');
    }

    $used = $total - $available;
    $value = ($used / $total) * 100;

    return metric(
        round($value, 1),
        'real',
        'proc_meminfo',
        'Memoria calculada con MemTotal y MemAvailable visibles desde el contenedor.'
    );
}

function readStorageMetric(): array
{
    $total = @disk_total_space('/');
    $free = @disk_free_space('/');

    if ($total === false || $free === false || $total <= 0) {
        return metric(null, 'pending', 'none', 'No se pudo leer el uso de almacenamiento.');
    }

    $used = $total - $free;
    $value = ($used / $total) * 100;

    return metric(
        round($value, 1),
        'real',
        'php_disk_space',
        'Almacenamiento calculado sobre el filesystem visible en el contenedor.'
    );
}

$cpu = readCpuMetric();
$memory = readMemoryMetric();
$storage = readStorageMetric();

$allReal = $cpu['status'] === 'real'
    && $memory['status'] === 'real'
    && $storage['status'] === 'real';

jsonResponse([
    'last_update' => nowIso(),
    'state' => $allReal ? 'operational' : 'partial',
    'cpu' => $cpu,
    'memory' => $memory,
    'storage' => $storage,
    'source' => 'container_procfs_php',
]);
