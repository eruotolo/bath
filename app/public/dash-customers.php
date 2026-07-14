<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Customer\ListCustomers;
use App\Application\Customer\FindCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

global $link;

include('layouts/config.php');

$allowedSortBy = ['created_at', 'rut'];
$allowedSortDir = ['ASC', 'DESC'];
$sortBy = in_array($_GET['sort'] ?? '', $allowedSortBy, true) ? $_GET['sort'] : 'created_at';
$sortDir = in_array($_GET['dir'] ?? '', $allowedSortDir, true) ? $_GET['dir'] : 'DESC';

$useCase = new ListCustomers(new MysqliCustomerRepository($link));
$listado = $useCase->handle($sortBy, $sortDir);

$totalClientes = (int) $listado['total'];
$clientesAll = $listado['items'];

$perPage = 9;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalPages = (int) max(1, ceil($totalClientes / $perPage));
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
}

$offset = ($currentPage - 1) * $perPage;
$clientes = array_slice($clientesAll, $offset, $perPage);

$firstShown = $totalClientes > 0 ? $offset + 1 : 0;
$lastShown = $offset + count($clientes);

$drawerMode = null;
$editingCustomer = null;

if (($_GET['action'] ?? '') === 'new') {
    $drawerMode = 'new';
} elseif (($_GET['action'] ?? '') === 'edit' && isset($_GET['id'])) {
    $editingCustomer = (new FindCustomer(new MysqliCustomerRepository($link)))->handle((int) $_GET['id']);
    if ($editingCustomer !== null) {
        $drawerMode = 'edit';
    }
}

$drawerError = isset($_GET['err']) ? (string) $_GET['err'] : '';

$chileRegiones = [
    'Región de Arica y Parinacota',
    'Región de Tarapacá',
    'Región de Antofagasta',
    'Región de Atacama',
    'Región de Coquimbo',
    'Región de Valparaíso',
    'Región Metropolitana de Santiago',
    'Región de O\'Higgins',
    'Región del Maule',
    'Región de Ñuble',
    'Región del Biobío',
    'Región de la Araucanía',
    'Región de los Ríos',
    'Región de Los Lagos',
    'Región de Aysén',
    'Región de Magallanes',
];

$chileComunasLosLagos = [
    'Ancud', 'Castro', 'Chonchi', 'Curaco de Vélez', 'Dalcahue', 'Puqueldón',
    'Queilén', 'Quellón', 'Quemchi', 'Quinchao', 'Puerto Montt', 'Osorno',
];

function baseQueryString(array $excludes = ['page']): string {
    $params = [];
    foreach ($_GET as $k => $v) {
        if (!in_array($k, $excludes, true) && $v !== '' && $v !== null) {
            $params[] = $k . '=' . urlencode((string) $v);
        }
    }
    return $params ? '&' . implode('&', $params) : '';
}

function sortUrl(string $column, string $currentSort, string $currentDir): string {
    $newDir = ($column === $currentSort && $currentDir === 'ASC') ? 'DESC' : 'ASC';
    return '?sort=' . $column . '&dir=' . $newDir;
}

?>

