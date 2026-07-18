<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Application\Certificate\ListCertificates;
use App\Application\Customer\ListCustomers;
use App\Infrastructure\Persistence\MysqliCertificateRepository;
use App\Infrastructure\Persistence\MysqliCustomerRepository;

global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php');

$certificates = (new ListCertificates(new MysqliCertificateRepository($link)))->handle();

// --- Drawer Nuevo Certificado (?action=new) — calco de dash-services.php ---
// Certificados SOLO tiene modo 'new' (no existe edicion: el repositorio no expone update).
$drawerAction = $_GET['action'] ?? '';
$drawerMode = ($drawerAction === 'new') ? 'new' : null;
$drawerError = isset($_GET['err']) ? (string) $_GET['err'] : '';
$closeDrawerUrl = 'dash-certificates.php';

$clientesDrawer = [];
if ($drawerMode === 'new') {
    $clientesDrawer = (new ListCustomers(new MysqliCustomerRepository($link)))->handle('rut', 'ASC')['items'];
}
?>

<head>
    <title>Certificados | Blanco Servicios - Admin & Dashboard</title>
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
                    <!-- Toolbar: título + buscador (native-table.js) + Agregar -->
                    <div class="table-toolbar">
                        <h5 class="table-toolbar-title">Certificados <span class="count">(<?php echo (int) $certificates['total']; ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="table-toolbar-search">
                                <div class="relative">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                    <input
                                        type="text"
                                        id="tabla-certificados-search"
                                        data-table-search-input="#tabla-certificados"
                                        placeholder="Buscar por cliente, obra, número..."
                                        class="w-full sm:w-64 pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
                                    >
                                </div>
                            </div>
                            <a href="?action=new" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Nuevo Certificado</a>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-table-native-wrap>
                        <div class="overflow-x-auto">
                            <table id="tabla-certificados" class="w-full text-left border-collapse" data-per-page="9" data-item-label="Certificados">
                                <thead>
                                    <tr class="border-b border-slate-50 bg-slate-50/50">
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Nro. Certificado</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Cliente</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">RUT Cliente</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Obra</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Fecha del Servicio</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach ($certificates['items'] as $row): ?>
                                        <?php
                                        $searchable = htmlspecialchars(
                                            '#' . $row['certificado'] . ' ' .
                                            $row['nombre_Cliente'] . ' ' .
                                            $row['rut_Cliente'] . ' ' .
                                            $row['obra_Contrato'] . ' ' .
                                            $row['fecha_Servicio'],
                                            ENT_QUOTES, 'UTF-8'
                                        );
                                        ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors group" data-search="<?php echo $searchable; ?>">
                                            <td class="px-6 py-4 font-mono text-sm font-semibold text-slate-700">#<?php echo htmlspecialchars($row['certificado'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-mono text-xs text-slate-500"><?php echo htmlspecialchars($row['rut_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['fecha_Servicio'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <div class="dropdown">
                                                        <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                            <i data-lucide="more-horizontal"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end m-0 min-w-[220px] list-none overflow-hidden rounded-2xl border border-slate-100 bg-white p-2 shadow-xl shadow-slate-200/50">
                                                            <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="controller/certificate-pdf.php?id_Certificado=<?php echo (int) $row['id_Certificado']; ?>&id_Contrato=<?php echo (int) $row['id_Contrato']; ?>" data-glightbox-preview data-type="external" data-width="900px" data-height="90vh"><i data-lucide="printer" class="!h-[14px] !w-[14px] shrink-0"></i>Imprimir</a></li>
                                                            <li><hr class="dropdown-divider m-1 border-slate-100"></li>
                                                            <li>
                                                                <a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-rose-500 hover:bg-rose-50 hover:text-rose-500" href="controller/certificate-remove.php?id_Certificado=<?php echo (int) $row['id_Certificado']; ?>" data-confirm-delete data-confirm-title="¿Eliminar este certificado?" data-confirm-text="Esta acción no se puede deshacer.">
                                                                    <i data-lucide="trash-2" class="!h-[14px] !w-[14px] shrink-0"></i>Eliminar
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($certificates['items']) === 0): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                                No hay certificados registrados.
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
    <!-- Drawer Nuevo Certificado (#certificate-drawer) — calco de dash-services.php:425-459 -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='<?php echo $closeDrawerUrl; ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0 app-drawer" id="certificate-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/10">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm">Registrar Nuevo Certificado</h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5">Emitir certificado asociado a un contrato.</span>
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

        <form method="post" action="controller/certificate-new.php" class="flex-1 overflow-y-auto p-6 space-y-4" id="certificate-drawer-form">
            <input type="hidden" name="fechahoy_Certificado" value="<?php echo date('Y-m-d'); ?>">

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Cliente <span class="text-rose-500">*</span></label>
                <select
                    name="id_Cliente"
                    id="id_Cliente"
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
                    id="id_Contrato"
                    class="dt-select w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    data-enhanced-select
                    data-search-placeholder="Buscar contrato..."
                    required
                >
                    <option value="">Seleccione un Contrato / Obra...</option>
                    <!-- Las opciones se cargan dinámicamente vía cascade (cliente -> contrato) -->
                </select>
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Metros Cúbicos <span class="text-rose-500">*</span></label>
                <input
                    type="number"
                    name="mts_Certificado"
                    id="mts_Certificado"
                    placeholder="Ingrese los metros cúbicos"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    required
                >
            </div>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Fecha del Servicio <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="fecha_Servicio"
                    id="fecha_Servicio"
                    placeholder="Ingrese la fecha del servicio"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    required
                >
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
                    name="crear"
                    class="flex-1 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-xs font-semibold font-sans transition-all shadow-lg shadow-indigo-500/10"
                >
                    Crear Certificado
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>
<script src="assets/js/components/native-table.js"></script>

<?php if ($drawerMode !== null): ?>
    <script>
        // Animación del drawer (calco de dash-services.php:635-654): translateX + Escape cierra
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
        // Cascade cliente -> contrato (calco de dash-certificates-add.php:92-100)
        $(document).ready(function () {
            SelectEnhanced.cascade({
                parent: 'id_Cliente',
                child: 'id_Contrato',
                endpoint: 'controller/obtener_contratos.php',
                paramName: 'idCliente',
            });
        });
    </script>
<?php endif; ?>

<?php if (($_GET['flash'] ?? '') !== ''): ?>
    <script>
        // Toast flash (calco de dash-services.php:743-768) — feedback tras crear/editar
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
