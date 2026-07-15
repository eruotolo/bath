<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/layouts/helpers.php';

use App\Application\Bathroom\ListBathroomsWithAssignment;
use App\Application\Bathroom\FindBathroom;
use App\Application\Contract\ListContracts;
use App\Infrastructure\Persistence\MysqliBathroomRepository;
use App\Infrastructure\Persistence\MysqliContractRepository;

global $link;

include('layouts/config.php');

$repo = new MysqliBathroomRepository($link);
$csrf_token = csrf_token();

$bañosAll = (new ListBathroomsWithAssignment($repo))->handle()['items'];

// --- Filtro por pill (mapeo 0.2): server-side sobre TODOS los registros ---
$allowedFilters = ['todos', 'disponible', 'asignado', 'mantencion', 'inactivo'];
$rawFilter = $_GET['filter'] ?? 'todos';
$filter = in_array($rawFilter, $allowedFilters, true) ? $rawFilter : 'todos';

$bañosFiltrados = [];
foreach ($bañosAll as $b) {
    $estado = (int) $b['estado_Bath'];
    $asignado = (int) $b['asignado_Bath'];
    if ($filter === 'inactivo') {
        $keep = $estado === 0;
    } elseif ($filter === 'mantencion') {
        $keep = $estado === 2;
    } elseif ($filter === 'disponible') {
        $keep = $estado === 1 && $asignado === 0;
    } elseif ($filter === 'asignado') {
        $keep = $estado === 1 && $asignado === 1;
    } else {
        $keep = true;
    }
    if ($keep) {
        $bañosFiltrados[] = $b;
    }
}

$totalBaños = count($bañosFiltrados);

