<?php
// =============================================================================
// scripts/purge-logs-actividad.php
//
// Politica de retencion del log de actividad (plan-log-actividad.md §8):
//   borrar los registros de logs_actividad con fecha_Log estrictamente
//   anterior a 12 meses desde el momento de ejecucion.
//
// Por que este script y no un MariaDB EVENT:
//   El contenedor MySQL/MariaDB del proyecto actual tiene event_scheduler=OFF
//   (verificado con SHOW VARIABLES LIKE 'event_scheduler'). Habilitarlo
//   requiere tocar la configuracion del contenedor (my.cnf +
//   docker-compose.yml + restart), cosa que no queremos hacer para esta tarea
//   opcional. Este script PHP es la alternativa portable: lo agenda el cron
//   externo del hosting (cPanel / Vercel cron / similar).
//
// Como invocarlo desde cron del hosting:
//   /usr/local/bin/php /home/<usuario>/<sitio>/app/public/cron/purge-logs-actividad.php
//   o equivalentemente, via docker exec:
//   docker-compose exec -T php php /var/www/html/cron/purge-logs-actividad.php
// Frecuencia sugerida: 1 vez por mes (ej: 1 de cada mes, 03:00 AM).
//
// Seguridad:
//   El script verifica php_sapi_name() al inicio y muere si se intenta
//   invocar desde HTTP. NO es un controller normal.
// =============================================================================

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden: este script solo puede ejecutarse desde la linea de comandos (cron).' . PHP_EOL);
}

require_once __DIR__ . '/../layouts/config.php';

if (!isset($link) || !($link instanceof mysqli)) {
    fwrite(STDERR, "Error: no se pudo obtener la conexion mysqli desde config.php" . PHP_EOL);
    exit(1);
}

$retention_months = 12;
$cutoff = (new DateTimeImmutable('now'))->modify("-{$retention_months} months");
$cutoff_str = $cutoff->format('Y-m-d H:i:s');

$stmt = $link->prepare('DELETE FROM logs_actividad WHERE fecha_Log < ?');
if ($stmt === false) {
    fwrite(STDERR, "Error al preparar la consulta: " . $link->error . PHP_EOL);
    $link->close();
    exit(1);
}

$stmt->bind_param('s', $cutoff_str);

if (!$stmt->execute()) {
    fwrite(STDERR, "Error al ejecutar la consulta: " . $stmt->error . PHP_EOL);
    $stmt->close();
    $link->close();
    exit(1);
}

$deleted = $stmt->affected_rows;
$stmt->close();

echo sprintf(
    "[purge-logs-actividad] OK: borrados %d registros anteriores a %s (retencion=%d meses)" . PHP_EOL,
    $deleted,
    $cutoff_str,
    $retention_months,
);

$link->close();
exit(0);
