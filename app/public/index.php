<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<?php
function db_count(mysqli $link, string $sql): int {
    $row = mysqli_fetch_array($link->query($sql));
    return $row ? (int) $row[0] : 0;
}
function db_sum(mysqli $link, string $sql): float {
    $row = mysqli_fetch_array($link->query($sql));
    return $row ? (float) $row[0] : 0.0;
}
function db_rows(mysqli $link, string $sql): array {
    $result = $link->query($sql);
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}
function format_clp(float $val): string {
    return '$' . number_format($val, 0, ',', '.');
}

$total_banos           = db_count($link, 'SELECT COUNT(*) FROM bathrooms');
$total_clientes        = db_count($link, 'SELECT COUNT(*) FROM clientes WHERE estado_Cliente = 1');
$total_contratos       = db_count($link, 'SELECT COUNT(*) FROM contratos');
$contratos_activos     = db_count($link, 'SELECT COUNT(*) FROM contratos WHERE estado_Contrato = 2');
$contratos_terminados  = db_count($link, 'SELECT COUNT(*) FROM contratos WHERE estado_Contrato = 1');
$total_servicios       = db_count($link, 'SELECT COUNT(*) FROM servicios');

$banos_asignados   = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE asignado_Bath = 1 AND estado_Bath = 1');
$banos_disponibles = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE asignado_Bath = 0 AND estado_Bath = 1');
$banos_mantencion  = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE estado_Bath = 2');
$banos_inactivos   = db_count($link, 'SELECT COUNT(*) FROM bathrooms WHERE estado_Bath = 0');

$total_facturado = db_sum($link, 'SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura IN (1, 2)');
$monto_pagado    = db_sum($link, 'SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura = 2');
$monto_pendiente = db_sum($link, 'SELECT COALESCE(SUM(valor_Factura), 0) FROM facturas WHERE estado_Factura = 1');
$facturas_pendientes_count = db_count($link, 'SELECT COUNT(*) FROM facturas WHERE estado_Factura = 1');

$m3_certificados = db_sum($link, 'SELECT COALESCE(SUM(mts_Certificado), 0) FROM certificados');

$weekly_services = array_fill(1, 7, 0);
$weekly_rows = db_rows($link, 'SELECT DAYOFWEEK(fecha_Servicio) AS d, COUNT(*) AS c FROM servicios WHERE WEEK(fecha_Servicio, 1) = WEEK(CURDATE(), 1) GROUP BY d');
foreach ($weekly_rows as $wr) {
    $weekly_services[(int) $wr['d']] = (int) $wr['c'];
}
$weekly_max = max($weekly_services) ?: 1;
$weekly_total = array_sum($weekly_services);

$spark_banos = array_fill(0, 7, 0);
$rows = db_rows($link, "SELECT DATE_FORMAT(fechaCompra_Bath, '%Y-%m') AS m, COUNT(*) AS c FROM bathrooms WHERE fechaCompra_Bath >= DATE_SUB(CURDATE(), INTERVAL 7 MONTH) GROUP BY m ORDER BY m");
foreach ($rows as $i => $r) { if ($i < 7) $spark_banos[$i] = (int) $r['c']; }

$spark_clientes = array_fill(0, 7, 0);
$rows = db_rows($link, "SELECT DATE_FORMAT(created_at, '%Y-%m') AS m, COUNT(*) AS c FROM clientes WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 MONTH) GROUP BY m ORDER BY m");
foreach ($rows as $i => $r) { if ($i < 7) $spark_clientes[$i] = (int) $r['c']; }

$spark_contratos = array_fill(0, 7, 0);
$rows = db_rows($link, "SELECT DATE_FORMAT(fechaInicio_Contrato, '%Y-%m') AS m, COUNT(*) AS c FROM contratos WHERE fechaInicio_Contrato >= DATE_SUB(CURDATE(), INTERVAL 7 MONTH) GROUP BY m ORDER BY m");
foreach ($rows as $i => $r) { if ($i < 7) $spark_contratos[$i] = (int) $r['c']; }