<head>

    <title>Clientes | Blanco Servicios - Admin & Dashboard</title>

    <?php include 'layouts/head.php'; ?>

    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

        <div class="main-content">
            <div class="page-content">
            <div class="container-fluid px-10 py-10 bg-slate-50/50">

                <div class="space-y-4 max-w-full mx-auto">

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 mb-6">
                        <div class="relative flex-1 max-w-md">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input
                                type="text"
                                placeholder="Buscar por RUT, Nombre, Comuna..."
                                id="clientes-local-search"
                                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
                            />
                        </div>

                        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                            <a
                                href="controller/customer-export.php?format=csv"
                                id="export-customers-csv"
                                class="px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 bg-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 active:scale-95 transition-all shadow-sm"
                            >
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                <span>Excel</span>
                            </a>
                            <a
                                href="controller/customer-export.php?format=pdf"
                                id="export-customers-pdf"
                                class="px-3.5 py-2 border border-slate-200 hover:bg-slate-50 text-slate-700 bg-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 active:scale-95 transition-all shadow-sm"
                            >
                                <i data-lucide="file-down" class="w-3.5 h-3.5 text-rose-600 shrink-0"></i>
                                <span>PDF</span>
                            </a>
                            <a
                                href="?action=new<?php echo baseQueryString(['page', 'action']); ?>"
                                id="add-client-btn"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-sans text-xs font-semibold flex items-center space-x-1.5 shadow-lg shadow-indigo-600/10 transition-all active:scale-95"
                            >
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                <span>Agregar Nuevo Cliente</span>
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-50 bg-slate-50/50">
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">
                                            <a href="<?php echo sortUrl('rut', $sortBy, $sortDir); ?>" class="inline-flex items-center gap-1 hover:text-slate-600 transition-colors">
                                                <span>RUT</span>
                                                <?php if ($sortBy === 'rut'): ?>
                                                    <i data-lucide="<?php echo $sortDir === 'ASC' ? 'chevron-up' : 'chevron-down'; ?>" class="w-3 h-3 text-indigo-600"></i>
                                                <?php else: ?>
                                                    <i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-300"></i>
                                                <?php endif; ?>
                                            </a>
                                        </th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Nombre Cliente</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Contacto</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Ubicación</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Estado</th>
                                        <th class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach ($clientes as $customer): ?>
                                        <tr class="hover:bg-slate-50/75 transition-colors group">
                                            <td class="px-6 py-4.5 font-mono text-xs text-slate-500 font-semibold">
                                                <a href="dash-customers-item.php?id_Cliente=<?php echo (int) $customer->id; ?>" class="transition-colors hover:text-indigo-600">
                                                    <?php echo htmlspecialchars($customer->rut, ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4.5">
                                                <a href="dash-customers-item.php?id_Cliente=<?php echo (int) $customer->id; ?>" class="block">
                                                    <span class="font-sans font-bold text-slate-900 group-hover:text-indigo-600 transition-colors block text-sm">
                                                        <?php echo htmlspecialchars($customer->name, ENT_QUOTES, 'UTF-8'); ?>
                                                    </span>
                                                    <span class="font-sans text-[11px] text-slate-400 block mt-0.5">ID: <?php echo (int) $customer->id; ?></span>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4.5 space-y-1">
                                                <div class="flex items-center text-xs text-slate-600 font-sans">
                                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                                    <span><?php echo htmlspecialchars($customer->phone, ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                                <div class="flex items-center text-[11px] text-slate-500 font-sans">
                                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                                    <span class="truncate max-w-[180px]"><?php echo htmlspecialchars($customer->email, ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4.5">
                                                <div class="flex items-center text-xs text-slate-600 font-sans">
                                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                                    <span><?php echo htmlspecialchars($customer->commune, ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                                <span class="text-[11px] text-slate-400 block mt-0.5 ml-5 truncate max-w-[150px]"><?php echo htmlspecialchars($customer->city, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td class="px-6 py-4.5">
                                                <?php if ($customer->active): ?>
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold font-sans tracking-wide uppercase bg-emerald-50 text-emerald-700">
                                                        Activo
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold font-sans tracking-wide uppercase bg-slate-50 text-slate-600">
                                                        Inactivo
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4.5 text-right">
                                                <div class="inline-flex items-center gap-1">
                                                    <a
                                                        href="?action=edit&id=<?php echo (int) $customer->id; ?><?php echo baseQueryString(['page', 'action', 'id']); ?>"
                                                        class="p-1.5 rounded-lg border border-slate-100 text-slate-500 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50/40 transition-all inline-flex items-center justify-center"
                                                        title="Editar Cliente"
                                                        id="edit-client-btn-<?php echo (int) $customer->id; ?>"
                                                    >
                                                        <i data-lucide="square-pen" class="w-4 h-4"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($clientes) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                                No se encontraron clientes que coincidan con la búsqueda.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between">
                            <span class="font-mono text-[10px] text-slate-400 font-bold uppercase">Mostrando <?php echo $firstShown; ?>-<?php echo $lastShown; ?> de <?php echo $totalClientes; ?> Clientes</span>
                            <div class="flex items-center space-x-1">
                                <?php if ($currentPage > 1): ?>
                                    <a href="?page=<?php echo $currentPage - 1; ?>" class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 transition-colors hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200">Anterior</a>
                                <?php else: ?>
                                    <button class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 cursor-not-allowed" disabled>Anterior</button>
                                <?php endif; ?>
                                <a href="?page=1" class="px-3 py-1 rounded-lg border border-slate-100 text-xs font-semibold transition-colors <?php echo $currentPage === 1 ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200'; ?>">1</a>
                                <?php if ($currentPage < $totalPages): ?>
                                    <a href="?page=<?php echo $currentPage + 1; ?>" class="px-3 py-1 rounded-lg border border-slate-100 bg-white text-xs font-semibold text-slate-500 transition-colors hover:bg-slate-50 hover:text-indigo-600 hover:border-slate-200">Siguiente</a>
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
</div>

<?php if ($drawerMode === 'new' || $drawerMode === 'edit'): ?>
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='?page=<?php echo (int) $currentPage; ?><?php echo baseQueryString(['action', 'id', 'err']); ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0" id="customer-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl <?php echo $drawerMode === 'edit' ? 'bg-indigo-100 text-indigo-700' : 'bg-indigo-600 text-white'; ?> flex items-center justify-center <?php echo $drawerMode === 'edit' ? '' : 'shadow-lg shadow-indigo-600/10'; ?>">
                    <i data-lucide="<?php echo $drawerMode === 'edit' ? 'square-pen' : 'user-plus'; ?>" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm">
                        <?php if ($drawerMode === 'edit'): ?>
                            Editar Cliente
                        <?php else: ?>
                            Registro de Nuevo Cliente
                        <?php endif; ?>
                    </h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5">
                        <?php if ($drawerMode === 'edit'): ?>
                            <?php echo htmlspecialchars($editingCustomer->name, ENT_QUOTES, 'UTF-8'); ?>
                        <?php else: ?>
                            Ingresar los datos en los campos obligatorios.
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <a href="?page=<?php echo (int) $currentPage; ?><?php echo baseQueryString(['action', 'id', 'err']); ?>" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all" aria-label="Cerrar panel">
                <i data-lucide="x" class="w-5 h-5"></i>
            </a>
        </div>

        <?php if ($drawerError !== ''): ?>
            <div class="px-6 py-3 bg-rose-50 border-b border-rose-100 text-rose-700 font-sans text-xs">
                <?php echo htmlspecialchars($drawerError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form
            method="post"
            action="controller/<?php echo $drawerMode === 'edit' ? 'customer-edit-drawer.php' : 'customer-create-drawer.php'; ?>"
            class="flex-1 overflow-y-auto p-6 space-y-5"
            id="customer-drawer-form"
        >
            <?php if ($drawerMode === 'edit'): ?>
                <input type="hidden" name="id_Cliente" value="<?php echo (int) $editingCustomer->id; ?>">
            <?php endif; ?>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">RUT del Cliente <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="rut_Cliente"
                    placeholder="e.g. 77.123.456-7"
                    value="<?php echo htmlspecialchars($drawerMode === 'edit' ? (string) $editingCustomer->rut : '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-mono"
                    required
                    data-rut-mask
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Nombre Completo / Razón Social <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="nombre_Cliente"
                    placeholder="e.g. CONSTRUCTORA PUERTO OCTAY LTDA"
                    value="<?php echo htmlspecialchars($drawerMode === 'edit' ? (string) $editingCustomer->name : '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                    required
                >
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Teléfono de Contacto</label>
                    <input
                        type="tel"
                        name="telefono_Cliente"
                        placeholder="e.g. +56 9 9876 5432"
                        value="<?php echo htmlspecialchars($drawerMode === 'edit' ? (string) $editingCustomer->phone : '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                    >
                </div>
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Correo Electrónico <span class="text-rose-500">*</span></label>
                    <input
                        type="email"
                        name="email_Cliente"
                        placeholder="e.g. contacto@empresa.cl"
                        value="<?php echo htmlspecialchars($drawerMode === 'edit' ? (string) $editingCustomer->email : '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                        required
                    >
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Dirección Particular / Casa Matriz</label>
                <input
                    type="text"
                    name="direccion_Cliente"
                    placeholder="e.g. Sector Chulchuy Ruta W-85"
                    value="<?php echo htmlspecialchars($drawerMode === 'edit' ? (string) $editingCustomer->address : '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Región</label>
                <select
                    name="region_Cliente"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                >
                    <?php foreach ($chileRegiones as $r): ?>
                        <option value="<?php echo $r; ?>" <?php echo ($drawerMode === 'edit' && $editingCustomer->region === $r) ? 'selected' : ''; ?>>
                            <?php echo $r; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Ciudad</label>
                    <input
                        type="text"
                        name="ciudad_Cliente"
                        placeholder="e.g. Castro"
                        value="<?php echo htmlspecialchars($drawerMode === 'edit' ? (string) $editingCustomer->city : '', ENT_QUOTES, 'UTF-8'); ?>"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 transition-all font-sans"
                    >
                </div>
                <div class="space-y-1.5">
                    <label class="font-sans text-xs font-bold text-slate-600 block">Comuna (Los Lagos)</label>
                    <select
                        name="comuna_Cliente"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-emerald-500 transition-all font-sans"
                    >
                        <?php foreach ($chileComunasLosLagos as $com): ?>
                            <option value="<?php echo $com; ?>" <?php echo ($drawerMode === 'edit' && $editingCustomer->commune === $com) ? 'selected' : ''; ?>>
                                <?php echo $com; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center space-x-3">
                <a
                    href="?page=<?php echo (int) $currentPage; ?><?php echo baseQueryString(['action', 'id', 'err']); ?>"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    name="<?php echo $drawerMode === 'edit' ? 'submit_edit_client' : 'submit_new_client'; ?>"
                    id="<?php echo $drawerMode === 'edit' ? 'submit-edit-client' : 'submit-new-client'; ?>"
                    class="flex-1 py-2.5 <?php echo $drawerMode === 'edit' ? 'bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/15' : 'bg-emerald-500 hover:bg-emerald-600 shadow-lg shadow-emerald-500/10'; ?> text-white rounded-xl text-xs font-semibold font-sans transition-colors"
                >
                    <?php echo $drawerMode === 'edit' ? 'Guardar Cambios' : 'Registrar Cliente'; ?>
                </button>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var drawer = document.getElementById('customer-drawer');
            if (!drawer) return;
            drawer.style.transform = 'translateX(100%)';
            requestAnimationFrame(function () {
                drawer.style.transition = 'transform 300ms cubic-bezier(0.22, 1, 0.36, 1)';
                drawer.style.transform = 'translateX(0)';
            });

            var firstField = drawer.querySelector('input[name="rut_Cliente"]');
            if (firstField) {
                setTimeout(function () { firstField.focus(); }, 320);
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    var closeHref = '?page=<?php echo (int) $currentPage; ?><?php echo baseQueryString(['action', 'id', 'err']); ?>';
                    window.location = closeHref;
                }
            });
        })();
    </script>
<?php endif; ?>

<?php include 'layouts/vendor-scripts.php'; ?>

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
