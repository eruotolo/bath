<?php global $link;
include 'layouts/session.php'; ?>
<?php
require_once 'layouts/permissions.php';
require_min_nivel(NIVEL_SUPERADMIN);
?>
<?php include 'layouts/head-main.php'; ?>

<?php
include 'layouts/config.php';
include 'layouts/helpers.php';

$acciones_conocidas = [
    'CREATE', 'UPDATE', 'DELETE', 'ACTIVATE', 'DEACTIVATE', 'ASSIGN', 'UNASSIGN',
    'TERMINATE', 'STATE_CHANGE', 'ROLE_CHANGE', 'PASSWORD_RESET', 'IMPORT',
    'EXPORT', 'PDF', 'LOGIN', 'LOGOUT', 'ERROR',
];
$entidades_conocidas = ['Bathroom', 'Customer', 'Contact', 'Contract', 'Service', 'Invoice', 'Certificate', 'User'];

// --- Filtros (todos opcionales, vía GET) ---
$f_usuario = trim((string) ($_GET['usuario'] ?? ''));
$f_entidad = in_array($_GET['entidad'] ?? '', $entidades_conocidas, true) ? $_GET['entidad'] : '';
$f_accion = in_array($_GET['accion'] ?? '', $acciones_conocidas, true) ? $_GET['accion'] : '';
$f_resultado = in_array($_GET['resultado'] ?? '', ['success', 'error'], true) ? $_GET['resultado'] : '';
$f_desde = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['desde'] ?? '')) ? $_GET['desde'] : '';
$f_hasta = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['hasta'] ?? '')) ? $_GET['hasta'] : '';

$condiciones = [];
$params = [];

if ($f_usuario !== '') {
    $condiciones[] = 'username_Log LIKE ?';
    $params[] = '%' . $f_usuario . '%';
}
if ($f_entidad !== '') {
    $condiciones[] = 'entidad_Log = ?';
    $params[] = $f_entidad;
}
if ($f_accion !== '') {
    $condiciones[] = 'accion_Log = ?';
    $params[] = $f_accion;
}
if ($f_resultado !== '') {
    $condiciones[] = 'resultado_Log = ?';
    $params[] = $f_resultado;
}
if ($f_desde !== '') {
    $condiciones[] = 'fecha_Log >= ?';
    $params[] = $f_desde . ' 00:00:00';
}
if ($f_hasta !== '') {
    $condiciones[] = 'fecha_Log <= ?';
    $params[] = $f_hasta . ' 23:59:59';
}

$where_sql = $condiciones ? ('WHERE ' . implode(' AND ', $condiciones)) : '';

// --- Paginación server-side ---
$per_page = 25;
$page = max(1, (int) ($_GET['page'] ?? 1));

$total_result = $params
    ? $link->execute_query("SELECT COUNT(*) AS total FROM logs_actividad {$where_sql}", $params)
    : $link->execute_query("SELECT COUNT(*) AS total FROM logs_actividad {$where_sql}");
$total = (int) $total_result->fetch_assoc()['total'];
$total_pages = max(1, (int) ceil($total / $per_page));
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $per_page;

$select_sql = "SELECT id_Log, id_Usuario, username_Log, accion_Log, entidad_Log, entidad_id_Log,
                      descripcion_Log, pantalla_Log, metodo_Log, datos_Log, resultado_Log,
                      ip_Log, user_agent_Log, fecha_Log
               FROM logs_actividad {$where_sql}
               ORDER BY fecha_Log DESC
               LIMIT ? OFFSET ?";
$select_params = array_merge($params, [$per_page, $offset]);
$rows_result = $link->execute_query($select_sql, $select_params);
$rows = $rows_result->fetch_all(MYSQLI_ASSOC);

function activity_log_query_string(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    $params = array_filter($params, static fn($v) => $v !== '' && $v !== null);
    return $params ? ('?' . http_build_query($params)) : '?';
}

function activity_log_accion_badge(string $accion): string
{
    $variantes = [
        'CREATE' => 'is-success', 'ACTIVATE' => 'is-success', 'LOGIN' => 'is-success',
        'DELETE' => 'is-danger', 'DEACTIVATE' => 'is-danger', 'TERMINATE' => 'is-danger', 'LOGOUT' => 'is-danger',
        'ERROR' => 'is-danger',
    ];
    return $variantes[$accion] ?? 'is-info';
}
?>

