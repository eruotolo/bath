<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\ListServices;
use App\Application\Service\FindServiceWithContractAndCustomer;
use App\Application\Service\ListAssignedBathrooms;
use App\Application\Bathroom\ListBathroomsByContract;
use App\Application\Customer\ListCustomers;
use App\Infrastructure\Persistence\MysqliServiceRepository;
use App\Infrastructure\Persistence\MysqliCustomerRepository;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

include('layouts/config.php');

$servicios = (new ListServices(new MysqliServiceRepository($link)))->handle();

// --- Filtro por pill (server-side): Todos / Facturados / No Facturados ---
$allowedFilters = ['todos', 'facturados', 'no-facturados'];
$rawFilter = $_GET['filter'] ?? 'todos';
$filter = in_array($rawFilter, $allowedFilters, true) ? $rawFilter : 'todos';

$serviciosFiltrados = [];
foreach ($servicios as $row) {
    $facturado = (int) ($row['facturado'] ?? 0);
    if ($filter === 'facturados') {
        $keep = $facturado === 1;
    } elseif ($filter === 'no-facturados') {
        $keep = $facturado === 0;
    } else {
        $keep = true;
    }
    if ($keep) {
        $serviciosFiltrados[] = $row;
    }
}
$servicios = $serviciosFiltrados;

$pills = [
    'todos' => 'Todos',
    'facturados' => 'Facturados',
    'no-facturados' => 'No Facturados',
];

// --- Labels visibles de tipo_servicio a partir de los 9 flags ---
function service_types_labels(array $row): array {
    $map = [
        'instalacion_Tipo' => 'Instalación',
        'reparacion_Tipo' => 'Reparación',
        'limpieza_Tipo' => 'Limpieza',
        'desinfeccion_Tipo' => 'Desinfección',
        'sanitizacion_Tipo' => 'Sanitización',
        'higienico_Tipo' => 'Entrega Papel Higiénico',
        'jabon_Tipo' => 'Entrega de Jabón Líquido',
        'otros_Tipo' => 'Otros',
        'retiro_Tipo' => 'Retiro de Baños',
    ];
    $labels = [];
    foreach ($map as $column => $label) {
        if (!empty($row[$column])) {
            $labels[] = $label;
        }
    }
    return $labels;
}

// --- Drawer Nuevo/Editar Servicio (calco de dash-contracts.php:49-93) ---
$drawerAction = $_GET['action'] ?? '';
$drawerMode = in_array($drawerAction, ['new', 'edit'], true) && ($drawerAction === 'new' || isset($_GET['id_Servicio']))
    ? $drawerAction
    : null;
$drawerError = isset($_GET['err']) ? (string) $_GET['err'] : '';
$isNew = $drawerMode === 'new';
$isEdit = $drawerMode === 'edit';

$clientesDrawer = [];
if ($isNew) {
    $clientesDrawer = (new ListCustomers(new MysqliCustomerRepository($link)))->handle('rut', 'ASC')['items'];
}

$editService = null;
$bathsDelContrato = [];
$bathsAsignadosIds = [];
if ($isEdit && isset($_GET['id_Servicio']) && ctype_digit((string) $_GET['id_Servicio'])) {
    $idSvc = (int) $_GET['id_Servicio'];
    $editService = (new FindServiceWithContractAndCustomer(new MysqliServiceRepository($link)))->handle($idSvc);
    if ($editService !== null) {
        $bathsDelContrato = (new ListBathroomsByContract(new MysqliBathroomRepository($link)))->handle((int) $editService['id_Contrato']);
        foreach ((new ListAssignedBathrooms(new MysqliServiceRepository($link)))->handle($idSvc) as $ba) {
            $bathsAsignadosIds[(int) $ba['id_Bath']] = true;
        }
    }
}

$closeDrawerQs = baseQueryString(['action', 'err', 'id_Servicio']);
$closeDrawerUrl = 'dash-services.php' . ($closeDrawerQs !== '' ? '?' . ltrim($closeDrawerQs, '&') : '');

function baseQueryString(array $excludes = ['page']): string {
    $params = [];
    foreach ($_GET as $k => $v) {
        if (!in_array($k, $excludes, true) && $v !== '' && $v !== null) {
            $params[] = $k . '=' . urlencode((string) $v);
        }
    }
    return $params ? '&' . implode('&', $params) : '';
}
?>