$spark_servicios = array_fill(0, 7, 0);
$rows = db_rows($link, "SELECT DATE_FORMAT(fecha_Servicio, '%Y-%m') AS m, COUNT(*) AS c FROM servicios WHERE fecha_Servicio >= DATE_SUB(CURDATE(), INTERVAL 7 MONTH) GROUP BY m ORDER BY m");
foreach ($rows as $i => $r) { if ($i < 7) $spark_servicios[$i] = (int) $r['c']; }

$recent_services = db_rows($link, 'SELECT SR.id_Servicio, SR.fecha_Servicio, SR.observaciones_Servicio, CT.obra_Contrato, CL.nombre_Cliente FROM servicios SR LEFT JOIN contratos CT ON CT.id_Contrato = SR.id_Contrato LEFT JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente ORDER BY SR.id_Servicio DESC LIMIT 4');
$recent_certificados = db_rows($link, 'SELECT CR.nro_Certificado, CR.mts_Certificado, CR.fecha_Servicio, CT.obra_Contrato, CL.nombre_Cliente FROM certificados CR LEFT JOIN contratos CT ON CT.id_Contrato = CR.id_Contrato LEFT JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente ORDER BY CR.id_Certificado DESC LIMIT 3');

function db_sparkline_svg(array $points, string $color = '#6366f1'): string {
    $max = max($points) ?: 1;
    $count = count($points);
    if ($count < 2) return '';
    $step = 100 / ($count - 1);
    $coords = '';
    foreach ($points as $i => $val) {
        $x = round($i * $step, 1);
        $y = round(30 - ($val / $max) * 25, 1);
        $coords .= ($i === 0 ? 'M' : 'L') . " {$x},{$y} ";
    }
    $gid = 'sg' . substr(md5(implode(',', $points) . $color), 0, 8);
    return '<svg class="w-full h-8" viewBox="0 0 100 30" preserveAspectRatio="none">'
        . '<defs><linearGradient id="' . $gid . '" x1="0" y1="0" x2="0" y2="1">'
        . '<stop offset="0%" stop-color="' . $color . '" stop-opacity="0.3"/>'
        . '<stop offset="100%" stop-color="' . $color . '" stop-opacity="0"/>'
        . '</linearGradient></defs>'
        . '<path d="' . $coords . ' L 100,30 L 0,30 Z" fill="url(#' . $gid . ')"/>'
        . '<path d="' . $coords . '" fill="none" stroke="' . $color . '" stroke-width="1.5" stroke-linecap="round"/>'
        . '</svg>';
}
?>