<head>
    <title>Bitácora de Actividad | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid px-10 py-10 bg-slate-50/50">

                <div class="space-y-4">

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="font-sans font-bold text-xl text-slate-900">Bitácora de Actividad</h1>
                            <p class="font-sans text-sm text-slate-500 mt-1">Registro de acciones ejecutadas por los usuarios del sistema. Acceso restringido a SuperAdministrador.</p>
                        </div>
                        <span class="font-mono text-[10px] text-slate-400 font-bold uppercase"><?php echo number_format($total); ?> registros totales</span>
                    </div>

                    <!-- Filtros -->
                    <form method="get" class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
                        <div class="lg:col-span-2">
                            <label class="block text-[10px] font-mono font-bold uppercase text-slate-400 mb-1">Usuario</label>
                            <input type="text" name="usuario" value="<?php echo htmlspecialchars($f_usuario, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nombre de usuario..."
                                   class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors font-sans">
                        </div>
                        <div>
                            <label class="block text-[10px] font-mono font-bold uppercase text-slate-400 mb-1">Entidad</label>
                            <select name="entidad" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors font-sans">
                                <option value="">Todas</option>
                                <?php foreach ($entidades_conocidas as $e): ?>
                                    <option value="<?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $f_entidad === $e ? 'selected' : ''; ?>><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-mono font-bold uppercase text-slate-400 mb-1">Acción</label>
                            <select name="accion" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors font-sans">
                                <option value="">Todas</option>
                                <?php foreach ($acciones_conocidas as $a): ?>
                                    <option value="<?php echo htmlspecialchars($a, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $f_accion === $a ? 'selected' : ''; ?>><?php echo htmlspecialchars($a, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-mono font-bold uppercase text-slate-400 mb-1">Resultado</label>
                            <select name="resultado" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors font-sans">
                                <option value="">Todos</option>
                                <option value="success" <?php echo $f_resultado === 'success' ? 'selected' : ''; ?>>Éxito</option>
                                <option value="error" <?php echo $f_resultado === 'error' ? 'selected' : ''; ?>>Error</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="w-full px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl font-sans text-xs font-semibold transition-all active:scale-95">Filtrar</button>
                        </div>
                        <div>
                            <label class="block text-[10px] font-mono font-bold uppercase text-slate-400 mb-1">Desde</label>
                            <input type="date" name="desde" value="<?php echo htmlspecialchars($f_desde, ENT_QUOTES, 'UTF-8'); ?>"
                                   class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors font-sans">
                        </div>
                        <div>
                            <label class="block text-[10px] font-mono font-bold uppercase text-slate-400 mb-1">Hasta</label>
                            <input type="date" name="hasta" value="<?php echo htmlspecialchars($f_hasta, ENT_QUOTES, 'UTF-8'); ?>"
                                   class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors font-sans">
                        </div>
                        <?php if ($f_usuario !== '' || $f_entidad !== '' || $f_accion !== '' || $f_resultado !== '' || $f_desde !== '' || $f_hasta !== ''): ?>
                            <div class="flex items-end lg:col-span-2">
                                <a href="dash-activity-log.php" class="w-full text-center px-4 py-2 border border-slate-200 text-slate-500 hover:bg-slate-50 rounded-xl font-sans text-xs font-semibold transition-all">Limpiar filtros</a>
                            </div>
                        <?php endif; ?>
                    </form>

                    <!-- Tabla -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-50 bg-slate-50/50">
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Fecha</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Usuario</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Acción</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Entidad</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Descripción</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Pantalla</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">IP</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Resultado</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right whitespace-nowrap">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach ($rows as $row): ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700 whitespace-nowrap"><?php echo htmlspecialchars(format_fecha($row['fecha_Log']), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700 whitespace-nowrap"><?php echo htmlspecialchars($row['username_Log'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="badge-status <?php echo activity_log_accion_badge($row['accion_Log']); ?>"><?php echo htmlspecialchars($row['accion_Log'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700 whitespace-nowrap">
                                                <?php if ($row['entidad_Log']): ?>
                                                    <?php echo htmlspecialchars($row['entidad_Log'], ENT_QUOTES, 'UTF-8'); ?><?php echo $row['entidad_id_Log'] !== null ? ' #' . (int) $row['entidad_id_Log'] : ''; ?>
                                                <?php else: ?>
                                                    <span class="text-slate-300">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-600 max-w-xs truncate" title="<?php echo htmlspecialchars($row['descripcion_Log'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['descripcion_Log'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-mono text-xs text-slate-400 whitespace-nowrap"><?php echo htmlspecialchars($row['pantalla_Log'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-mono text-xs text-slate-400 whitespace-nowrap"><?php echo htmlspecialchars($row['ip_Log'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($row['resultado_Log'] === 'error'): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-semibold uppercase tracking-wide">Error</span>
                                                <?php else: ?>
                                                    <span class="badge-status is-success">Éxito</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <button type="button"
                                                        class="dt-cell-action js-log-detail-btn"
                                                        title="Ver detalle"
                                                        data-log-detail="<?php echo htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i data-lucide="eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($rows) === 0): ?>
                                        <tr>
                                            <td colspan="9" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                                No se registran acciones que coincidan con los filtros.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                            <span class="font-mono text-[10px] text-slate-400 font-bold uppercase">
                                <?php
                                $first_shown = $total > 0 ? $offset + 1 : 0;
                                $last_shown = min($offset + $per_page, $total);
                                echo "Mostrando {$first_shown}-{$last_shown} de {$total} registros";
                                ?>
                            </span>
                            <div class="flex items-center space-x-1">
                                <?php if ($page > 1): ?>
                                    <a href="<?php echo htmlspecialchars(activity_log_query_string(['page' => $page - 1]), ENT_QUOTES, 'UTF-8'); ?>" class="px-3 py-1 rounded-lg border border-slate-100 text-xs font-semibold bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200 transition-colors">Anterior</a>
                                <?php else: ?>
                                    <button disabled class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-300 cursor-not-allowed">Anterior</button>
                                <?php endif; ?>
                                <span class="px-3 py-1 text-xs font-semibold text-slate-500">Página <?php echo $page; ?> de <?php echo $total_pages; ?></span>
                                <?php if ($page < $total_pages): ?>
                                    <a href="<?php echo htmlspecialchars(activity_log_query_string(['page' => $page + 1]), ENT_QUOTES, 'UTF-8'); ?>" class="px-3 py-1 rounded-lg border border-slate-100 text-xs font-semibold bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200 transition-colors">Siguiente</a>
                                <?php else: ?>
                                    <button disabled class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-300 cursor-not-allowed">Siguiente</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Drawer de detalle -->
<div id="log-detail-backdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity hidden"></div>
<div class="fixed inset-y-0 right-0 w-full sm:max-w-lg bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-full app-drawer" id="log-detail-drawer">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/10 flex items-center justify-center">
                <i data-lucide="file-clock" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="font-sans font-bold text-slate-900 text-sm">Detalle de la acción</h3>
                <span class="font-sans text-[10px] text-slate-400 block mt-0.5">Registro de auditoría</span>
            </div>
        </div>
        <button type="button" id="log-detail-close" class="rounded-lg border-0 bg-transparent p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-900">
            <i data-lucide="x" class="!h-5 !w-5"></i>
        </button>
    </div>
    <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4" id="log-detail-body">
        <!-- Poblado por JS -->
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>
<script>
(function () {
    'use strict';

    var backdrop = document.getElementById('log-detail-backdrop');
    var drawer = document.getElementById('log-detail-drawer');
    var body = document.getElementById('log-detail-body');
    var closeBtn = document.getElementById('log-detail-close');

    function fieldRow(label, value) {
        return '<div><span class="block text-[10px] font-mono font-bold uppercase text-slate-400 mb-1">' + label + '</span>' +
               '<span class="block text-sm text-slate-700 font-sans break-words">' + (value || '—') + '</span></div>';
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function openDrawer(data) {
        var html = '';
        html += fieldRow('Fecha', escapeHtml(data.fecha_Log));
        html += fieldRow('Usuario', escapeHtml(data.username_Log));
        html += fieldRow('Acción', escapeHtml(data.accion_Log));
        html += fieldRow('Entidad', escapeHtml(data.entidad_Log) + (data.entidad_id_Log ? ' #' + escapeHtml(data.entidad_id_Log) : ''));
        html += fieldRow('Descripción', escapeHtml(data.descripcion_Log));
        html += fieldRow('Pantalla', escapeHtml(data.pantalla_Log));
        html += fieldRow('Método', escapeHtml(data.metodo_Log));
        html += fieldRow('Resultado', escapeHtml(data.resultado_Log));
        html += fieldRow('IP', escapeHtml(data.ip_Log));
        html += fieldRow('User Agent', escapeHtml(data.user_agent_Log));

        var datosFormatted = '—';
        if (data.datos_Log) {
            try {
                datosFormatted = JSON.stringify(JSON.parse(data.datos_Log), null, 2);
            } catch (e) {
                datosFormatted = data.datos_Log;
            }
        }
        html += '<div><span class="block text-[10px] font-mono font-bold uppercase text-slate-400 mb-1">Datos (JSON)</span>' +
                '<pre class="text-xs font-mono bg-slate-50 border border-slate-100 rounded-xl p-4 overflow-x-auto whitespace-pre-wrap">' + escapeHtml(datosFormatted) + '</pre></div>';

        body.innerHTML = html;
        backdrop.classList.remove('hidden');
        drawer.classList.remove('translate-x-full');
    }

    function closeDrawer() {
        backdrop.classList.add('hidden');
        drawer.classList.add('translate-x-full');
    }

    document.querySelectorAll('.js-log-detail-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            try {
                var data = JSON.parse(btn.getAttribute('data-log-detail'));
                openDrawer(data);
            } catch (e) {
                // noop
            }
        });
    });

    if (backdrop) backdrop.addEventListener('click', closeDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
})();
</script>
