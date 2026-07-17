<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Invoice\ListInvoices;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;
use App\Application\Customer\ListCustomers;
use App\Infrastructure\Persistence\MysqliCustomerRepository;
use App\Application\Invoice\FindInvoice;

include('layouts/config.php');
include('layouts/helpers.php');

$facturas = (new ListInvoices(new MysqliInvoiceRepository($link)))->handle();

// --- Drawers: Nueva Factura, Editar Factura y Subir Excel (calco de dash-services.php) ---
$action = $_GET['action'] ?? '';
$drawerMode = in_array($action, ['new', 'edit', 'upload'], true) ? $action : null;
$drawerError = isset($_GET['err']) ? (string) $_GET['err'] : '';

// Errores del drawer de carga (mismos códigos que dash-invoices-upload.php)
$erroresCarga = [
    'sin_archivo' => 'No se recibió ningún archivo. Elegí un archivo .xlsx para continuar.',
    'formato_invalido' => 'El archivo no es un .xlsx válido. Descargá la plantilla y completala sin cambiar el formato.',
    'sin_filas' => 'El archivo no tiene ninguna fila de datos para cargar (solo el encabezado).',
];

$facturaEdit = null;
if ($drawerMode === 'edit') {
    $facturaEdit = isset($_GET['id_Factura']) && is_numeric($_GET['id_Factura'])
        ? (new FindInvoice(new MysqliInvoiceRepository($link)))->handle((int) $_GET['id_Factura'])
        : null;
    if ($facturaEdit === null) {
        $drawerMode = null;
    }
}

$clientesDrawer = [];
if ($drawerMode === 'new' || $drawerMode === 'edit') {
    $clientesDrawer = (new ListCustomers(new MysqliCustomerRepository($link)))->handle('rut', 'ASC')['items'];
}

$contratosEdit = [];
if ($drawerMode === 'edit') {
    $idClienteEdit = $facturaEdit->customerId;
    $stmtContratosEdit = mysqli_prepare($link, "SELECT id_Contrato, obra_Contrato FROM contratos WHERE id_Cliente = ? AND estado_Contrato = 2");
    mysqli_stmt_bind_param($stmtContratosEdit, "i", $idClienteEdit);
    mysqli_stmt_execute($stmtContratosEdit);
    $contratosEdit = mysqli_fetch_all(mysqli_stmt_get_result($stmtContratosEdit), MYSQLI_ASSOC);
    mysqli_stmt_close($stmtContratosEdit);
}

// --- Modal de previsualización de carga (se autoabre si hay datos en $_SESSION['carga_facturas']) ---
$modalCargaFilas = (isset($_SESSION['carga_facturas']) && is_array($_SESSION['carga_facturas']) && count($_SESSION['carga_facturas']) > 0) ? $_SESSION['carga_facturas'] : [];
$modalCargaMensajes = [
    'rut_no_encontrado' => 'RUT no encontrado en Clientes',
    'numero_factura_vacio' => 'Falta el número de factura',
    'fecha_invalida' => 'Fecha inválida',
    'monto_invalido' => 'Monto inválido',
];
$modalCargaValidas = count(array_filter($modalCargaFilas, fn($f) => $f['error'] === null));
$modalCargaError = count($modalCargaFilas) - $modalCargaValidas;

function baseQueryString(array $excludes = []): string {
    $params = [];
    foreach ($_GET as $k => $v) {
        if (!in_array($k, $excludes, true) && $v !== '' && $v !== null) {
            $params[] = $k . '=' . urlencode((string) $v);
        }
    }
    return $params ? '&' . implode('&', $params) : '';
}
$closeDrawerQs = baseQueryString(['action', 'err', 'id_Factura']);
$closeDrawerUrl = 'dash-invoices-list.php' . ($closeDrawerQs !== '' ? '?' . ltrim($closeDrawerQs, '&') : '');

$totalPagado = array_sum(array_map(fn($f) => (int) $f['valor_Factura'], array_filter($facturas, fn($f) => (int) $f['estado_Factura'] === 2)));
$totalPendiente = array_sum(array_map(fn($f) => (int) $f['valor_Factura'], array_filter($facturas, fn($f) => (int) $f['estado_Factura'] === 1)));
$countPagadas = count(array_filter($facturas, fn($f) => (int) $f['estado_Factura'] === 2));
$eficiencia = count($facturas) > 0 ? round($countPagadas / count($facturas) * 100) : 0;

