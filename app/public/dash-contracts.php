<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Contract\ListContracts;
use App\Application\Contract\FindContract;
use App\Application\Contract\FindContractWithCustomer;
use App\Application\Customer\ListCustomers;
use App\Application\Bathroom\ListAvailableBathrooms;
use App\Application\Bathroom\ListBathroomsByContract;
use App\Infrastructure\Persistence\MysqliContractRepository;
use App\Infrastructure\Persistence\MysqliCustomerRepository;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

include('layouts/config.php');
include('layouts/native-table.php');

$estado_Contrato_filtro = isset($_GET['estado']) ? (int) $_GET['estado'] : null;

$allowedSortBy = ['cliente', 'obra', 'estado'];
$allowedSortDir = ['ASC', 'DESC'];
$sortBy = in_array($_GET['sort'] ?? '', $allowedSortBy, true) ? $_GET['sort'] : null;
$sortDir = in_array($_GET['dir'] ?? '', $allowedSortDir, true) ? $_GET['dir'] : 'ASC';

$contractRepository = new MysqliContractRepository($link);
$useCase = new ListContracts($contractRepository);
$listado = $useCase->handle(
    in_array($estado_Contrato_filtro, [1, 2], true) ? $estado_Contrato_filtro : null,
    $sortBy ?? 'created_at',
    $sortBy !== null ? $sortDir : 'DESC'
);

$contratos_totales = $contractRepository->countStateTotals();
$total_contratos_todos = $contratos_totales['todos'];
$total_contratos_activos = $contratos_totales['activos'];
$total_contratos_terminados = $contratos_totales['terminados'];

$titulo_listado = 'Contratos';
if ($estado_Contrato_filtro === 2) {
    $titulo_listado = 'Contratos Activos';
} elseif ($estado_Contrato_filtro === 1) {
    $titulo_listado = 'Contratos Terminados';
}

$drawerAction = $_GET['action'] ?? '';
$drawerMode = in_array($drawerAction, ['new', 'edit', 'view', 'manage'], true) && ($drawerAction === 'new' || isset($_GET['id_Contrato']))
    ? $drawerAction
    : null;
$drawerError = isset($_GET['err']) ? (string) $_GET['err'] : '';
$clientesDrawer = [];
$banosDisponibles = [];
$contratoManage = null;
$banosAsignadosManage = [];
$contratoEdit = null;

if ($drawerMode === 'new') {
    $clientesDrawer = (new ListCustomers(new MysqliCustomerRepository($link)))->handle('rut', 'ASC')['items'];
    $banosDisponibles = (new ListAvailableBathrooms(new MysqliBathroomRepository($link)))->handle();
} elseif ($drawerMode === 'edit' || $drawerMode === 'view') {
    $idContratoEdit = (int) $_GET['id_Contrato'];
    $contratoEdit = (new FindContract(new MysqliContractRepository($link)))->handle($idContratoEdit);
    if ($contratoEdit !== null) {
        $clientesDrawer = (new ListCustomers(new MysqliCustomerRepository($link)))->handle('rut', 'ASC')['items'];
    } else {
        $drawerMode = null;
    }
} elseif ($drawerMode === 'manage') {
    $idContratoManage = (int) $_GET['id_Contrato'];
    $contratoManage = (new FindContractWithCustomer(new MysqliContractRepository($link)))->handle($idContratoManage);
    if ($contratoManage !== null) {
        $banosAsignadosManage = (new ListBathroomsByContract(new MysqliBathroomRepository($link)))->handle($idContratoManage);
        $banosDisponibles = (new ListAvailableBathrooms(new MysqliBathroomRepository($link)))->handle();
    } else {
        $drawerMode = null;
    }
}

$closeDrawerQs = baseQueryString(['action', 'err', 'id_Contrato']);
$closeDrawerUrl = 'dash-contracts.php' . ($closeDrawerQs !== '' ? '?' . ltrim($closeDrawerQs, '&') : '');

function baseQueryString(array $excludes = ['page']): string {
    $params = [];
    foreach ($_GET as $k => $v) {
        if (!in_array($k, $excludes, true) && $v !== '' && $v !== null) {
            $params[] = $k . '=' . urlencode((string) $v);
        }
    }
    return $params ? '&' . implode('&', $params) : '';
}