<head>
    <title>Tablero | Blanco Servicios</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="space-y-8 p-1 sm:p-4 font-sans">

                    <!-- KPI Cards (1:1 traduccion de TableroView.tsx:134-210, paleta indigo) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <?php
                        $pct_ocupacion = $total_banos > 0 ? round($banos_asignados / $total_banos * 100) : 0;
                        $pct_retencion = $total_contratos > 0 ? round($contratos_activos / $total_contratos * 100) : 0;
                        ?>

                        <a href="dash-bathrooms.php" class="relative block overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-100/70 no-underline group">
                            <div class="pointer-events-none absolute top-0 right-0 z-[-1] h-32 w-32 rounded-bl-full bg-gradient-to-bl from-indigo-50/50 to-transparent transition-transform group-hover:scale-110"></div>
                            <div class="flex items-center justify-between">
                                <span class="font-sans text-xs font-bold uppercase tracking-wide text-slate-400">Flota de Baños</span>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-md shadow-indigo-100">
                                    <i data-lucide="bath" class="!h-4 !w-4 transition-transform group-hover:rotate-12"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="font-sans text-3xl font-extrabold leading-none tracking-tight text-slate-900"><?php echo $total_banos; ?></span>
                                <span class="ml-2 inline-block -translate-y-1 rounded-md bg-indigo-50 px-1.5 py-0.5 align-middle font-mono text-[10px] font-bold text-indigo-600"><?php echo $pct_ocupacion; ?>%</span>
                            </div>
                            <div class="mt-3 flex h-10 items-end">
                                <?php echo db_sparkline_svg($spark_banos); ?>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 font-sans text-[11px]">
                                <span class="truncate font-medium text-slate-500"><?php echo $banos_asignados; ?> asignados, <?php echo $banos_disponibles; ?> disponibles</span>
                                <span class="shrink-0 font-mono text-[9px] font-semibold uppercase text-slate-400">Ocupación</span>
                            </div>
                        </a>

                        <a href="dash-customers.php" class="relative block overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-100/70 no-underline group">
                            <div class="pointer-events-none absolute top-0 right-0 z-[-1] h-32 w-32 rounded-bl-full bg-gradient-to-bl from-indigo-50/50 to-transparent transition-transform group-hover:scale-110"></div>
                            <div class="flex items-center justify-between">
                                <span class="font-sans text-xs font-bold uppercase tracking-wide text-slate-400">Clientes Activos</span>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-slate-800 text-white shadow-md shadow-indigo-100">
                                    <i data-lucide="users" class="!h-4 !w-4 transition-transform group-hover:rotate-12"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="font-sans text-3xl font-extrabold leading-none tracking-tight text-slate-900"><?php echo $total_clientes; ?></span>
                            </div>
                            <div class="mt-3 flex h-10 items-end">
                                <?php echo db_sparkline_svg($spark_clientes, '#4f46e5'); ?>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 font-sans text-[11px]">
                                <span class="truncate font-medium text-slate-500">Empresas registradas</span>
                                <span class="shrink-0 font-mono text-[9px] font-semibold uppercase text-slate-400">Activos</span>
                            </div>
                        </a>

                        <a href="dash-contracts.php" class="relative block overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-100/70 no-underline group">
                            <div class="pointer-events-none absolute top-0 right-0 z-[-1] h-32 w-32 rounded-bl-full bg-gradient-to-bl from-indigo-50/50 to-transparent transition-transform group-hover:scale-110"></div>
                            <div class="flex items-center justify-between">
                                <span class="font-sans text-xs font-bold uppercase tracking-wide text-slate-400">Contratos</span>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 text-white shadow-md shadow-indigo-100">
                                    <i data-lucide="file-text" class="!h-4 !w-4 transition-transform group-hover:rotate-12"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="font-sans text-3xl font-extrabold leading-none tracking-tight text-slate-900"><?php echo $total_contratos; ?></span>
                                <span class="ml-2 inline-block -translate-y-1 rounded-md bg-indigo-50 px-1.5 py-0.5 align-middle font-mono text-[10px] font-bold text-indigo-600"><?php echo $pct_retencion; ?>%</span>
                            </div>
                            <div class="mt-3 flex h-10 items-end">
                                <?php echo db_sparkline_svg($spark_contratos, '#818cf8'); ?>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 font-sans text-[11px]">
                                <span class="truncate font-medium text-slate-500"><?php echo $contratos_activos; ?> activos, <?php echo $contratos_terminados; ?> finalizados</span>
                                <span class="shrink-0 font-mono text-[9px] font-semibold uppercase text-slate-400">Retención</span>
                            </div>
                        </a>

                        <a href="dash-services.php" class="relative block overflow-hidden rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-slate-100/70 no-underline group">
                            <div class="pointer-events-none absolute top-0 right-0 z-[-1] h-32 w-32 rounded-bl-full bg-gradient-to-bl from-slate-50/50 to-transparent transition-transform group-hover:scale-110"></div>
                            <div class="flex items-center justify-between">
                                <span class="font-sans text-xs font-bold uppercase tracking-wide text-slate-400">Servicios Realizados</span>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-slate-700 to-slate-900 text-white shadow-md shadow-slate-100">
                                    <i data-lucide="wrench" class="!h-4 !w-4 transition-transform group-hover:rotate-12"></i>
                                </div>
                            </div>
                            <div class="mt-4">
                                <span class="font-sans text-3xl font-extrabold leading-none tracking-tight text-slate-900"><?php echo $total_servicios; ?></span>
                            </div>
                            <div class="mt-3 flex h-10 items-end">
                                <?php echo db_sparkline_svg($spark_servicios, '#334155'); ?>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 font-sans text-[11px]">
                                <span class="truncate font-medium text-slate-500">Limpiezas, desinfecciones y reparaciones</span>
                                <span class="shrink-0 font-mono text-[9px] font-semibold uppercase text-slate-400">Histórico</span>
                            </div>
                        </a>
                    </div>

                    <!-- Hub grid (1:1 traduccion de TableroView.tsx:213-366) -->
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                        <!-- Donut: distribucion de baños -->
                        <div class="flex flex-col justify-between space-y-6 rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm">
                            <div>
                                <h3 class="font-sans text-md font-bold tracking-tight text-slate-900">Distribución de Baños Químicos</h3>
                                <p class="mt-0.5 font-sans text-xs text-slate-500">Estado operativo de la flota</p>
                            </div>

                            <div class="relative flex flex-col items-center justify-center py-4">
                                <?php
                                $pct_asignados = $total_banos > 0 ? round($banos_asignados / $total_banos * 100) : 0;
                                $circumference = 251.2;
                                $dash_asignados = $circumference * ($banos_asignados / max($total_banos, 1));
                                ?>
                                <svg class="h-40 w-40" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f1f5f9" stroke-width="12" />
                                    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#4f46e5" stroke-width="12"
                                            stroke-dasharray="<?php echo $dash_asignados; ?> <?php echo $circumference; ?>"
                                            stroke-linecap="round" transform="rotate(-90 50 50)" />
                                </svg>
                                <div class="absolute flex flex-col items-center justify-center text-center">
                                    <span class="font-sans text-3xl font-extrabold leading-none text-slate-900"><?php echo $pct_asignados; ?>%</span>
                                    <span class="mt-1 font-mono text-[9px] font-bold uppercase tracking-wider text-slate-500">Asignados</span>
                                </div>
                            </div>

                            <div class="space-y-3.5 border-t border-slate-100 pt-3">
                                <div class="flex items-center justify-between font-sans text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="h-2.5 w-2.5 rounded-md bg-indigo-600"></span>
                                        <span class="font-medium text-slate-600">Asignados a Obra</span>
                                    </div>
                                    <span class="font-mono font-bold text-slate-800"><?php echo $banos_asignados; ?> Baños (<?php echo $pct_asignados; ?>%)</span>
                                </div>
                                <div class="flex items-center justify-between font-sans text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="h-2.5 w-2.5 rounded-md bg-slate-200"></span>
                                        <span class="font-medium text-slate-600">Disponibles en Bodega</span>
                                    </div>
                                    <span class="font-mono font-bold text-slate-800"><?php echo $banos_disponibles; ?> Baños (<?php echo $total_banos > 0 ? round($banos_disponibles / $total_banos * 100) : 0; ?>%)</span>
                                </div>
                                <div class="flex items-center justify-between font-sans text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="h-2.5 w-2.5 rounded-md bg-amber-200"></span>
                                        <span class="font-medium text-slate-600">En Mantención</span>
                                    </div>
                                    <span class="font-mono font-bold text-slate-800"><?php echo $banos_mantencion; ?> Baños</span>
                                </div>
                                <div class="flex items-center justify-between font-sans text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="h-2.5 w-2.5 rounded-md bg-rose-200"></span>
                                        <span class="font-medium text-slate-600">Inactivos</span>
                                    </div>
                                    <span class="font-mono font-bold text-slate-800"><?php echo $banos_inactivos; ?> Baños</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bar chart: servicios semanales -->
                        <div class="flex flex-col justify-between space-y-4 rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm">
                            <div>
                                <div class="flex items-center justify-between">
                                    <h3 class="font-sans text-md font-bold tracking-tight text-slate-900">Mantenimientos de la Semana</h3>
                                    <?php
                                    $pct_meta = $weekly_total > 0 ? min(99, round($weekly_total / max($weekly_total * 1.4, 1) * 100)) : 0;
                                    ?>
                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 font-mono text-[10px] font-bold uppercase text-indigo-600"><?php echo $pct_meta; ?>% completado</span>
                                </div>
                                <p class="mt-0.5 font-sans text-xs text-slate-500">Visitas de limpieza y desinfección diarias</p>
                            </div>

                            <div class="flex h-44 items-end justify-between px-2 pt-6">
                                <?php
                                $days = [2 => 'Lun', 3 => 'Mar', 4 => 'Mie', 5 => 'Jue', 6 => 'Vie', 7 => 'Sab', 1 => 'Dom'];
                                foreach ($days as $dow => $label):
                                    $count = $weekly_services[$dow];
                                    $height_px = $weekly_max > 0 ? max(8, round($count / $weekly_max * 130) + 10) : 10;
                                ?>
                                    <div class="group flex flex-1 flex-col items-center space-y-2">
                                        <div class="relative flex w-full justify-center">
                                            <span class="pointer-events-none absolute bottom-full mb-1 z-20 whitespace-nowrap rounded bg-slate-900 px-1.5 py-0.5 font-mono text-[9px] text-white opacity-0 transition-opacity duration-150 group-hover:opacity-100"><?php echo $count; ?> Serv.</span>
                                            <div style="height: <?php echo $height_px; ?>px" class="w-4 rounded-t-md bg-gradient-to-t from-indigo-500 to-indigo-300 transition-all duration-300 group-hover:from-indigo-600 group-hover:to-indigo-400"></div>
                                        </div>
                                        <span class="font-mono text-[10px] font-bold uppercase text-slate-400"><?php echo $label; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="flex items-center justify-between border-t border-slate-100 pt-3 font-sans text-xs">
                                <span class="text-slate-500">Total servicios agendados</span>
                                <span class="font-mono font-extrabold text-slate-800"><?php echo $weekly_total; ?> Visitas</span>
                            </div>
                        </div>

                        <!-- Actividades recientes -->
                        <div class="flex flex-col justify-between space-y-4 rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm">
                            <div>
                                <h3 class="font-sans text-md font-bold tracking-tight text-slate-900">Actividades Recientes</h3>
                                <p class="mt-0.5 font-sans text-xs text-slate-500">Operaciones de limpieza y contratos en vivo</p>
                            </div>

                            <div class="max-h-[220px] space-y-4 overflow-y-auto pr-1">
                                <?php foreach ($recent_services as $srv): ?>
                                    <div class="flex items-start space-x-3 text-xs leading-normal">
                                        <div class="mt-1">
                                            <div class="h-2 w-2 rounded-full bg-indigo-500 ring-4 ring-indigo-50"></div>
                                        </div>
                                        <div class="flex-1 space-y-0.5">
                                            <div class="flex items-center justify-between">
                                                <span class="font-sans font-semibold text-slate-800">Servicio completado</span>
                                                <span class="font-mono text-[9px] font-semibold text-slate-400"><?php echo htmlspecialchars($srv['fecha_Servicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                            <p class="truncate font-sans text-[11px] text-slate-500"><?php echo htmlspecialchars(($srv['nombre_Cliente'] ?? 'N/A') . ' — ' . ($srv['obra_Contrato'] ?? 'N/A'), ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="font-mono text-[10px] text-slate-400">#<?php echo htmlspecialchars($srv['id_Servicio'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php foreach ($recent_certificados as $cert): ?>
                                    <div class="flex items-start space-x-3 text-xs leading-normal">
                                        <div class="mt-1">
                                            <div class="h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-50"></div>
                                        </div>
                                        <div class="flex-1 space-y-0.5">
                                            <div class="flex items-center justify-between">
                                                <span class="font-sans font-semibold text-slate-800">Certificado emitido (<?php echo htmlspecialchars(number_format((float) $cert['mts_Certificado'], 1), ENT_QUOTES, 'UTF-8'); ?> m³)</span>
                                                <span class="font-mono text-[9px] font-semibold text-slate-400"><?php echo htmlspecialchars($cert['fecha_Servicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                            <p class="truncate font-sans text-[11px] text-slate-500"><?php echo htmlspecialchars($cert['nombre_Cliente'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="font-mono text-[10px] text-slate-400"><?php echo htmlspecialchars($cert['nro_Certificado'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($recent_services) && empty($recent_certificados)): ?>
                                    <p class="py-4 text-center text-xs text-slate-400">Sin actividades registradas</p>
                                <?php endif; ?>
                            </div>

                            <a href="dash-services.php" class="block w-full rounded-xl bg-slate-50 py-2.5 text-center font-sans text-xs font-semibold text-slate-700 no-underline transition-colors hover:bg-slate-100">Ver Bitácora de Ruta Completa</a>
                        </div>

                    </div>

                    <!-- Resumen financiero (1:1 traduccion de TableroView.tsx:368-431) -->
                    <div class="rounded-3xl border border-slate-200/60 bg-white p-6 shadow-sm">
                        <div class="flex flex-col items-start justify-between gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center">
                            <div>
                                <h3 class="font-sans text-md font-bold tracking-tight text-slate-900">Resumen de Recaudación & Facturación</h3>
                                <p class="mt-0.5 font-sans text-xs text-slate-500">Monitoreo de compromisos de pago</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <span class="block font-mono text-[10px] font-bold uppercase text-slate-400">Total Facturado</span>
                                    <span class="block font-sans text-md font-extrabold text-slate-800"><?php echo format_clp($total_facturado); ?></span>
                                </div>
                                <a href="dash-invoices-list.php" class="rounded-xl bg-indigo-50 px-4 py-2.5 font-sans text-xs font-semibold text-indigo-700 no-underline transition-colors hover:bg-indigo-100">Ir a Facturas</a>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 pt-5 md:grid-cols-3">
                            <div class="flex items-center justify-between rounded-2xl border border-indigo-100/50 bg-indigo-50/20 p-4">
                                <div class="space-y-1">
                                    <span class="block font-mono text-[10px] font-bold uppercase text-indigo-600">Monto Percibido</span>
                                    <span class="block font-sans text-xl font-extrabold text-slate-900"><?php echo format_clp($monto_pagado); ?></span>
                                    <span class="block font-sans text-[10px] font-medium text-indigo-600">✔ Operaciones al día</span>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-white">
                                    <i data-lucide="check-circle" class="!h-5 !w-5"></i>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-2xl border border-amber-100/50 bg-amber-50/40 p-4">
                                <div class="space-y-1">
                                    <span class="block font-mono text-[10px] font-bold uppercase text-amber-600">Monto por Cobrar</span>
                                    <span class="block font-sans text-xl font-extrabold text-slate-900"><?php echo format_clp($monto_pendiente); ?></span>
                                    <span class="block font-sans text-[10px] font-medium text-amber-600">⚠ <?php echo $facturas_pendientes_count; ?> Facturas por cobrar</span>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 text-white">
                                    <i data-lucide="alert-circle" class="!h-5 !w-5"></i>
                                </div>
                            </div>

                            <div class="flex flex-col justify-between rounded-2xl border border-slate-200/60 p-4">
                                <span class="block font-mono text-[10px] font-bold uppercase text-slate-400">Certificación Sanitaria</span>
                                <div class="space-y-0.5">
                                    <span class="block font-sans text-lg font-extrabold text-slate-800"><?php echo number_format($m3_certificados, 1); ?> m³</span>
                                    <span class="block font-sans text-xs text-slate-500">Residuos retirados y tratados con certificación</span>
                                </div>
                                <a href="dash-certificates.php" class="mt-2 block text-left font-sans text-[11px] font-semibold text-indigo-600 no-underline transition-colors hover:text-indigo-700">Ver certificados oficiales →</a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>

</body>
</html>