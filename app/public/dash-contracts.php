<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Contract\ListContracts;
use App\Infrastructure\Persistence\MysqliContractRepository;

include('layouts/config.php');

$estado_Contrato_filtro = isset($_GET['estado']) ? (int) $_GET['estado'] : null;
$useCase = new ListContracts(new MysqliContractRepository($link));
$listado = $useCase->handle(in_array($estado_Contrato_filtro, [1, 2], true) ? $estado_Contrato_filtro : null);
?>

<head>
    <title>Contratos | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>

    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <?php
                    $titulo_listado = 'Listado de contratos';
                    if ($estado_Contrato_filtro === 2) {
                        $titulo_listado = 'Contratos Activos';
                    } elseif ($estado_Contrato_filtro === 1) {
                        $titulo_listado = 'Contratos Terminados';
                    }
                ?>

                <div class="space-y-4">
                    <div class="table-toolbar">
                        <h5 class="table-toolbar-title">Contratos <span class="count">(<?php echo (int) $listado['total']; ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="table-toolbar-search"></div>
                            <a href="dash-contracts-add.php" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Nuevo Contrato</a>
                        </div>
                    </div>

                    <div class="table-card mb-4">
                        <table id="datatable-buttons" class="table align-middle datatable dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Obra</th>
                                    <th scope="col" class="text-center">Estado</th>
                                    <th scope="col" class="text-center">Fecha de Inicio</th>
                                    <th scope="col" class="text-center">Fecha de Fin</th>
                                    <th scope="col" class="text-center">Valor Mensual</th>
                                    <th scope="col" class="text-center">Valor Total</th>
                                    <th style="width: 80px; min-width: 80px;" class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listado['items'] as $row): ?>
                                    <tr>
                                        <td class="font-sans text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center">
                                            <?php if ($row['estado_Contrato'] == 2): ?>
                                                <span class="badge-status is-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge-status is-info">Terminado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['fechaInicio_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['fechaFin_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center">$ <?php echo (int) $row['valorMensual_Contrato']; ?></td>
                                        <td class="text-center">$ <?php echo (int) $row['valorTotal_Contrato']; ?></td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="dash-contracts-edit.php?id_Contrato=<?php echo (int) $row['id_Contrato']; ?>" class="dt-cell-action" title="Editar">
                                                    <i data-lucide="square-pen"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                        <i data-lucide="more-horizontal"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="dash-contracts-item.php?id_Contrato=<?php echo (int) $row['id_Contrato']; ?>"><i data-lucide="bath" class="!mr-2 !h-[14px] !w-[14px]"></i>Agregar Baños Químicos</a></li>
                                                        <li><a class="dropdown-item" href="controller/contract-active.php?id_Contrato=<?php echo (int) $row['id_Contrato']; ?>"><i data-lucide="lock-open" class="!mr-2 !h-[14px] !w-[14px]"></i>Activar</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="controller/contract-inactive.php?id_Contrato=<?php echo (int) $row['id_Contrato']; ?>" data-confirm-delete data-confirm-title="¿Inactivar este contrato?" data-confirm-text="Los baños asignados quedarán disponibles para otros contratos."><i data-lucide="lock" class="!mr-2 !h-[14px] !w-[14px]"></i>Inactivar</a></li>
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
            buttons: [
                { extend: 'collection', text: 'Exportar', buttons: ['copy', 'excel', 'pdf'] },
                { extend: 'colvis', text: 'Visibilidad de columnas' },
            ],
        });
    });
</script>

</body>
</html>