// --- Paginación server-side (respeta el filtro activo) ---
$perPage = 12;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalPages = (int) max(1, ceil($totalBaños / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;
$baños = array_slice($bañosFiltrados, $offset, $perPage);

$firstShown = $totalBaños > 0 ? $offset + 1 : 0;
$lastShown = $offset + count($baños);

// --- Modo drawer alta/edición ---
$drawerMode = null;
$editingBath = null;
if (($_GET['action'] ?? '') === 'new') {
    $drawerMode = 'new';
} elseif (($_GET['action'] ?? '') === 'edit' && isset($_GET['id'])) {
    $editingBath = (new FindBathroom($repo))->handle((int) $_GET['id']);
    if ($editingBath !== null) {
        $drawerMode = 'edit';
    }
}

// --- Asignar/retirar obra: integrado en el drawer de edición (no un modal separado) ---
$contratosActivos = [];
if ($drawerMode === 'edit' && $editingBath !== null && $editingBath->estadoBath === 1 && $editingBath->asignadoBath === 0) {
    $contratosActivos = (new ListContracts(new MysqliContractRepository($link)))->handle(2)['items'];
    usort($contratosActivos, fn($a, $b) => strcasecmp((string) $a['obra_Contrato'], (string) $b['obra_Contrato']));
}

$drawerError = isset($_GET['err']) ? (string) $_GET['err'] : '';

function bath_error_is_codigo(string $err): bool {
    if ($err === '') {
        return false;
    }
    return str_contains(mb_strtolower($err, 'UTF-8'), 'código');
}

$err_es_codigo = $drawerError !== '' && bath_error_is_codigo($drawerError);
$mostrar_banner_err = $drawerError !== '' && ! $err_es_codigo;

function base_query_string(array $excludes = ['page']): string {
    $allowed_keys = ['filter', 'page', 'action', 'id', 'assign', 'flash', 'msg', 'err'];
    $params = [];

    foreach ($_GET as $key => $value) {
        if (!in_array($key, $allowed_keys, true)
            || in_array($key, $excludes, true)
            || !is_scalar($value)
            || $value === '') {
            continue;
        }

        $params[$key] = (string) $value;
    }

    $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    return $query !== '' ? '&' . $query : '';
}

// --- Metadatos visuales por estado: Inactivo=slate > Mantención=amber > Asignado=blue > Disponible=emerald ---
function bath_estado_meta(int $estado, int $asignado): array {
    if ($estado === 0) {
        return [
            'tone' => 'slate',
            'accent' => 'bg-slate-400',
            'border' => 'border-slate-200 hover:border-slate-300',
            'shadow' => 'shadow-slate-500/5',
            'icon' => 'text-slate-500',
            'dot' => 'bg-slate-400',
            'tagBg' => 'bg-slate-100 text-slate-600',
        ];
    }
    if ($estado === 2) {
        return [
            'tone' => 'amber',
            'accent' => 'bg-amber-400',
            'border' => 'border-amber-100 hover:border-amber-200',
            'shadow' => 'shadow-amber-500/5',
            'icon' => 'text-amber-500',
            'dot' => 'bg-amber-400 animate-pulse',
            'tagBg' => 'bg-amber-50 text-amber-700',
        ];
    }
    if ($asignado === 1) {
        return [
            'tone' => 'blue',
            'accent' => 'bg-blue-500',
            'border' => 'border-blue-100 hover:border-blue-200',
            'shadow' => 'shadow-blue-500/5',
            'icon' => 'text-blue-500',
            'dot' => 'bg-blue-500',
            'tagBg' => 'bg-blue-50 text-blue-700',
        ];
    }
    return [
        'tone' => 'emerald',
        'accent' => 'bg-emerald-400',
        'border' => 'border-emerald-100 hover:border-emerald-200',
        'shadow' => 'shadow-emerald-500/5',
        'icon' => 'text-emerald-500',
        'dot' => 'bg-emerald-400',
        'tagBg' => 'bg-emerald-50 text-emerald-700',
    ];
}

// --- Badge de "Estado Técnico" en la tabla: refleja solo estado_Bath, no la asignación ---
function bath_estado_tecnico(int $estado): array {
    return match ($estado) {
        1 => ['label' => 'Activo', 'tagBg' => 'bg-emerald-50 text-emerald-700'],
        2 => ['label' => 'Mantención', 'tagBg' => 'bg-amber-50 text-amber-700'],
        default => ['label' => 'Inactivo', 'tagBg' => 'bg-slate-100 text-slate-600'],
    };
}

$pills = [
    'todos' => 'Todos',
    'disponible' => 'Disponibles',
    'asignado' => 'Asignados',
    'mantencion' => 'Mantención',
    'inactivo' => 'Inactivos',
];

?>

<head>

    <title>Inventario de Baños | Blanco Servicios - Admin & Dashboard</title>

    <?php include 'layouts/head.php'; ?>

    <?php include 'layouts/head-style.php'; ?>

    <style>
        html[data-bathrooms-view="table"] #banos-grid { display: none !important; }
        html[data-bathrooms-view="table"] #banos-table { display: block !important; }
    </style>
    <script>
        try {
            if (window.localStorage.getItem('bathrooms-view') === 'table') {
                document.documentElement.dataset.bathroomsView = 'table';
            }
        } catch (error) {}
    </script>

</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

        <div class="main-content">
            <div class="page-content">
            <div class="container-fluid px-10 py-10 bg-slate-50/50">

                <div class="space-y-6 max-w-full mx-auto">

                    <!-- Toolbar: search + pills + toggle grid/tabla + Registrar -->
                    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                            <div class="relative max-w-xs">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <input
                                    type="text"
                                    placeholder="Código o Faena..."
                                    id="banos-local-search"
                                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-colors font-sans"
                                />
                            </div>

                            <div class="flex items-center bg-slate-100 p-1 rounded-xl gap-0.5">
                                <?php foreach ($pills as $key => $label): ?>
                                    <a
                                        href="?filter=<?php echo $key; ?><?php echo base_query_string(['filter', 'page', 'action', 'id', 'assign', 'err']); ?>"
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
                                    aria-controls="banos-grid banos-table"
                                    title="Vista de Cabinas"
                                    class="p-1.5 rounded-lg transition-all bg-white text-slate-900 shadow-sm"
                                >
                                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                                </button>
                                <button
                                    type="button"
                                    data-view-toggle="table"
                                    aria-controls="banos-grid banos-table"
                                    title="Vista de Tabla"
                                    class="p-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-800"
                                >
                                    <i data-lucide="list" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <a
                                href="?action=new<?php echo base_query_string(['page', 'action', 'id', 'assign', 'err']); ?>"
                                id="new-bath-btn"
                                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 shadow-lg shadow-emerald-500/10 transition-all active:scale-95"
                            >
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Registrar Baño</span>
                            </a>
                        </div>
                    </div>

                    <!-- Grid de cards (#banos-grid) -->
                    <div id="banos-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <?php foreach ($baños as $b):
                            $meta = bath_estado_meta((int) $b['estado_Bath'], (int) $b['asignado_Bath']);
                            $obra = $b['obra_Contrato'] !== null ? (string) $b['obra_Contrato'] : '';
                            $cliente = $b['nombre_Cliente'] !== null ? (string) $b['nombre_Cliente'] : '';
                            $obs = (string) $b['observacion_Bath'];
                            $fecha = date('d-m-Y', strtotime((string) $b['fechaCompra_Bath']));
                        ?>
                            <div
                                class="bano-card p-5 rounded-3xl border transition-all duration-300 relative overflow-hidden group flex flex-col justify-between h-48 bg-white <?php echo $meta['border']; ?> <?php echo $meta['shadow']; ?>"
                                data-estado="<?php echo (int) $b['estado_Bath']; ?>"
                                data-asignado="<?php echo (int) $b['asignado_Bath']; ?>"
                                data-code="<?php echo htmlspecialchars($b['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-obra="<?php echo htmlspecialchars($obra, ENT_QUOTES, 'UTF-8'); ?>"
                                data-cliente="<?php echo htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <div class="absolute top-0 left-0 right-0 h-1.5 <?php echo $meta['accent']; ?>"></div>

                                <div class="flex items-center justify-between">
                                    <a
                                        href="?action=edit&id=<?php echo (int) $b['id_Bath']; ?><?php echo base_query_string(['page', 'action', 'id', 'assign', 'err']); ?>"
                                        class="flex items-center space-x-2 hover:opacity-80 transition-opacity"
                                        title="Editar cabina"
                                    >
                                        <i data-lucide="bath" class="w-4 h-4 <?php echo $meta['icon']; ?>"></i>
                                        <span class="font-mono font-extrabold text-sm text-slate-800"><?php echo htmlspecialchars($b['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </a>
                                    <span class="w-2.5 h-2.5 rounded-full <?php echo $meta['dot']; ?>"></span>
                                </div>

                                <div class="space-y-1 my-3">
                                    <?php if ($meta['tone'] === 'blue'): ?>
                                        <span class="text-[10px] font-mono font-bold text-blue-600 block uppercase">Asignado</span>
                                        <?php if ($obra !== ''): ?>
                                            <p class="text-xs font-sans font-bold text-slate-800 truncate max-w-[150px]"><?php echo htmlspecialchars($obra, ENT_QUOTES, 'UTF-8'); ?></p>
                                            <p class="text-[9px] font-sans text-slate-400 truncate max-w-[150px]"><?php echo htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8'); ?></p>
                                        <?php else: ?>
                                            <p class="text-[11px] font-sans text-slate-400 italic truncate max-w-[150px]">Obra no disponible</p>
                                        <?php endif; ?>
                                    <?php elseif ($meta['tone'] === 'amber'): ?>
                                        <span class="text-[10px] font-mono font-bold text-amber-600 block uppercase">Mantención</span>
                                        <p class="text-[11px] font-sans text-slate-500 italic truncate max-w-[150px]"><?php echo htmlspecialchars($obs, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php elseif ($meta['tone'] === 'slate'): ?>
                                        <span class="text-[10px] font-mono font-bold text-slate-600 block uppercase">Inactivo</span>
                                        <p class="text-[11px] font-sans text-slate-500 italic truncate max-w-[150px]"><?php echo htmlspecialchars($obs, ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php else: ?>
                                        <span class="text-[10px] font-mono font-bold text-emerald-600 block uppercase">En Bodega</span>
                                        <p class="text-[11px] font-sans text-slate-500 font-medium">Disponible para obra</p>
                                    <?php endif; ?>
                                </div>

                                <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-[10px] font-sans">
                                    <span class="text-slate-400 font-mono">C: <?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <a
                                        href="?action=edit&id=<?php echo (int) $b['id_Bath']; ?><?php echo base_query_string(['page', 'action', 'id', 'assign', 'err']); ?>"
                                        class="text-emerald-600 hover:text-emerald-700 font-bold hover:underline transition-colors"
                                    >
                                        Editar baño
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if (count($baños) === 0): ?>
                            <div class="col-span-full text-center py-16 text-slate-400 font-sans text-sm">
                                No se encontraron baños que coincidan con el filtro.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tabla (#banos-table) — oculta por defecto; la visibilidad la controla bathrooms.js (Fase 4) -->
                    <div id="banos-table" class="hidden bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-50 bg-slate-50/50">
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Código</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">F. Adquisición</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Ubicación Actual</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Observaciones</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Estado Técnico</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach ($baños as $b):
                                        $meta = bath_estado_meta((int) $b['estado_Bath'], (int) $b['asignado_Bath']);
                                        $estadoTecnico = bath_estado_tecnico((int) $b['estado_Bath']);
                                        $obra = $b['obra_Contrato'] !== null ? (string) $b['obra_Contrato'] : '';
                                        $cliente = $b['nombre_Cliente'] !== null ? (string) $b['nombre_Cliente'] : '';
                                        $obs = (string) $b['observacion_Bath'];
                                    ?>
                                        <tr
                                            class="bano-row hover:bg-slate-50/50 transition-colors group"
                                            data-estado="<?php echo (int) $b['estado_Bath']; ?>"
                                            data-asignado="<?php echo (int) $b['asignado_Bath']; ?>"
                                            data-code="<?php echo htmlspecialchars($b['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?>"
                                            data-obra="<?php echo htmlspecialchars($obra, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-cliente="<?php echo htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                            <td class="px-6 py-4">
                                                <a
                                                    href="?action=edit&id=<?php echo (int) $b['id_Bath']; ?><?php echo base_query_string(['page', 'action', 'id', 'assign', 'err']); ?>"
                                                    class="font-mono font-extrabold text-slate-800 text-sm hover:text-emerald-600 transition-colors"
                                                >
                                                    <?php echo htmlspecialchars($b['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 font-mono text-xs text-slate-500"><?php echo htmlspecialchars(date('d-m-Y', strtotime((string) $b['fechaCompra_Bath'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4">
                                                <?php if ($meta['tone'] === 'blue'): ?>
                                                    <?php if ($obra !== ''): ?>
                                                        <div class="space-y-0.5">
                                                            <span class="font-sans font-bold text-xs text-slate-800 block"><?php echo htmlspecialchars($obra, ENT_QUOTES, 'UTF-8'); ?></span>
                                                            <span class="font-sans text-[10px] text-slate-400 block"><?php echo htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-xs text-slate-400 italic">Obra no disponible</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-bold uppercase font-sans">Bodega Central</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-sans text-slate-500 italic max-w-xs truncate"><?php echo htmlspecialchars($obs, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4">
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold font-sans tracking-wide uppercase <?php echo $estadoTecnico['tagBg']; ?>">
                                                    <?php echo $estadoTecnico['label']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="inline-flex items-center gap-1">
                                                    <a
                                                        href="?action=edit&id=<?php echo (int) $b['id_Bath']; ?><?php echo base_query_string(['page', 'action', 'id', 'assign', 'err']); ?>"
                                                        class="px-3 py-1 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors text-xs font-semibold rounded-lg font-sans"
                                                    >
                                                        Editar
                                                    </a>

                                                    <div class="dropdown">
                                                        <button class="p-1.5 rounded-lg border border-slate-100 text-slate-500 hover:text-slate-800 hover:border-slate-200 transition-all dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Más acciones">
                                                            <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item" href="?action=edit&id=<?php echo (int) $b['id_Bath']; ?><?php echo base_query_string(['page', 'action', 'id', 'assign', 'err']); ?>">
                                                                    <i data-lucide="square-pen" class="!mr-2 !h-[14px] !w-[14px]"></i>Editar
                                                                </a>
                                                            </li>
                                                            <li><a class="dropdown-item" href="controller/bath-active.php?id_Bath=<?php echo (int) $b['id_Bath']; ?>"><i data-lucide="lock-open" class="!mr-2 !h-[14px] !w-[14px]"></i>Activar</a></li>
                                                            <li><a class="dropdown-item" href="controller/bath-maintenance.php?id_Bath=<?php echo (int) $b['id_Bath']; ?>"><i data-lucide="wrench" class="!mr-2 !h-[14px] !w-[14px]"></i>Mantención</a></li>
                                                            <li><a class="dropdown-item" href="controller/bath-inactive.php?id_Bath=<?php echo (int) $b['id_Bath']; ?>"><i data-lucide="lock" class="!mr-2 !h-[14px] !w-[14px]"></i>Inactivar</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="controller/bath-delete.php?id_Bath=<?php echo (int) $b['id_Bath']; ?>" data-confirm-delete data-confirm-title="¿Eliminar este baño?" data-confirm-text="Esta acción no se puede deshacer.">
                                                                    <i data-lucide="trash-2" class="!mr-2 !h-[14px] !w-[14px]"></i>Eliminar
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($baños) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                                No se encontraron baños que coincidan con el filtro.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer de paginación -->
                    <div class="flex items-center justify-between px-1">
                        <span class="font-mono text-[10px] text-slate-400 font-bold uppercase">Mostrando <?php echo $firstShown; ?>-<?php echo $lastShown; ?> de <?php echo $totalBaños; ?> Baños</span>
                        <div class="flex items-center space-x-1">
                            <?php if ($currentPage > 1): ?>
                                <a href="?page=<?php echo $currentPage - 1; ?><?php echo base_query_string(['page']); ?>" class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 transition-colors hover:bg-slate-50 hover:text-emerald-600 hover:border-slate-200">Anterior</a>
                            <?php else: ?>
                                <button class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 cursor-not-allowed" disabled>Anterior</button>
                            <?php endif; ?>
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <?php if ($p === $currentPage): ?>
                                    <span class="px-3 py-1 rounded-lg border border-emerald-600 bg-emerald-600 text-white text-xs font-semibold"><?php echo $p; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $p; ?><?php echo base_query_string(['page']); ?>" class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 transition-colors hover:bg-slate-50 hover:text-emerald-600 hover:border-slate-200"><?php echo $p; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?page=<?php echo $currentPage + 1; ?><?php echo base_query_string(['page']); ?>" class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 transition-colors hover:bg-slate-50 hover:text-emerald-600 hover:border-slate-200">Siguiente</a>
                            <?php else: ?>
                                <button class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 cursor-not-allowed" disabled>Siguiente</button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<?php if ($drawerMode === 'new' || $drawerMode === 'edit'): ?>
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='?page=<?php echo (int) $currentPage; ?><?php echo base_query_string(['action', 'id', 'assign', 'err']); ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0" id="bath-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl <?php echo $drawerMode === 'edit' ? 'bg-emerald-100 text-emerald-700' : 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/10'; ?> flex items-center justify-center">
                    <i data-lucide="<?php echo $drawerMode === 'edit' ? 'square-pen' : 'bath'; ?>" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm">
                        <?php echo $drawerMode === 'edit' ? 'Editar Cabina' : 'Registrar Nueva Cabina'; ?>
                    </h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5">
                        <?php if ($drawerMode === 'edit'): ?>
                            <?php echo htmlspecialchars($editingBath->codigoBath, ENT_QUOTES, 'UTF-8'); ?>
                        <?php else: ?>
                            Ingresar al inventario de Blanco Servicios.
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <a href="?page=<?php echo (int) $currentPage; ?><?php echo base_query_string(['action', 'id', 'assign', 'err']); ?>" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all" aria-label="Cerrar panel">
                <i data-lucide="x" class="w-5 h-5"></i>
            </a>
        </div>

        <?php if ($mostrar_banner_err): ?>
            <div class="px-6 py-3 bg-rose-50 border-b border-rose-100 text-rose-700 font-sans text-xs">
                <?php echo htmlspecialchars($drawerError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form
            method="post"
            action="controller/<?php echo $drawerMode === 'edit' ? 'bath-edit-drawer.php' : 'bath-create-drawer.php'; ?>"
            class="flex-1 overflow-y-auto p-6 space-y-5"
            id="bath-drawer-form"
        >
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if ($drawerMode === 'edit'): ?>
                <input type="hidden" name="id_Bath" value="<?php echo (int) $editingBath->id; ?>">
            <?php endif; ?>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Código Único de Cabina <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="codigo_Bath"
                    placeholder="e.g. AT096"
                    value="<?php echo htmlspecialchars($drawerMode === 'edit' ? $editingBath->codigoBath : '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border transition-all font-mono <?php echo $err_es_codigo ? 'border-rose-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20' : 'border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20'; ?>"
                    required
                >
                <?php if ($err_es_codigo): ?>
                    <p class="mt-1.5 text-[11px] font-sans text-rose-600 flex items-center gap-1">
                        <i data-lucide="alert-circle" class="w-3 h-3 shrink-0"></i>
                        <?php echo htmlspecialchars($drawerError, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Fecha de Compra / Ingreso</label>
                <input
                    type="date"
                    name="fechaCompra_Bath"
                    value="<?php echo htmlspecialchars($drawerMode === 'edit' ? $editingBath->fechaCompraBath : date('Y-m-d'), ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono"
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Estado Técnico</label>
                <select
                    name="estado_Bath"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                >
                    <option value="1" <?php echo ($drawerMode === 'edit' && $editingBath->estadoBath === 1) ? 'selected' : ''; ?>>Activo (Disponible para faenas)</option>
                    <option value="2" <?php echo ($drawerMode === 'edit' && $editingBath->estadoBath === 2) ? 'selected' : ''; ?>>Mantención (En reparación)</option>
                    <option value="0" <?php echo ($drawerMode === 'edit' && $editingBath->estadoBath === 0) ? 'selected' : ''; ?>>Inactivo (Fuera de servicio)</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Observaciones Técnicas</label>
                <textarea
                    name="observacion_Bath"
                    placeholder="e.g. Cabina estándar reforzada, SO"
                    rows="4"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                ><?php echo htmlspecialchars($drawerMode === 'edit' ? $editingBath->observacionBath : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <a
                    href="?page=<?php echo (int) $currentPage; ?><?php echo base_query_string(['action', 'id', 'assign', 'err']); ?>"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    name="<?php echo $drawerMode === 'edit' ? 'submit_edit_bath' : 'submit_new_bath'; ?>"
                    id="<?php echo $drawerMode === 'edit' ? 'submit-edit-bath' : 'submit-new-bath'; ?>"
                    class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-semibold font-sans transition-colors shadow-lg shadow-emerald-500/10"
                >
                    <?php echo $drawerMode === 'edit' ? 'Guardar Cambios' : 'Guardar en Inventario'; ?>
                </button>
            </div>
        </form>

        <?php if ($drawerMode === 'edit'): ?>
            <div class="px-6 py-5 border-t border-slate-100 bg-slate-50 space-y-3">
                <?php if ($editingBath->asignadoBath === 1): ?>
                    <?php
                        $obraActual = '';
                        $clienteActual = '';
                        foreach ($bañosAll as $bAll) {
                            if ((int) $bAll['id_Bath'] === (int) $editingBath->id) {
                                $obraActual = $bAll['obra_Contrato'] !== null ? (string) $bAll['obra_Contrato'] : '';
                                $clienteActual = $bAll['nombre_Cliente'] !== null ? (string) $bAll['nombre_Cliente'] : '';
                                break;
                            }
                        }
                    ?>
                    <span class="font-sans text-[10px] font-bold text-blue-600 block uppercase">Asignado actualmente</span>
                    <?php if ($obraActual !== ''): ?>
                        <p class="text-xs font-sans font-bold text-slate-800"><?php echo htmlspecialchars($obraActual, ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-[10px] font-sans text-slate-400 mb-2"><?php echo htmlspecialchars($clienteActual, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php else: ?>
                        <p class="text-xs font-sans text-slate-400 italic mb-2">Obra no disponible</p>
                    <?php endif; ?>
                    <form method="post" action="controller/bath-unassign-drawer.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id_Bath" value="<?php echo (int) $editingBath->id; ?>">
                        <input type="hidden" name="submit_unassign_bath" value="1">
                        <button
                            type="submit"
                            data-confirm-unassign
                            data-confirm-title="¿Retirar baño <?php echo htmlspecialchars($editingBath->codigoBath, ENT_QUOTES, 'UTF-8'); ?>?"
                            data-confirm-text="Volverá a estar Disponible en bodega."
                            class="w-full py-2.5 bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors rounded-xl text-xs font-semibold font-sans"
                        >
                            Retirar baño de la obra
                        </button>
                    </form>
                <?php elseif ($editingBath->estadoBath === 1): ?>
                    <span class="font-sans text-[10px] font-bold text-emerald-600 block uppercase">Asignar a obra</span>
                    <form method="post" action="controller/bath-assign-drawer.php" class="space-y-2">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id_Bath" value="<?php echo (int) $editingBath->id; ?>">
                        <?php if (count($contratosActivos) > 0): ?>
                            <select
                                name="id_Contrato"
                                id="id_Contrato_assign"
                                class="dt-select w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                                data-enhanced-select
                                data-search-placeholder="Buscar obra..."
                                required
                            >
                                <option value="">Seleccione obra activa...</option>
                                <?php foreach ($contratosActivos as $con): ?>
                                    <option value="<?php echo (int) $con['id_Contrato']; ?>">
                                        <?php echo htmlspecialchars((string) $con['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars((string) $con['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button
                                type="submit"
                                name="submit_assign_bath"
                                class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-semibold font-sans transition-colors"
                            >
                                Confirmar Asignación
                            </button>
                        <?php else: ?>
                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-xl px-4 py-2.5 font-sans">
                                No hay contratos activos disponibles para asignar.
                            </p>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <p class="text-xs text-slate-400 font-sans italic">El baño debe estar Activo para poder asignarlo a una obra.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        (function () {
            var drawer = document.getElementById('bath-drawer');
            if (!drawer) return;
            drawer.style.transform = 'translateX(100%)';
            requestAnimationFrame(function () {
                drawer.style.transition = 'transform 300ms cubic-bezier(0.22, 1, 0.36, 1)';
                drawer.style.transform = 'translateX(0)';
            });

            var firstField = drawer.querySelector('input[name="codigo_Bath"]');
            if (firstField) {
                setTimeout(function () { firstField.focus(); }, 320);
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    var closeHref = '?page=<?php echo (int) $currentPage; ?><?php echo base_query_string(['action', 'id', 'assign', 'err']); ?>';
                    window.location = closeHref;
                }
            });
        })();
    </script>
<?php endif; ?>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/components/bathrooms.js"></script>

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