<head>
    <title>Listado de Servicios | Seguimientos | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <style>
        /* Anti-flash: ambas vistas ocultas por defecto; solo la activa (data-services-view)
           se muestra. El dataset lo setea el <script> de abajo pre-body, asi el primer paint
           ya tiene la vista correcta (sin un frame de la otra vista). Fallback: grid si no hay JS.

           IMPORTANTE — @layer utilities: estas reglas DEBEN vivir dentro del layer
           `utilities` de Tailwind. El `important` flag del proyecto emite las utilities
           (.grid, .hidden, .flex que llevan los contenedores) con !important dentro de
           @layer utilities. Regla del cascade CSS: entre declaraciones !important, las que
           estan en un layer le ganan a las unlayered, sin importar especificidad. Si este
           bloque queda sin layer, `.grid`/`.hidden`/`.flex` (layered) le ganan y en el primer
           paint SIEMPRE se ve el grid ~1s hasta que services.js corrige con estilo inline.
           Dentro del mismo layer gana la especificidad: estos selectores (ID + atributo)
           superan a las utilities de una sola clase. NO sacar el @layer. */
        @layer utilities {
            #servicios-grid, #servicios-table, #servicios-cards-pagination { display: none !important; }
            html[data-services-view="grid"] #servicios-grid { display: grid !important; }
            html[data-services-view="grid"] #servicios-cards-pagination { display: flex !important; }
            html[data-services-view="table"] #servicios-table { display: block !important; }
            html:not([data-services-view]) #servicios-grid { display: grid !important; }
            html:not([data-services-view]) #servicios-cards-pagination { display: flex !important; }

            /* Anti-flash del toggle grid/tabla: el markup PHP marca "grid" activo por
               defecto (no conoce el localStorage). Sin esto, en cada recarga (ej. al
               cambiar de pill Facturados/No Facturados) el boton de card se ve activo
               un frame antes de que services.js corrija el estado. */
            html[data-services-view="table"] [data-view-toggle="grid"] {
                background-color: transparent !important;
                color: #64748b !important;
                box-shadow: none !important;
            }
            html[data-services-view="table"] [data-view-toggle="table"] {
                background-color: #fff !important;
                color: #0f172a !important;
                box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important;
            }
        }
    </style>
    <script>
        try {
            var v = window.localStorage.getItem('services-view');
            document.documentElement.dataset.servicesView = (v === 'table') ? 'table' : 'grid';
        } catch (error) {
            document.documentElement.dataset.servicesView = 'grid';
        }
    </script>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid px-10 py-10 bg-slate-50/50">

                <div class="space-y-4">
                    <!-- Toolbar: search + pills + toggle grid/tabla + Agregar Nuevo Servicio -->
                    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                            <div class="relative max-w-xs">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <input
                                    type="text"
                                    placeholder="Cliente, obra o número..."
                                    id="servicios-local-search"
                                    data-table-search-input="#servicios-table"
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
                            <div class="bg-slate-100 p-1 rounded-xl flex items-center space-x-0.5">
                                <button
                                    type="button"
                                    data-view-toggle="grid"
                                    aria-controls="servicios-grid servicios-table"
                                    title="Vista de Cards"
                                    class="p-1.5 rounded-lg transition-all bg-white text-slate-900 shadow-sm"
                                >
                                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                                </button>
                                <button
                                    type="button"
                                    data-view-toggle="table"
                                    aria-controls="servicios-grid servicios-table"
                                    title="Vista de Tabla"
                                    class="p-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-800"
                                >
                                    <i data-lucide="list" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <a
                                href="?action=new"
                                class="px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 shadow-lg shadow-indigo-500/10 transition-all active:scale-95"
                            >
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Agregar Nuevo Servicio</span>
                            </a>
                        </div>
                    </div>

                    <!-- Grid de cards (#servicios-grid) -->
                    <div id="servicios-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <?php foreach ($servicios as $row):
                            $tipoLabels = service_types_labels($row);
                            $nro = (int) $row['nro_Servicio'];
                            $obra = htmlspecialchars((string) ($row['obra_Contrato'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $cliente = htmlspecialchars((string) ($row['nombre_Cliente'] ?? ''), ENT_QUOTES, 'UTF-8');
                            $fechaRaw = (string) ($row['fecha_Servicio'] ?? '');
                            $fechaFmt = $fechaRaw !== '' ? date('d-m-Y', strtotime($fechaRaw)) : '—';
                            $obs = isset($row['observaciones_Servicio']) && $row['observaciones_Servicio'] !== null && $row['observaciones_Servicio'] !== ''
                                ? htmlspecialchars((string) $row['observaciones_Servicio'], ENT_QUOTES, 'UTF-8')
                                : 'Sin observaciones especiales.';
                            $facturadoFlag = (int) ($row['facturado'] ?? 0);
                            $numeroFactura = isset($row['numero_Factura']) && $row['numero_Factura'] !== null
                                ? htmlspecialchars((string) $row['numero_Factura'], ENT_QUOTES, 'UTF-8')
                                : '';
                            $searchTipos = htmlspecialchars(implode(' ', $tipoLabels), ENT_QUOTES, 'UTF-8');
                        ?>
                            <article
                                class="servicio-card p-5 bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-md transition-all duration-300 relative overflow-hidden"
                                data-search-cliente="<?php echo $cliente; ?>"
                                data-search-obra="<?php echo $obra; ?>"
                                data-search-nro="#<?php echo $nro; ?>"
                                data-search-tipos="<?php echo $searchTipos; ?>"
                            >
                                <!-- Badge: estado de facturación (único dato variable de la card) -->
                                <?php if ($facturadoFlag === 0): ?>
                                    <div class="absolute top-4 right-4 flex items-center space-x-1.5 bg-amber-50 border border-amber-100 px-2.5 py-1 rounded-xl text-amber-700 font-medium font-sans text-[10px] uppercase">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                        <span>Pendiente de Cobro</span>
                                    </div>
                                <?php else: ?>
                                    <div class="absolute top-4 right-4 flex items-center space-x-1.5 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-xl text-emerald-700 font-medium font-sans text-[10px] uppercase">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>Facturado #<?php echo $numeroFactura; ?></span>
                                    </div>
                                <?php endif; ?>

                                <!-- Cabecera: código + obra + cliente -->
                                <div class="space-y-0.5">
                                    <span class="font-mono text-[10px] text-slate-400 block font-semibold">CÓDIGO VISITA: <a href="?action=edit&id_Servicio=<?php echo (int) $row['id_Servicio']; ?>" class="hover:text-indigo-600 transition-colors">#<?php echo $nro; ?></a></span>
                                    <h3 class="font-sans font-bold text-slate-900 text-base truncate pr-20"><?php echo $obra; ?></h3>
                                    <p class="font-sans text-xs text-slate-500 truncate max-w-xs"><?php echo $cliente; ?></p>
                                </div>

                                <!-- Fecha de visita -->
                                <div class="flex items-center space-x-1.5 font-mono text-[11px] text-slate-400 mt-2.5">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <span>Fecha visita: <?php echo htmlspecialchars($fechaFmt, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>

                                <!-- Tags de tipos de servicio realizados -->
                                <?php if (count($tipoLabels) > 0): ?>
                                    <div class="flex flex-wrap gap-1.5 mt-4">
                                        <?php foreach ($tipoLabels as $label): ?>
                                            <span class="px-2.5 py-1 bg-slate-50 border border-slate-100 text-slate-600 rounded-lg text-[10px] font-semibold font-sans uppercase tracking-wider">
                                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Bitácora -->
                                <div class="mt-4 p-3 bg-slate-50/50 rounded-2xl border border-slate-50 text-[11px] font-sans text-slate-500 leading-normal italic">
                                    <strong>Bitácora:</strong> &ldquo;<?php echo $obs; ?>&rdquo;
                                </div>

                                <!-- Footer: acción de impresión (misma acción que la vista de lista) -->
                                <div class="mt-4 pt-3 border-t border-slate-50 flex items-center justify-end">
                                    <a href="dash-services-print.php?id_Servicio=<?php echo (int) $row['id_Servicio']; ?>" class="flex items-center gap-1.5 text-[11px] font-sans font-bold text-indigo-600 hover:text-indigo-700 transition-colors">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Imprimir
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>

                        <?php if (count($servicios) === 0): ?>
                            <div class="col-span-full p-10 bg-white rounded-3xl border border-dashed border-slate-200 text-center text-slate-400 font-sans text-sm">
                                No se registran visitas que coincidan con los filtros.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer de paginacion de cards (calco visual de native-table.php:116-119).
                         Como el grid no tiene card contenedor, es su propia superficie rounded-3xl.
                         La visibilidad la controla el CSS anti-flash del <head> (solo vista grid). -->
                    <div
                        id="servicios-cards-pagination"
                        class="bg-white rounded-3xl border border-slate-100 shadow-sm px-6 py-4 flex items-center justify-between"
                        data-cards-pagination
                        data-per-page="9"
                        data-item-label="Servicios"
                    >
                        <span class="font-mono text-[10px] text-slate-400 font-bold uppercase" data-cards-summary></span>
                        <div class="flex items-center space-x-1" data-cards-pages></div>
                    </div>

                    <!-- Tabla (#servicios-table) — oculta por defecto; la visibilidad la controla services.js (Fase 5) -->
                    <div id="servicios-table" data-table-native-wrap data-per-page="9" data-item-label="Servicios" class="hidden bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-50 bg-slate-50/50">
                                        <th class="w-[13%] px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Número de Servicio</th>
                                        <th class="w-[21%] px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Cliente</th>
                                        <th class="w-[20%] px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Obra</th>
                                        <th class="w-[17%] px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Factura</th>
                                        <th class="w-[15%] px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Fecha</th>
                                        <th class="w-[14%] px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach ($servicios as $row):
                                        $tipoLabels = service_types_labels($row);
                                        $nro = (int) $row['nro_Servicio'];
                                        $obra = htmlspecialchars((string) ($row['obra_Contrato'] ?? ''), ENT_QUOTES, 'UTF-8');
                                        $cliente = htmlspecialchars((string) ($row['nombre_Cliente'] ?? ''), ENT_QUOTES, 'UTF-8');
                                        $fechaRaw = (string) ($row['fecha_Servicio'] ?? '');
                                        $fechaFmt = $fechaRaw !== '' ? date('d-m-Y', strtotime($fechaRaw)) : '—';
                                        $facturadoFlag = (int) ($row['facturado'] ?? 0);
                                        $searchTipos = htmlspecialchars(implode(' ', $tipoLabels), ENT_QUOTES, 'UTF-8');
                                    ?>
                                        <tr
                                            class="servicio-row hover:bg-slate-50/50 transition-colors group"
                                            data-search="<?php echo htmlspecialchars($cliente . ' ' . $obra . ' #' . $nro . ' ' . $searchTipos, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-search-cliente="<?php echo $cliente; ?>"
                                            data-search-obra="<?php echo $obra; ?>"
                                            data-search-nro="#<?php echo $nro; ?>"
                                            data-search-tipos="<?php echo $searchTipos; ?>"
                                        >
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="?action=edit&id_Servicio=<?php echo (int) $row['id_Servicio']; ?>" class="font-mono font-extrabold text-slate-800 text-sm hover:text-indigo-600 transition-colors">
                                                    #<?php echo $nro; ?>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700"><?php echo $cliente; ?></td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700"><?php echo $obra; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php if ($facturadoFlag === 0): ?>
                                                    <span class="badge-status is-warn">No Facturado</span>
                                                <?php else: ?>
                                                    <span class="badge-status is-success">Facturado</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 font-mono text-xs text-slate-500 whitespace-nowrap"><?php echo htmlspecialchars($fechaFmt, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="inline-flex items-center justify-end gap-1">
                                                    <a href="?action=edit&id_Servicio=<?php echo (int) $row['id_Servicio']; ?>" class="dt-cell-action" title="Editar">
                                                        <i data-lucide="square-pen"></i>
                                                    </a>
                                                    <div class="dropdown">
                                                        <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                            <i data-lucide="more-horizontal"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end m-0 min-w-[220px] list-none overflow-hidden rounded-2xl border border-slate-100 bg-white p-2 shadow-xl shadow-slate-200/50">
                                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="dash-services-print.php?id_Servicio=<?php echo (int) $row['id_Servicio']; ?>"><i data-lucide="printer" class="!h-[14px] !w-[14px] shrink-0"></i>Imprimir</a></li>
                                                            <li><hr class="dropdown-divider m-1 border-slate-100"></li>
                                                            <li>
                                                                <a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-rose-500 hover:bg-rose-50 hover:text-rose-500" href="controller/service-inactive.php?id_Servicio=<?php echo (int) $row['id_Servicio']; ?>" data-confirm-delete data-confirm-title="¿Eliminar este servicio?" data-confirm-text="Esta acción no se puede deshacer.">
                                                                    <i data-lucide="trash-2" class="!h-[14px] !w-[14px] shrink-0"></i>Eliminar
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if (count($servicios) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                                No se registran visitas que coincidan con los filtros.
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

<?php if ($drawerMode !== null): ?>
    <!-- Drawer Nuevo/Editar Servicio (#service-drawer) -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='<?php echo $closeDrawerUrl; ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0 app-drawer" id="service-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl <?php echo $isEdit ? 'bg-indigo-100 text-indigo-700' : 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'; ?> flex items-center justify-center">
                    <i data-lucide="<?php echo $isEdit ? 'square-pen' : 'briefcase'; ?>" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm">
                        <?php if ($isEdit): ?>
                            Editar Servicio #<?php echo (int) ($editService['nro_Servicio'] ?? 0); ?>
                        <?php else: ?>
                            Registrar Nuevo Servicio
                        <?php endif; ?>
                    </h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5">
                        <?php echo $isEdit ? 'Modificar visita y baños asignados de la obra.' : 'Registrar visita y baños asignados de la obra.'; ?>
                    </span>
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

        <form method="post" action="<?php echo $isEdit ? 'controller/service-update.php' : 'controller/service-new.php'; ?>" class="flex-1 overflow-y-auto p-6 space-y-4" id="service-drawer-form">
            <?php if ($isEdit && $editService !== null): ?>
                <input type="hidden" name="id_Servicio" value="<?php echo (int) $editService['id_Servicio']; ?>">
                <input type="hidden" name="nro_Servicio" value="<?php echo (int) $editService['nro_Servicio']; ?>">
                <input type="hidden" name="id_Contrato" value="<?php echo (int) $editService['id_Contrato']; ?>">
            <?php endif; ?>

            <?php if ($isNew): ?>
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Cliente <span class="text-rose-500">*</span></label>
                    <select
                        name="id_Cliente"
                        id="id_Cliente_service"
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
                    <label class="font-sans text-xs font-bold text-slate-600 block">Contrato / Obra <span class="text-rose-500">*</span></label>
                    <select
                        name="id_Contrato"
                        id="id_Contrato_service"
                        class="dt-select w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                        data-enhanced-select
                        data-search-placeholder="Buscar contrato..."
                        required
                    >
                        <option value="">Seleccione un Contrato / Obra...</option>
                        <!-- El resto de las opciones se cargan dinámicamente vía cascade (cliente -> contrato) -->
                    </select>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="font-sans text-xs font-bold text-slate-600 block">Cliente</label>
                        <input
                            type="text"
                            value="<?php echo htmlspecialchars((string) ($editService['nombre_Cliente'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            readonly
                            class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-sans"
                        >
                    </div>
                    <div class="space-y-1.5">
                        <label class="font-sans text-xs font-bold text-slate-600 block">Obra</label>
                        <input
                            type="text"
                            value="<?php echo htmlspecialchars((string) ($editService['obra_Contrato'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                            readonly
                            class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-sans"
                        >
                    </div>
                </div>
            <?php endif; ?>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Baños del Contrato</label>
                <div id="service-bath-checklist" class="p-3 border border-slate-200 rounded-xl max-h-48 overflow-y-auto space-y-2 bg-slate-50/50">
                    <?php if ($isEdit): ?>
                        <?php if (empty($bathsDelContrato)): ?>
                            <p class="text-[11px] text-slate-400 font-sans italic py-1">No hay baños asociados a este contrato.</p>
                        <?php else: ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="checkTodos" class="!h-4 !w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="font-sans text-xs font-bold uppercase tracking-wider text-slate-700">TODOS LOS BAÑOS</span>
                            </label>
                            <?php foreach ($bathsDelContrato as $bano): ?>
                                <label class="flex items-center gap-2 cursor-pointer" for="bath_<?php echo (int) $bano['id_Bath']; ?>">
                                    <input
                                        type="checkbox"
                                        name="id_Bath[]"
                                        value="<?php echo (int) $bano['id_Bath']; ?>"
                                        id="bath_<?php echo (int) $bano['id_Bath']; ?>"
                                        <?php echo isset($bathsAsignadosIds[(int) $bano['id_Bath']]) ? 'checked' : ''; ?>
                                        class="!h-4 !w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <span class="font-mono text-sm text-slate-700"><?php echo htmlspecialchars((string) $bano['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-[11px] text-slate-400 font-sans italic py-1">Seleccione un contrato para ver sus baños.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Tipo de Servicios</label>
                <div class="grid grid-cols-2 gap-2.5">
                    <?php
                    $tipos = [
                        'instalacion_Tipo' => 'Instalación',
                        'reparacion_Tipo' => 'Reparación',
                        'limpieza_Tipo' => 'Limpieza',
                        'desinfeccion_Tipo' => 'Desinfección',
                        'sanitizacion_Tipo' => 'Sanitización',
                        'higienico_Tipo' => 'Entrega Papel Higiénico',
                        'jabon_Tipo' => 'Entrega de Jabón Líquido',
                        'otros_Tipo' => 'Otros',
                        'retiro_Tipo' => 'Retiro de Baños',
                    ];
                    foreach ($tipos as $name => $labelTipo):
                        $checked = $isEdit && isset($editService[$name]) && (int) $editService[$name] === 1;
                    ?>
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50 hover:border-indigo-300 transition-colors">
                            <input type="checkbox" value="1" id="<?php echo $name; ?>" name="<?php echo $name; ?>" <?php echo $checked ? 'checked' : ''; ?> class="!h-4 !w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="font-sans text-xs text-slate-700"><?php echo htmlspecialchars($labelTipo, ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Fecha del Servicio <span class="text-rose-500">*</span></label>
                <input
                    type="date"
                    name="fecha_Servicio"
                    value="<?php echo $isEdit && isset($editService['fecha_Servicio']) ? htmlspecialchars((string) $editService['fecha_Servicio'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-mono"
                    required
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Valor del Servicio (CLP)</label>
                <input
                    type="number"
                    name="valor_Servicio"
                    value="<?php echo $isEdit ? (int) ($editService['valor_Servicio'] ?? 0) : ''; ?>"
                    placeholder="e.g. 12345"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all font-sans"
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Observaciones</label>
                <textarea
                    name="observaciones_Servicio"
                    rows="3"
                    placeholder="Bitácora de la visita..."
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                ><?php echo $isEdit ? htmlspecialchars((string) ($editService['observaciones_Servicio'] ?? ''), ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center space-x-3">
                <a
                    href="<?php echo $closeDrawerUrl; ?>"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    name="<?php echo $isEdit ? 'update' : 'crear'; ?>"
                    class="flex-1 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-xs font-semibold font-sans transition-all shadow-lg shadow-indigo-500/10"
                >
                    <?php echo $isEdit ? 'Guardar Cambios' : 'Crear Servicio'; ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>
<script src="assets/js/components/services.js"></script>
<script src="assets/js/components/native-table.js"></script>

<?php if ($drawerMode !== null): ?>
    <script>
        // Animación del drawer (calco de dash-contracts.php:575-593): translateX + Escape cierra
        (function () {
            var drawer = document.querySelector('.app-drawer');
            if (!drawer) return;
            drawer.style.transform = 'translateX(100%)';
            requestAnimationFrame(function () {
                drawer.style.transition = 'transform 300ms cubic-bezier(0.22, 1, 0.36, 1)';
                drawer.style.transform = 'translateX(0)';
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    window.location = '<?php echo $closeDrawerUrl; ?>';
                }
            });
        })();
    </script>
<?php endif; ?>

<?php if ($drawerMode === 'new'): ?>
    <script>
        // Cascade cliente -> contrato (calco de dash-services-add.php:114-122) +
        // fetch del checklist de baños al cambiar el contrato (endpoint Fase 1)
        document.addEventListener('DOMContentLoaded', function () {
            var clienteSelectEl = document.getElementById('id_Cliente_service');
            if (clienteSelectEl && window.SelectEnhanced) {
                clienteSelectEl.addEventListener('change', function () {
                    var idCliente = clienteSelectEl.value;
                    if (!idCliente) {
                        SelectEnhanced.setOptionsFromHtml('id_Contrato_service', '<option value="">Seleccione un Contrato / Obra...</option>');
                        return;
                    }
                    var formData = new FormData();
                    formData.append('idCliente', idCliente);
                    fetch('controller/obtener_contratos.php', { method: 'POST', body: formData })
                        .then(function (res) { return res.text(); })
                        .then(function (html) {
                            // Placeholder explícito: sin esto, Choices.js preselecciona el primer
                            // contrato de la lista en silencio (sin disparar 'change'), y si el
                            // usuario elige justo esa primera opción, el listener de más abajo
                            // (carga del checklist de baños) nunca llega a ejecutarse.
                            var placeholderOption = '<option value="">Seleccione un Contrato / Obra...</option>';
                            SelectEnhanced.setOptionsFromHtml('id_Contrato_service', placeholderOption + html);
                        });
                });
            }

            var checklist = document.getElementById('service-bath-checklist');
            var placeholder = '<p class="text-[11px] text-slate-400 font-sans italic py-1">Seleccione un contrato para ver sus baños.</p>';

            // Reata el listener al checkTodos (idempotente vía data-bound).
            // La función global wireCheckTodos() vive en el bloque compartido más abajo
            // (se ejecuta en ambos modos new y edit).
            var contratoSelect = document.getElementById('id_Contrato_service');
            if (contratoSelect && checklist) {
                contratoSelect.addEventListener('change', function () {
                    var idContrato = contratoSelect.value;
                    if (!idContrato) {
                        checklist.innerHTML = placeholder;
                        return;
                    }
                    var formData = new FormData();
                    formData.append('id_Contrato', idContrato);
                    fetch('controller/obtener_banos_contrato.php', { method: 'POST', body: formData })
                        .then(function (res) { return res.text(); })
                        .then(function (html) {
                            checklist.innerHTML = html || '<p class="text-[11px] text-slate-400 font-sans italic py-1">No hay baños asociados a este contrato.</p>';
                            if (typeof wireCheckTodos === 'function') wireCheckTodos();
                        });
                });
            }

            // Al cambiar el cliente el contrato se resetea: limpiar el checklist también
            var clienteSelect = document.getElementById('id_Cliente_service');
            if (clienteSelect && checklist) {
                clienteSelect.addEventListener('change', function () {
                    checklist.innerHTML = placeholder;
                });
            }
        });
    </script>
<?php endif; ?>

<?php if ($drawerMode !== null): ?>
    <script>
        // wireCheckTodos() compartido: enlaza el listener del checkbox "TODOS LOS BAÑOS".
        // Se ejecuta al cargar (caso edit: ya renderizado server-side) y desde el bloque new
        // tras el fetch que repobla el checklist. Idempotente vía data-bound.
        function wireCheckTodos() {
            var checkTodos = document.getElementById('checkTodos');
            if (!checkTodos || checkTodos.dataset.bound === 'true') return;
            checkTodos.dataset.bound = 'true';
            checkTodos.addEventListener('change', function () {
                document.querySelectorAll('#service-bath-checklist input[name="id_Bath[]"]').forEach(function (cb) {
                    cb.checked = checkTodos.checked;
                });
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', wireCheckTodos);
        } else {
            wireCheckTodos();
        }
    </script>
<?php endif; ?>

<?php if (($_GET['flash'] ?? '') !== ''): ?>
    <script>
        // Toast flash (calco de dash-contracts.php:601-625) — feedback tras crear/editar
        (function () {
            if (typeof Swal === 'undefined') return;
            var flash = <?php echo json_encode((string) $_GET['flash']); ?>;
            var msg = <?php echo json_encode((string) ($_GET['msg'] ?? '')); ?>;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: flash === 'success' ? 'success' : 'error',
                title: msg,
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                customClass: { popup: 'font-sans text-sm' }
            });
            if (window.history && window.history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.delete('flash');
                url.searchParams.delete('msg');
                window.history.replaceState({}, document.title, url.pathname + url.search);
            }
        })();
    </script>
<?php endif; ?>

</body>
</html>
