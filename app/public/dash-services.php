<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\ListServices;
use App\Infrastructure\Persistence\MysqliServiceRepository;

include('layouts/config.php');

$servicios = (new ListServices(new MysqliServiceRepository($link)))->handle();
?>

<head>
    <title>Listado de Servicios | Seguimientos | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="space-y-4">
                    <div class="table-toolbar">
                        <h5 class="table-toolbar-title">Cantidad de Servicios <span class="count">(<?php echo count($servicios); ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="table-toolbar-search"></div>
                            <a href="dash-services-add.php" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Nuevo Servicio</a>
                        </div>
                    </div>

                    <div class="table-card mb-4">
                        <table id="datatable-buttons" class="table align-middle datatable dt-responsive nowrap w-100" data-dt-state="true" data-dt-column-filters="true">
                            <thead>
                                <tr>
                                    <th scope="col">Número de Servicio</th>
                                    <th scope="col">Nombre Cliente</th>
                                    <th scope="col">Nombre de la Obra</th>
                                    <th scope="col">Factura</th>
                                    <th scope="col" class="text-center">Fecha Seguimiento</th>
                                    <th style="width:140px; min-width: 140px;" class="text-center no-filter">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $row): ?>
                                    <tr>
                                        <td class="font-mono text-sm font-semibold text-slate-700">#<?php echo (int) $row['nro_Servicio']; ?></td>
                                        <td class="font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if (!$row['facturado']): ?>
                                                <span class="badge-status is-warn">No Facturado</span>
                                            <?php else: ?>
                                                <span class="badge-status is-success">Facturado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['fecha_Servicio'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center" style="width: 140px">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="dash-services-edit.php?id_Servicio=<?php echo (int) $row['id_Servicio']; ?>" class="dt-cell-action" title="Editar">
                                                    <i data-lucide="square-pen"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                        <i data-lucide="more-horizontal"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="dash-services-bath.php?id_Servicio=<?php echo (int) $row['id_Servicio']; ?>"><i data-lucide="bath" class="!mr-2 !h-[14px] !w-[14px]"></i>Asignar Baños a Servicios</a></li>
                                                        <li><a class="dropdown-item" href="dash-services-print.php?id_Servicio=<?php echo (int) $row['id_Servicio']; ?>"><i data-lucide="printer" class="!mr-2 !h-[14px] !w-[14px]"></i>Imprimir</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="controller/service-inactive.php?id_Servicio=<?php echo (int) $row['id_Servicio']; ?>" data-confirm-delete data-confirm-title="¿Eliminar este servicio?" data-confirm-text="Esta acción no se puede deshacer.">
                                                                <i data-lucide="trash-2" class="!mr-2 !h-[14px] !w-[14px]"></i>Eliminar
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script src="assets/js/app.js"></script>
<script src="assets/js/components/datatable.js"></script>
<script>
    $(document).ready(function () {
        DataTable.init('#datatable-buttons', {
            order: [],
            columnDefs: [{ targets: 4, type: 'date' }],
        });
    });
</script>

</body>
</html>