$pills = ['todas' => 'Todas', 'pagadas' => 'Pagadas', 'pendientes' => 'Pendientes'];
$rawFilter = $_GET['filter'] ?? 'todas';
$filter = in_array($rawFilter, array_keys($pills), true) ? $rawFilter : 'todas';

$facturasFiltradas = array_values(array_filter($facturas, function ($row) use ($filter) {
    if ($filter === 'pagadas') return (int) $row['estado_Factura'] === 2;
    if ($filter === 'pendientes') return (int) $row['estado_Factura'] === 1;
    return true;
}));
?>

<head>
    <title>Facturas | Blanco Servicios - Admin & Dashboard</title>
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
                    <!-- KPIs financieros -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div class="p-5 rounded-3xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-800 flex items-center justify-between">
                            <div class="space-y-1">
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider block text-indigo-600">Recaudación Lograda</span>
                                <span class="font-sans font-extrabold text-xl block"><?php echo format_clp($totalPagado); ?></span>
                                <span class="text-[10px] text-slate-500 block">Ingresos depositados y confirmados</span>
                            </div>
                            <i data-lucide="trending-up" class="w-8 h-8 text-indigo-600/30"></i>
                        </div>
                        <div class="p-5 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-amber-800 flex items-center justify-between">
                            <div class="space-y-1">
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider block text-amber-600">Por Recaudar</span>
                                <span class="font-sans font-extrabold text-xl block"><?php echo format_clp($totalPendiente); ?></span>
                                <span class="text-[10px] text-slate-500 block">Compromisos de pago vigentes</span>
                            </div>
                            <i data-lucide="alert-triangle" class="w-8 h-8 text-amber-600/30"></i>
                        </div>
                        <div class="p-5 rounded-3xl bg-white border border-slate-100 shadow-sm flex items-center justify-between col-span-1 sm:col-span-2 lg:col-span-1">
                            <div class="space-y-1">
                                <span class="text-[10px] font-mono text-slate-400 font-bold uppercase block">Eficiencia de Cobro</span>
                                <span class="font-sans font-extrabold text-xl text-slate-800 block"><?php echo $eficiencia; ?>%</span>
                                <span class="text-[10px] text-indigo-600 block">✔ Cartera de clientes saludable</span>
                            </div>
                            <i data-lucide="check" class="w-8 h-8 text-indigo-600 bg-indigo-50 p-2 rounded-full"></i>
                        </div>
                    </div>

                    <!-- Toolbar: búsqueda + pills + date-range + acciones -->
                    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                            <div class="relative max-w-xs">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <input
                                    type="text"
                                    placeholder="N° factura, cliente u obra..."
                                    id="facturas-local-search"
                                    data-table-search-input="#facturas-table"
                                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors font-sans"
                                />
                            </div>

                            <div class="flex items-center bg-slate-100 p-1 rounded-xl gap-0.5">
                                <?php foreach ($pills as $key => $label): ?>
                                    <a
                                        href="?filter=<?php echo $key; ?>"
                                        class="px-3 py-1.5 text-xs font-semibold rounded-lg font-sans transition-all duration-200 <?php echo $filter === $key ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'; ?>"
                                    >
                                        <?php echo $label; ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 shrink-0">
                            <div class="dt-range-picker input-group datepicker-range" style="width: auto;">
                                <input type="text" class="dt-input flatpickr-input" data-input aria-describedby="date1">
                                <button class="dt-input-suffix" id="date1" data-toggle><i data-lucide="calendar" class="!h-4 !w-4"></i></button>
                            </div>
                            <a href="?action=upload" class="px-4 py-2 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 transition-all active:scale-95">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                <span>Subir Excel / CSV</span>
                            </a>
                            <a href="?action=new" class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 shadow-lg shadow-indigo-500/10 transition-all active:scale-95">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Agregar Nueva Factura</span>
                            </a>
                        </div>
                    </div>

                    <div id="facturas-table" data-table-native-wrap data-per-page="10" data-item-label="Facturas" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-50 bg-slate-50/50">
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Nro. Factura</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Fecha</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Cliente</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Obra</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Monto</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Estado</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase whitespace-nowrap">Fecha de Pago</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right whitespace-nowrap">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach ($facturasFiltradas as $row): ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors" data-search="<?php echo htmlspecialchars($row['numero_Factura'] . ' ' . $row['nombre_Cliente'] . ' ' . ($row['obra_Contrato'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="font-mono text-sm font-semibold text-slate-700">#<?php echo htmlspecialchars($row['numero_Factura'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700 whitespace-nowrap"><?php echo htmlspecialchars(format_fecha($row['fecha_Factura']), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['obra_Contrato'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-sans text-sm font-semibold text-slate-700 whitespace-nowrap"><?php echo format_clp($row['valor_Factura']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ((int) $row['estado_Factura'] === 1): ?>
                                                    <span class="badge-status is-warn">Pendiente</span>
                                                <?php elseif ((int) $row['estado_Factura'] === 2): ?>
                                                    <span class="badge-status is-success">Pagado</span>
                                                <?php else: ?>
                                                    <span class="badge-status is-danger">Anulado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ((int) $row['estado_Factura'] === 2 && !$row['fecha_Pago']): ?>
                                                    <form method="post" action="controller/invoice-fecha-pago.php" class="inline-flex items-center gap-1">
                                                        <input type="hidden" name="id_Factura" value="<?php echo (int) $row['id_Factura']; ?>">
                                                        <input
                                                            type="date"
                                                            name="fecha_Pago"
                                                            class="w-[90px] px-2 py-1.5 text-xs text-center font-sans rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-colors"
                                                            title="Registrar Fecha de Pago"
                                                            required
                                                        >
                                                        <button type="submit" class="dt-cell-action" title="Guardar fecha de pago">
                                                            <i data-lucide="check"></i>
                                                        </button>
                                                        <button type="reset" class="dt-cell-action" title="Cancelar">
                                                            <i data-lucide="x"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif ((int) $row['estado_Factura'] === 2 && $row['fecha_Pago']): ?>
                                                    <input
                                                        type="date"
                                                        value="<?php echo htmlspecialchars($row['fecha_Pago'], ENT_QUOTES, 'UTF-8'); ?>"
                                                        disabled
                                                        class="w-[90px] px-2 py-1.5 text-xs text-center font-sans rounded-lg border border-slate-200 bg-slate-50 text-slate-500 opacity-80 cursor-not-allowed"
                                                        title="La fecha de pago ya fue registrada y no se puede modificar"
                                                    >
                                                <?php else: ?>
                                                    <input
                                                        type="date"
                                                        value=""
                                                        disabled
                                                        class="w-[90px] px-2 py-1.5 text-xs text-center font-sans rounded-lg border border-slate-200 bg-slate-50 text-slate-400 opacity-60 cursor-not-allowed"
                                                        title="Solo se puede editar la fecha de pago de facturas pagadas"
                                                    >
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="inline-flex items-center justify-end gap-1">
                                                    <div class="dropdown">
                                                        <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                            <i data-lucide="more-horizontal"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end m-0 min-w-[220px] list-none overflow-hidden rounded-2xl border border-slate-100 bg-white p-2 shadow-xl shadow-slate-200/50">
                                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="?action=edit&id_Factura=<?php echo (int) $row['id_Factura']; ?>"><i data-lucide="square-pen" class="!h-[14px] !w-[14px] shrink-0"></i>Editar</a></li>
                                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="controller/invoice-pdf.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&id_Contrato=<?php echo (int) $row['id_Contrato']; ?>" data-glightbox-preview data-type="external" data-width="900px" data-height="90vh"><i data-lucide="printer" class="!h-[14px] !w-[14px] shrink-0"></i>Imprimir</a></li>
                                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="dash-invoices-detail.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&id_Contrato=<?php echo (int) $row['id_Contrato']; ?>"><i data-lucide="plus" class="!h-[14px] !w-[14px] shrink-0"></i>Agregar Servicios a la Factura</a></li>
                                                            <li>
                                                                <a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="controller/invoice-estado.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&estado_Factura=1">Pendiente de Pago</a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="controller/invoice-estado.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&estado_Factura=2">Pagado</a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="controller/invoice-estado.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&estado_Factura=3">Anular Pago</a>
                                                            </li>
                                                            <li><hr class="dropdown-divider m-1 border-slate-100"></li>
                                                            <li>
                                                                <a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-rose-500 hover:bg-rose-50 hover:text-rose-500" href="controller/invoice-delete.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>" data-confirm-delete data-confirm-title="¿Anular esta factura?" data-confirm-text="Los servicios asociados quedarán liberados para volver a facturarse." data-confirm-confirm-text="Sí, anular">
                                                                    <i data-lucide="trash-2" class="!h-[14px] !w-[14px] shrink-0"></i>Anular
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($facturasFiltradas) === 0): ?>
                                        <tr>
                                            <td colspan="8" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                                No se registran facturas que coincidan con los filtros.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between" data-table-native-pagination>
                            <span class="font-mono text-[10px] text-slate-400 font-bold uppercase" data-table-native-summary></span>
                            <div class="flex items-center space-x-1" data-table-native-pages></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($drawerMode === 'new'): ?>
    <!-- Drawer Nueva Factura (#invoice-drawer) -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='<?php echo $closeDrawerUrl; ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0 app-drawer" id="invoice-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/10 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm">Emitir Factura Manual</h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5">Crear un compromiso de pago directo.</span>
                </div>
            </div>
            <a href="<?php echo $closeDrawerUrl; ?>" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all" aria-label="Cerrar panel">
                <i data-lucide="x" class="w-5 h-5"></i>
            </a>
        </div>

        <?php if ($drawerError !== ''): ?>
            <div class="px-6 py-3 bg-rose-50 border-b border-rose-100 text-rose-700 font-sans text-xs">
                <?php echo htmlspecialchars($drawerError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="controller/invoice-new.php" class="flex-1 overflow-y-auto p-6 space-y-4" id="invoice-drawer-form">
            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Número de Factura <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="numero_Factura"
                    placeholder="e.g. 1901"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                    required
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Fecha de Factura <span class="text-rose-500">*</span></label>
                <input
                    type="date"
                    name="fecha_Factura"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                    required
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Cliente <span class="text-rose-500">*</span></label>
                <select
                    name="id_Cliente"
                    id="id_Cliente_invoice"
                    class="dt-select w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    data-enhanced-select
                    data-search-placeholder="Buscar cliente..."
                    required
                >
                    <option value="">Seleccione un cliente...</option>
                    <?php foreach ($clientesDrawer as $cliente): ?>
                        <option value="<?php echo (int) $cliente->id; ?>"><?php echo htmlspecialchars($cliente->name, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($cliente->rut, ENT_QUOTES, 'UTF-8'); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Obra / Contrato Asociado <span class="text-rose-500">*</span></label>
                <select
                    name="id_Contrato"
                    id="id_Contrato_invoice"
                    class="dt-select w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    data-enhanced-select
                    data-search-placeholder="Buscar contrato..."
                    required
                >
                    <option value="">Seleccione una obra / contrato...</option>
                    <!-- Opciones se cargan dinámicamente vía cascade (cliente -> contrato) -->
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Monto Neto Cobrado (CLP) <span class="text-rose-500">*</span></label>
                <input
                    type="number"
                    name="valor_Factura"
                    placeholder="e.g. 148750"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-sans"
                    required
                >
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <a
                    href="<?php echo $closeDrawerUrl; ?>"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans text-center transition-colors"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    name="crear"
                    class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold font-sans transition-all shadow-lg shadow-indigo-600/10"
                >
                    Emitir Factura
                </button>
            </div>
        </form>
    </div>
<?php elseif ($drawerMode === 'edit'): ?>
    <!-- Drawer Editar Factura (#invoice-edit-drawer) -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='<?php echo $closeDrawerUrl; ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0 app-drawer" id="invoice-edit-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/10 flex items-center justify-center">
                    <i data-lucide="square-pen" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm">Editar Factura</h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5">Modificar los datos de la factura #<?php echo htmlspecialchars($facturaEdit->number, ENT_QUOTES, 'UTF-8'); ?>.</span>
                </div>
            </div>
            <a href="<?php echo $closeDrawerUrl; ?>" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all" aria-label="Cerrar panel">
                <i data-lucide="x" class="w-5 h-5"></i>
            </a>
        </div>

        <?php if ($drawerError !== ''): ?>
            <div class="px-6 py-3 bg-rose-50 border-b border-rose-100 text-rose-700 font-sans text-xs">
                <?php echo htmlspecialchars($drawerError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="controller/invoice-update.php" class="flex-1 overflow-y-auto p-6 space-y-4" id="invoice-edit-drawer-form">
            <input type="hidden" name="id_Factura" value="<?php echo (int) $facturaEdit->id; ?>">

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Número de Factura <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="numero_Factura"
                    value="<?php echo htmlspecialchars($facturaEdit->number, ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                    required
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Fecha de Factura <span class="text-rose-500">*</span></label>
                <input
                    type="date"
                    name="fecha_Factura"
                    value="<?php echo htmlspecialchars($facturaEdit->date, ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-mono"
                    required
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Cliente <span class="text-rose-500">*</span></label>
                <select
                    name="id_Cliente"
                    id="id_Cliente_invoice_edit"
                    class="dt-select w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    data-enhanced-select
                    data-search-placeholder="Buscar cliente..."
                    required
                >
                    <option value="">Seleccione un cliente...</option>
                    <?php foreach ($clientesDrawer as $cliente): ?>
                        <option value="<?php echo (int) $cliente->id; ?>" <?php echo ((int) $cliente->id === $facturaEdit->customerId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cliente->name, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($cliente->rut, ENT_QUOTES, 'UTF-8'); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Obra / Contrato Asociado <span class="text-rose-500">*</span></label>
                <select
                    name="id_Contrato"
                    id="id_Contrato_invoice_edit"
                    class="dt-select w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    data-enhanced-select
                    data-search-placeholder="Buscar contrato..."
                    required
                >
                    <option value="">Seleccione una obra / contrato...</option>
                    <?php foreach ($contratosEdit as $contrato): ?>
                        <option value="<?php echo (int) $contrato['id_Contrato']; ?>" <?php echo ((int) $contrato['id_Contrato'] === $facturaEdit->contractId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($contrato['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Monto Neto Cobrado (CLP) <span class="text-rose-500">*</span></label>
                <input
                    type="number"
                    name="valor_Factura"
                    value="<?php echo (int) $facturaEdit->value; ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-sans"
                    required
                >
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <a
                    href="<?php echo $closeDrawerUrl; ?>"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans text-center transition-colors"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    name="editar"
                    class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold font-sans transition-all shadow-lg shadow-indigo-600/10"
                >
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
<?php elseif ($drawerMode === 'upload'): ?>
    <!-- Drawer Subir Excel / CSV (#invoice-upload-drawer) -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='<?php echo $closeDrawerUrl; ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0 app-drawer" id="invoice-upload-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/10 flex items-center justify-center">
                    <i data-lucide="upload" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm">Subir Excel / CSV</h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5">Carga masiva de facturas desde una plantilla .xlsx.</span>
                </div>
            </div>
            <a href="<?php echo $closeDrawerUrl; ?>" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all" aria-label="Cerrar panel">
                <i data-lucide="x" class="w-5 h-5"></i>
            </a>
        </div>

        <?php if ($drawerError !== '' && isset($erroresCarga[$drawerError])): ?>
            <div class="px-6 py-3 bg-rose-50 border-b border-rose-100 text-rose-700 font-sans text-xs">
                <?php echo htmlspecialchars($erroresCarga[$drawerError], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="controller/invoice-upload-parse.php" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-4" id="invoice-upload-drawer-form">

            <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4 space-y-2">
                <a href="assets/templates/plantilla-facturas.xlsx" download class="inline-flex items-center space-x-1.5 px-3 py-2 border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 rounded-xl font-sans text-xs font-semibold transition-all">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>Descargar plantilla modelo</span>
                </a>
                <p class="text-[11px] text-slate-500 font-sans leading-relaxed">
                    La plantilla tiene 4 columnas en este orden: <strong class="text-slate-700">RUT del Cliente</strong>,
                    <strong class="text-slate-700">Número de Factura</strong>, <strong class="text-slate-700">Fecha Factura</strong>,
                    <strong class="text-slate-700">Monto</strong>. No cambies el orden ni agregues columnas nuevas.
                </p>
            </div>

            <div class="space-y-1.5">
                <label for="archivo_facturas" class="font-sans text-xs font-bold text-slate-600 block">Archivo Excel (.xlsx) <span class="text-rose-500">*</span></label>
                <input
                    type="file"
                    id="archivo_facturas"
                    name="archivo_facturas"
                    accept=".xlsx"
                    required
                    class="block w-full text-xs font-sans text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                >
                <p class="text-[11px] text-slate-400 font-sans">Antes de guardar vas a poder revisar y corregir los datos en una previsualización.</p>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <a
                    href="<?php echo $closeDrawerUrl; ?>"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl text-xs font-semibold font-sans text-center transition-colors"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold font-sans transition-all shadow-lg shadow-indigo-600/10"
                >
                    Continuar
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php if (!empty($modalCargaFilas)): ?>
    <!-- MODAL PREVISUALIZAR CARGA DE FACTURAS (#modalPreviewCarga) -->
    <div class="modal fade show" id="modalPreviewCarga" tabindex="-1" aria-labelledby="modalPreviewCargaLabel" aria-modal="true" role="dialog" data-modal-autoshow>
        <div class="modal-dialog max-w-3xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPreviewCargaLabel">Previsualizar Carga de Facturas</h5>
                    <a href="controller/invoice-upload-cancel.php" class="btn-close" aria-label="Cerrar y cancelar carga"></a>
                </div>
                <form action="controller/invoice-upload-confirm.php" method="POST" id="form-confirmar">
                    <div class="modal-body">
                        <div class="dt-alert dt-alert-info mb-4">
                            <strong><?php echo (int) $modalCargaValidas; ?></strong> factura(s) lista(s) para cargar.
                            <?php if ($modalCargaError > 0): ?>
                                <strong class="text-rose-600"><?php echo (int) $modalCargaError; ?></strong> fila(s) con error no se van a cargar (marcadas en rojo).
                            <?php endif; ?>
                        </div>

                        <div class="table-card overflow-x-auto">
                            <table class="table align-middle mb-0 w-full">
                                <thead>
                                    <tr>
                                        <th>Nro Factura</th>
                                        <th>Fecha Factura</th>
                                        <th>Cliente Nombre</th>
                                        <th>Obra</th>
                                        <th class="text-end">Monto Factura</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modalCargaFilas as $indice => $fila): ?>
                                        <tr class="<?php echo $fila['error'] ? 'bg-rose-50' : ''; ?>">
                                            <td><?php echo htmlspecialchars($fila['numero_Factura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($fila['fecha_Factura'] ?? $fila['fecha_original'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <?php if ($fila['error']): ?>
                                                    <span class="text-rose-600"><?php echo htmlspecialchars($modalCargaMensajes[$fila['error']] ?? 'Error desconocido', ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php if ($fila['error'] === 'rut_no_encontrado'): ?>
                                                        <div class="text-xs text-slate-500">RUT en el archivo: <?php echo htmlspecialchars($fila['rut_original'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php echo htmlspecialchars($fila['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!$fila['error']): ?>
                                                    <?php if (count($fila['contratos']) === 0): ?>
                                                        <span class="text-rose-600">Sin obras activas para este cliente</span>
                                                    <?php else: ?>
                                                        <select name="obra[<?php echo (int) $indice; ?>]" class="dt-select select-obra" required>
                                                            <option value="">Seleccionar la obra</option>
                                                            <?php foreach ($fila['contratos'] as $contrato): ?>
                                                                <option value="<?php echo (int) $contrato['id_Contrato']; ?>"><?php echo htmlspecialchars($contrato['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    —
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end font-mono text-sm"><?php echo is_numeric($fila['valor_Factura']) ? format_clp($fila['valor_Factura']) : htmlspecialchars($fila['valor_Factura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="controller/invoice-upload-cancel.php" class="dt-btn-secondary">Cancelar</a>
                        <button type="submit" class="dt-btn-add" id="btn-confirmar" disabled>Confirmar Carga</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.body.classList.add('modal-open');
        (function () {
            function actualizarBotonConfirmar() {
                var selects = document.querySelectorAll('.select-obra');
                var todasCompletas = true;
                selects.forEach(function (select) {
                    if (select.value === '') {
                        todasCompletas = false;
                    }
                });
                var btn = document.getElementById('btn-confirmar');
                if (btn) {
                    btn.disabled = !todasCompletas || selects.length === 0;
                }
            }
            document.querySelectorAll('.select-obra').forEach(function (select) {
                select.addEventListener('change', actualizarBotonConfirmar);
            });
            actualizarBotonConfirmar();
        })();
    </script>
<?php endif; ?>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>
<script src="assets/js/components/native-table.js"></script>

<?php if ($drawerMode === 'new'): ?>
    <script>
        $(document).ready(function () {
            SelectEnhanced.cascade({
                parent: 'id_Cliente_invoice',
                child: 'id_Contrato_invoice',
                endpoint: 'controller/obtener_contratos.php',
                paramName: 'idCliente',
            });
        });
    </script>
<?php elseif ($drawerMode === 'edit'): ?>
    <script>
        $(document).ready(function () {
            SelectEnhanced.cascade({
                parent: 'id_Cliente_invoice_edit',
                child: 'id_Contrato_invoice_edit',
                endpoint: 'controller/obtener_contratos.php',
                paramName: 'idCliente',
            });
        });
    </script>
<?php endif; ?>

</body>
</html>
