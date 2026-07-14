<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bathroom\ListBathrooms;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;

include('layouts/config.php');

$useCase = new ListBathrooms(new MysqliBathroomRepository($link));
$listado = $useCase->handle();
?>

<head>
    <title>Listado de Baños | Blanco Servicios - Admin & Dashboard</title>
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
                        <h5 class="table-toolbar-title">Cantidad de Baños <span class="count">(<?php echo (int) $listado['total']; ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="table-toolbar-search"></div>
                            <a href="dash-bathrooms-add.php" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Nuevo Baño</a>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <div class="dt-filter-group" role="group" aria-label="Filtro por estado">
                            <button type="button" class="dt-filter-btn is-active" data-estado="all">Todos</button>
                            <button type="button" class="dt-filter-btn" data-estado="1">Activo</button>
                            <button type="button" class="dt-filter-btn" data-estado="0">Inactivo</button>
                        </div>
                        <div class="dt-filter-group" role="group" aria-label="Filtro por asignación">
                            <button type="button" class="dt-filter-btn is-active" data-asignado="all">Todos</button>
                            <button type="button" class="dt-filter-btn" data-asignado="1">Asignado</button>
                            <button type="button" class="dt-filter-btn" data-asignado="0">Disponible</button>
                        </div>
                    </div>

                    <div class="table-card mb-4">
                        <table id="datatable-buttons" class="table align-middle datatable dt-responsive nowrap w-100" data-dt-state="true">
                            <thead>
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Fecha de compra</th>
                                    <th scope="col">Observaciones</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Asignado a Obra</th>
                                    <th style="width: 80px; min-width: 80px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listado['items'] as $row): ?>
                                    <tr data-estado="<?php echo (int) $row['estado_Bath']; ?>" data-asignado="<?php echo (int) $row['asignado_Bath']; ?>">
                                        <td class="font-mono text-xs font-semibold text-slate-700"><?php echo htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['fechaCompra_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['observacion_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if ($row['estado_Bath'] == 1): ?>
                                                <span class="badge-status is-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge-status is-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['asignado_Bath'] == 0): ?>
                                                <span class="badge-status is-info">Disponible</span>
                                            <?php else: ?>
                                                <span class="badge-status is-warn">Asignado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="dash-bathrooms-edit.php?id_Bath=<?php echo (int) $row['id_Bath']; ?>" class="dt-cell-action" title="Editar">
                                                    <i data-lucide="square-pen"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                        <i data-lucide="more-horizontal"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="controller/bath-active.php?id_Bath=<?php echo (int) $row['id_Bath']; ?>"><i data-lucide="lock-open" class="!mr-2 !h-[14px] !w-[14px]"></i>Activar</a></li>
                                                        <li><a class="dropdown-item" href="controller/bath-inactive.php?id_Bath=<?php echo (int) $row['id_Bath']; ?>"><i data-lucide="lock" class="!mr-2 !h-[14px] !w-[14px]"></i>Inactivar</a></li>
                                                        <li><a class="dropdown-item" href="controller/bath-notassign.php?id_Bath=<?php echo (int) $row['id_Bath']; ?>"><i data-lucide="undo-2" class="!mr-2 !h-[14px] !w-[14px]"></i>Deshacer asignación</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="controller/bath-delete.php?id_Bath=<?php echo (int) $row['id_Bath']; ?>" data-confirm-delete data-confirm-title="¿Eliminar este baño?" data-confirm-text="Esta acción no se puede deshacer.">
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
        var table = DataTable.init('#datatable-buttons', {
            order: [[2, "desc"]],
            columnDefs: [{ targets: 2, type: 'date' }],
        });

        var filtroEstado = 'all';
        var filtroAsignado = 'all';

        $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex) {
            if (settings.nTable.id !== 'datatable-buttons') return true;
            var $row = $(table.row(dataIndex).node());
            if (filtroEstado !== 'all' && $row.data('estado').toString() !== filtroEstado) return false;
            if (filtroAsignado !== 'all' && $row.data('asignado').toString() !== filtroAsignado) return false;
            return true;
        });

        $('.filter-estado, [data-estado]').on('click', function () {
            $('.filter-estado, [data-estado]').removeClass('is-active');
            $(this).addClass('is-active');
            filtroEstado = $(this).data('estado').toString();
            table.draw();
        });

        $('.filter-asignado, [data-asignado]').on('click', function () {
            $('.filter-asignado, [data-asignado]').removeClass('is-active');
            $(this).addClass('is-active');
            filtroAsignado = $(this).data('asignado').toString();
            table.draw();
        });
    });
</script>

</body>
</html>