function contractsFilterUrl(?int $estado, ?string $currentSort, string $currentDir): string {
    $params = [];
    if ($estado !== null) {
        $params[] = 'estado=' . $estado;
    }
    if ($currentSort !== null) {
        $params[] = 'sort=' . $currentSort;
        $params[] = 'dir=' . $currentDir;
    }
    return 'dash-contracts.php' . ($params ? '?' . implode('&', $params) : '');
}

function sortUrl(string $column, ?string $currentSort, string $currentDir, ?int $estado): string {
    $newDir = ($column === $currentSort && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    $url = '?sort=' . $column . '&dir=' . $newDir;
    if ($estado !== null) {
        $url .= '&estado=' . $estado;
    }
    return $url;
}

function sort_header_html(string $label, string $column, ?string $currentSort, string $currentDir, ?int $estado): string {
    $url = htmlspecialchars(sortUrl($column, $currentSort, $currentDir, $estado), ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $icon = $currentSort === $column
        ? '<i data-lucide="' . ($currentDir === 'ASC' ? 'chevron-up' : 'chevron-down') . '" class="w-3 h-3 text-indigo-600"></i>'
        : '<i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-300"></i>';
    return <<<HTML
        <a href="{$url}" class="inline-flex items-center gap-1 hover:text-slate-600 transition-colors">
            <span>{$label}</span>
            {$icon}
        </a>
        HTML;
}
?>

<head>
    <title>Contratos | Blanco Servicios - Admin & Dashboard</title>
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
                    <ul class="flex items-center gap-2">
                        <li>
                            <a href="<?php echo htmlspecialchars(contractsFilterUrl(null, $sortBy, $sortDir), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $estado_Contrato_filtro === null ? 'active ' : ''; ?>group flex items-center gap-1.5 rounded-xl bg-slate-100 px-4 py-2 font-sans text-xs font-semibold text-slate-500 transition-all duration-200 hover:text-slate-800 [&.active]:bg-slate-900 [&.active]:text-white">
                                Todos los Contratos
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700 group-[.active]:bg-white/15 group-[.active]:text-white"><?php echo (int) $total_contratos_todos; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo htmlspecialchars(contractsFilterUrl(2, $sortBy, $sortDir), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $estado_Contrato_filtro === 2 ? 'active ' : ''; ?>group flex items-center gap-1.5 rounded-xl bg-slate-100 px-4 py-2 font-sans text-xs font-semibold text-slate-500 transition-all duration-200 hover:text-slate-800 [&.active]:bg-slate-900 [&.active]:text-white">
                                Contratos Activos
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700 group-[.active]:bg-white/15 group-[.active]:text-white"><?php echo (int) $total_contratos_activos; ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo htmlspecialchars(contractsFilterUrl(1, $sortBy, $sortDir), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $estado_Contrato_filtro === 1 ? 'active ' : ''; ?>group flex items-center gap-1.5 rounded-xl bg-slate-100 px-4 py-2 font-sans text-xs font-semibold text-slate-500 transition-all duration-200 hover:text-slate-800 [&.active]:bg-slate-900 [&.active]:text-white">
                                Contratos Terminados
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700 group-[.active]:bg-white/15 group-[.active]:text-white"><?php echo (int) $total_contratos_terminados; ?></span>
                            </a>
                        </li>
                    </ul>
                    <?php
                        table_native_open([
                            'table_id' => 'tabla-contratos',
                            'search_placeholder' => 'Buscar por cliente, obra...',
                            'item_label' => $titulo_listado,
                            'per_page' => 9,
                            'actions_html' => '<a href="?action=new' . htmlspecialchars(baseQueryString(['action', 'id_Contrato']), ENT_QUOTES, 'UTF-8') . '" id="add-contract-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 shadow-lg shadow-indigo-600/10 transition-all active:scale-95"><i data-lucide="plus" class="w-3.5 h-3.5"></i><span>Agregar Nuevo Contrato</span></a>',
                            'columns' => [
                                ['label' => sort_header_html('Cliente', 'cliente', $sortBy, $sortDir, $estado_Contrato_filtro), 'html' => true],
                                ['label' => sort_header_html('Obra', 'obra', $sortBy, $sortDir, $estado_Contrato_filtro), 'html' => true],
                                ['label' => sort_header_html('Estado', 'estado', $sortBy, $sortDir, $estado_Contrato_filtro), 'html' => true],
                                ['label' => 'Fecha de Inicio'],
                                ['label' => 'Fecha de Fin'],
                                ['label' => 'Valor Mensual'],
                                ['label' => 'Valor Total'],
                                ['label' => 'Acción'],
                            ],
                        ]);
                    ?>
                    <?php foreach ($listado['items'] as $row): ?>
                        <?php
                            $cliente = htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8');
                            $obra = htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8');
                            $fechaInicio = htmlspecialchars(date('d-m-Y', strtotime($row['fechaInicio_Contrato'])), ENT_QUOTES, 'UTF-8');
                            $fechaFin = htmlspecialchars(date('d-m-Y', strtotime($row['fechaFin_Contrato'])), ENT_QUOTES, 'UTF-8');
                            $searchable = htmlspecialchars($row['nombre_Cliente'] . ' ' . $row['obra_Contrato'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-slate-50/75 transition-colors" data-search="<?php echo $searchable; ?>">
                            <td class="px-6 py-4.5">
                                <div class="flex items-center text-xs text-slate-700 font-sans font-semibold">
                                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                    <span><?php echo $cliente; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="flex items-center text-xs text-slate-600 font-sans">
                                    <i data-lucide="briefcase" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                    <span><?php echo $obra; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4.5">
                                <?php if ($row['estado_Contrato'] == 2): ?>
                                    <span class="badge-status is-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge-status is-info">Terminada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="flex items-center text-xs text-slate-600 font-sans">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                    <span><?php echo $fechaInicio; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="flex items-center text-xs text-slate-600 font-sans">
                                    <i data-lucide="calendar-off" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                    <span><?php echo $fechaFin; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4.5">
                                <span class="text-xs text-slate-600 font-mono">$ <?php echo number_format((int) $row['valorMensual_Contrato'], 0, ',', '.'); ?></span>
                            </td>
                            <td class="px-6 py-4.5">
                                <span class="text-xs text-slate-700 font-mono font-semibold">$ <?php echo number_format((int) $row['valorTotal_Contrato'], 0, ',', '.'); ?></span>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="flex items-center gap-1">
                                    <?php if ($row['estado_Contrato'] == 2): ?>
                                        <a href="?action=edit&id_Contrato=<?php echo (int) $row['id_Contrato']; ?><?php echo htmlspecialchars(baseQueryString(['action', 'id_Contrato']), ENT_QUOTES, 'UTF-8'); ?>" class="dt-cell-action" title="Editar">
                                            <i data-lucide="square-pen"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=view&id_Contrato=<?php echo (int) $row['id_Contrato']; ?><?php echo htmlspecialchars(baseQueryString(['action', 'id_Contrato']), ENT_QUOTES, 'UTF-8'); ?>" class="dt-cell-action" title="Ver">
                                            <i data-lucide="eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    <div class="dropdown">
                                        <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                            <i data-lucide="more-horizontal"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end m-0 min-w-[220px] list-none overflow-hidden rounded-2xl border border-slate-100 bg-white p-2 shadow-xl shadow-slate-200/50">
                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="?action=edit&id_Contrato=<?php echo (int) $row['id_Contrato']; ?><?php echo htmlspecialchars(baseQueryString(['action', 'id_Contrato']), ENT_QUOTES, 'UTF-8'); ?>"><i data-lucide="square-pen" class="!h-[14px] !w-[14px] shrink-0"></i>Editar</a></li>
                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="?action=manage&id_Contrato=<?php echo (int) $row['id_Contrato']; ?><?php echo htmlspecialchars(baseQueryString(['action', 'id_Contrato']), ENT_QUOTES, 'UTF-8'); ?>"><i data-lucide="bath" class="!h-[14px] !w-[14px] shrink-0"></i>Agregar Baños Químicos</a></li>
                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="controller/contract-active.php?id_Contrato=<?php echo (int) $row['id_Contrato']; ?>"><i data-lucide="lock-open" class="!h-[14px] !w-[14px] shrink-0"></i>Activar</a></li>
                                            <li><hr class="dropdown-divider m-1 border-slate-100"></li>
                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-rose-500 hover:bg-rose-50 hover:text-rose-500" href="controller/contract-inactive.php?id_Contrato=<?php echo (int) $row['id_Contrato']; ?>" data-confirm-delete data-confirm-title="¿Terminar este contrato?" data-confirm-text="Los baños asignados quedarán disponibles para otros contratos."><i data-lucide="lock" class="!h-[14px] !w-[14px] shrink-0"></i>Terminar</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($listado['items']) === 0): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                No hay contratos registrados en este momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php table_native_close(); ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php if ($drawerMode === 'new' || (($drawerMode === 'edit' || $drawerMode === 'view') && $contratoEdit !== null)): ?>
    <?php
        $isEdit = $drawerMode === 'edit' || $drawerMode === 'view';
        $isView = $drawerMode === 'view';
        $clienteActual = null;
        if ($isEdit) {
            foreach ($clientesDrawer as $cliente) {
                if ($cliente->id === $contratoEdit->customerId) {
                    $clienteActual = $cliente;
                    break;
                }
            }
        }
    ?>
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='<?php echo $closeDrawerUrl; ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0 app-drawer" id="contract-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/10">
                    <i data-lucide="<?php echo $isView ? 'eye' : ($isEdit ? 'square-pen' : 'briefcase'); ?>" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm"><?php echo $isView ? 'Ver Contrato' : ($isEdit ? 'Editar Contrato' : 'Registrar Nuevo Contrato'); ?></h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5"><?php echo $isView ? 'Detalle del servicio sanitario.' : ($isEdit ? 'Actualizar datos del servicio sanitario.' : 'Establecer servicio sanitario para faenas.'); ?></span>
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

        <form method="post" action="controller/<?php echo $isEdit ? 'contract-update.php' : 'contract-new.php'; ?>" class="flex-1 overflow-y-auto p-6 space-y-4" id="contract-drawer-form">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id_Contrato" value="<?php echo (int) $contratoEdit->id; ?>">
                <input type="hidden" name="estado_Contrato" value="<?php echo (int) $contratoEdit->state; ?>">
            <?php endif; ?>
            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Cliente Titular <span class="text-rose-500">*</span></label>
                <?php if ($isView): ?>
                    <input
                        type="text"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-slate-50 font-sans"
                        value="<?php echo $clienteActual !== null ? htmlspecialchars($clienteActual->name . ' (' . $clienteActual->rut . ')', ENT_QUOTES, 'UTF-8') : ''; ?>"
                        disabled
                    >
                <?php else: ?>
                    <select
                        name="id_Cliente"
                        id="id_Cliente_new_contract"
                        class="dt-select w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                        data-enhanced-select
                        data-search-placeholder="Buscar cliente..."
                        required
                    >
                        <option value="">Seleccione un cliente...</option>
                        <?php foreach ($clientesDrawer as $cliente): ?>
                            <option value="<?php echo (int) $cliente->id; ?>" <?php echo $isEdit && $cliente->id === $contratoEdit->customerId ? 'selected' : ''; ?>><?php echo htmlspecialchars($cliente->name, ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($cliente->rut, ENT_QUOTES, 'UTF-8'); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Nombre de Obra / Faena <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="obra_Contrato"
                    placeholder="e.g. OBRA RUTA 5 - CHONCHI"
                    value="<?php echo $isEdit ? htmlspecialchars($contratoEdit->obra, ENT_QUOTES, 'UTF-8') : ''; ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans<?php echo $isView ? ' bg-slate-50' : ''; ?>"
                    <?php echo $isView ? 'disabled' : 'required'; ?>
                >
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Fecha Inicio <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        name="fechaInicio_Contrato"
                        value="<?php echo $isEdit ? htmlspecialchars($contratoEdit->startDate, ENT_QUOTES, 'UTF-8') : ''; ?>"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono<?php echo $isView ? ' bg-slate-50' : ''; ?>"
                        <?php echo $isView ? 'disabled' : 'required'; ?>
                    >
                </div>
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Fecha Término <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        name="fechaFin_Contrato"
                        value="<?php echo $isEdit ? htmlspecialchars($contratoEdit->endDate, ENT_QUOTES, 'UTF-8') : ''; ?>"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono<?php echo $isView ? ' bg-slate-50' : ''; ?>"
                        <?php echo $isView ? 'disabled' : 'required'; ?>
                    >
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Valor Mensual (CLP) <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        inputmode="numeric"
                        name="valorMensual_Contrato"
                        placeholder="e.g. 125.000"
                        value="<?php echo $isEdit ? number_format($contratoEdit->monthlyValue, 0, ',', '.') : ''; ?>"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans<?php echo $isView ? ' bg-slate-50' : ''; ?>"
                        data-money-mask
                        <?php echo $isView ? 'disabled' : 'required'; ?>
                    >
                </div>
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Valor Total Estimado (CLP) <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        inputmode="numeric"
                        name="valorTotal_Contrato"
                        placeholder="e.g. 1.500.000"
                        value="<?php echo $isEdit ? number_format($contratoEdit->totalValue, 0, ',', '.') : ''; ?>"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans<?php echo $isView ? ' bg-slate-50' : ''; ?>"
                        data-money-mask
                        <?php echo $isView ? 'disabled' : 'required'; ?>
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Dirección de la Faena <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="direccion_Contrato"
                    placeholder="e.g. Km 12 Camino a Rilán, Castro"
                    value="<?php echo $isEdit ? htmlspecialchars($contratoEdit->address, ENT_QUOTES, 'UTF-8') : ''; ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans<?php echo $isView ? ' bg-slate-50' : ''; ?>"
                    <?php echo $isView ? 'disabled' : 'required'; ?>
                >
            </div>

            <?php if (!$isEdit): ?>
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Asignar Baños Disponibles</label>
                    <div class="p-3 border border-slate-200 rounded-xl max-h-32 overflow-y-auto space-y-1 bg-slate-50/50">
                        <?php foreach ($banosDisponibles as $bano): ?>
                            <label class="flex items-center gap-2 text-xs font-sans cursor-pointer select-none hover:bg-slate-100 px-2 py-1 rounded-md transition-colors">
                                <input type="checkbox" name="id_Bath[]" value="<?php echo (int) $bano['id_Bath']; ?>" class="accent-emerald-500 rounded">
                                <span class="font-mono font-bold text-slate-700"><?php echo htmlspecialchars($bano['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if (!empty($bano['observacion_Bath'])): ?>
                                    <span class="text-[10px] text-slate-400">(<?php echo htmlspecialchars($bano['observacion_Bath'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                        <?php if (empty($banosDisponibles)): ?>
                            <p class="text-[11px] text-slate-400 font-sans italic text-center py-2">No hay baños disponibles en bodega.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Condiciones / Observaciones</label>
                <textarea
                    name="observacion_Contrato"
                    placeholder="Mantenimiento los martes y sábados, incluir papel..."
                    rows="3"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans<?php echo $isView ? ' bg-slate-50' : ''; ?>"
                    <?php echo $isView ? 'disabled' : ''; ?>
                ><?php echo $isEdit ? htmlspecialchars($contratoEdit->observation ?? '', ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center space-x-3">
                <?php if ($isView): ?>
                    <a
                        href="<?php echo $closeDrawerUrl; ?>"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
                    >
                        Cerrar
                    </a>
                <?php else: ?>
                    <a
                        href="<?php echo $closeDrawerUrl; ?>"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
                    >
                        Cancelar
                    </a>
                    <button
                        type="submit"
                        name="<?php echo $isEdit ? 'update' : 'crear'; ?>"
                        id="submit-new-contract"
                        class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-semibold font-sans transition-all shadow-lg shadow-emerald-500/10"
                    >
                        <?php echo $isEdit ? 'Actualizar Contrato' : 'Establecer Contrato'; ?>
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php if ($drawerMode === 'manage' && $contratoManage !== null): ?>
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='<?php echo $closeDrawerUrl; ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0 app-drawer" id="bathrooms-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/10 shrink-0">
                    <i data-lucide="bath" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="font-sans font-bold text-slate-900 text-sm truncate"><?php echo htmlspecialchars($contratoManage['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5 truncate"><?php echo htmlspecialchars($contratoManage['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            </div>
            <a href="<?php echo $closeDrawerUrl; ?>" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all shrink-0" aria-label="Cerrar panel">
                <i data-lucide="x" class="w-5 h-5"></i>
            </a>
        </div>

        <?php if ($drawerError !== ''): ?>
            <div class="px-6 py-3 bg-rose-50 border-b border-rose-100 text-rose-700 font-sans text-xs">
                <?php echo htmlspecialchars($drawerError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Baños Asignados a la Obra</label>
                <div class="border border-slate-200 rounded-xl divide-y divide-slate-100 bg-white overflow-hidden">
                    <?php if (empty($banosAsignadosManage)): ?>
                        <p class="m-0 p-4 text-center text-xs italic text-slate-400 font-sans">Sin baños asignados a esta obra.</p>
                    <?php else: ?>
                        <?php $esUltimoBanoAsignado = count($banosAsignadosManage) === 1; ?>
                        <?php foreach ($banosAsignadosManage as $bano): ?>
                            <div class="flex items-center justify-between gap-3 px-4 py-2.5">
                                <div class="flex items-center gap-2 min-w-0">
                                    <i data-lucide="bath" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    <span class="font-mono text-xs font-bold text-slate-700 truncate"><?php echo htmlspecialchars($bano['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="font-sans text-[10px] text-slate-400 shrink-0">C: <?php echo htmlspecialchars(date('d-m-Y', strtotime($bano['fechaCompra_Bath'])), ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <a
                                    href="controller/contract-bath-notassign.php?id_Contrato=<?php echo (int) $bano['id_Contrato']; ?>&id_Bath=<?php echo (int) $bano['id_Bath']; ?>&id_Relacion=<?php echo (int) $bano['id_Relacion']; ?>"
                                    class="dt-cell-action shrink-0"
                                    title="Desasignar"
                                    data-confirm-delete
                                    <?php if ($esUltimoBanoAsignado): ?>
                                        data-confirm-title="¿Desasignar el último baño de la obra?"
                                        data-confirm-text="Al quedar sin baños asignados, el contrato pasará automáticamente a estado Terminada."
                                    <?php else: ?>
                                        data-confirm-title="¿Desasignar este baño?"
                                        data-confirm-text="El baño quedará disponible para otros contratos."
                                    <?php endif; ?>
                                >
                                    <i data-lucide="trash-2" class="!h-3.5 !w-3.5"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <form method="post" action="controller/contract-bath-new-assign.php" class="space-y-1.5 pt-4 border-t border-slate-100">
                <input type="hidden" name="id_Contrato" value="<?php echo (int) $contratoManage['id_Contrato']; ?>">
                <label class="font-sans text-xs font-bold text-slate-600 block">Asignar Baños Disponibles</label>
                <div class="p-3 border border-slate-200 rounded-xl max-h-32 overflow-y-auto space-y-1 bg-slate-50/50">
                    <?php foreach ($banosDisponibles as $bano): ?>
                        <label class="flex items-center gap-2 text-xs font-sans cursor-pointer select-none hover:bg-slate-100 px-2 py-1 rounded-md transition-colors">
                            <input type="checkbox" name="id_Bath[]" value="<?php echo (int) $bano['id_Bath']; ?>" class="accent-emerald-500 rounded">
                            <span class="font-mono font-bold text-slate-700"><?php echo htmlspecialchars($bano['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if (!empty($bano['observacion_Bath'])): ?>
                                <span class="text-[10px] text-slate-400">(<?php echo htmlspecialchars($bano['observacion_Bath'], ENT_QUOTES, 'UTF-8'); ?>)</span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                    <?php if (empty($banosDisponibles)): ?>
                        <p class="text-[11px] text-slate-400 font-sans italic text-center py-2">No hay baños disponibles en bodega.</p>
                    <?php endif; ?>
                </div>
                <button
                    type="submit"
                    name="update"
                    class="w-full mt-2 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-semibold font-sans transition-all shadow-lg shadow-emerald-500/10"
                >
                    Asignar
                </button>
            </form>

            <a
                href="<?php echo $closeDrawerUrl; ?>"
                class="block w-full py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
            >
                Cerrar
            </a>
        </div>
    </div>
<?php endif; ?>

<?php if ($drawerMode === 'new' || $drawerMode === 'edit' || $drawerMode === 'view' || $drawerMode === 'manage'): ?>
    <script>
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

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>
<script src="assets/js/components/native-table.js"></script>
<script src="assets/js/components/money-mask.js"></script>

<?php if (($_GET['flash'] ?? '') !== ''): ?>
    <script>
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
