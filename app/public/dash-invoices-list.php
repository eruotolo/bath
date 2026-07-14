<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Invoice\ListInvoices;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include('layouts/config.php');
include('layouts/helpers.php');

$facturas = (new ListInvoices(new MysqliInvoiceRepository($link)))->handle();
?>

<head>
    <title>Facturas | Blanco Servicios - Admin & Dashboard</title>
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
                        <h5 class="table-toolbar-title">Facturas <span class="count">(<?php echo count($facturas); ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="dt-range-picker input-group datepicker-range" style="width: auto;">
                                <input type="text" class="dt-input flatpickr-input" data-input aria-describedby="date1">
                                <button class="dt-input-suffix" id="date1" data-toggle><i data-lucide="calendar" class="!h-4 !w-4"></i></button>
                            </div>
                            <div class="table-toolbar-search"></div>
                            <a href="dash-invoices-add.php" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Nueva Factura</a>
                        </div>
                    </div>

                    <div class="table-card mb-4">
                        <table id="datatable-buttons" class="table align-middle datatable dt-responsive nowrap w-100" data-dt-state="true" data-dt-column-filters="true">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Nro. Factura</th>
                                    <th style="text-align: center">Fecha Factura</th>
                                    <th>Cliente</th>
                                    <th>Obra</th>
                                    <th style="text-align: center">Monto Factura</th>
                                    <th style="text-align: center">Estado</th>
                                    <th style="text-align: center" class="no-filter">Fecha de Pago</th>
                                    <th style="width: 90px;" class="no-filter">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facturas as $row): ?>
                                    <tr>
                                        <td class="font-mono text-sm font-semibold text-slate-700">#<?php echo htmlspecialchars($row['numero_Factura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($row['fecha_Factura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['obra_Contrato'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center font-sans text-sm font-semibold text-slate-700"><?php echo format_clp($row['valor_Factura']); ?></td>
                                        <td class="text-center">
                                            <?php if ($row['estado_Factura'] == 1): ?>
                                                <span class="badge-status is-warn">Pendiente</span>
                                            <?php elseif ($row['estado_Factura'] == 2): ?>
                                                <span class="badge-status is-success">Pagado</span>
                                            <?php else: ?>
                                                <span class="badge-status is-danger">Anulado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <?php if ($row['fecha_Pago']): ?>
                                                    <span class="font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['fecha_Pago'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php else: ?>
                                                    <span class="font-sans text-sm text-slate-400">Sin definir</span>
                                                <?php endif; ?>
                                                <button type="button" class="dt-cell-action btn-fecha-pago" data-bs-toggle="modal" data-bs-target="#modalFechaPago" data-id-factura="<?php echo (int) $row['id_Factura']; ?>" data-fecha-pago="<?php echo htmlspecialchars($row['fecha_Pago'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" title="Editar Fecha de Pago">
                                                    <i data-lucide="square-pen"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="dash-invoices-edit.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>" class="dt-cell-action" title="Editar Factura">
                                                    <i data-lucide="square-pen"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                        <i data-lucide="more-horizontal"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="controller/invoice-pdf.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&id_Contrato=<?php echo (int) $row['id_Contrato']; ?>" data-glightbox-preview data-type="external" data-width="900px" data-height="90vh"><i data-lucide="printer" class="!mr-2 !h-[14px] !w-[14px]"></i>Imprimir</a></li>
                                                        <li><a class="dropdown-item" href="dash-invoices-detail.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&id_Contrato=<?php echo (int) $row['id_Contrato']; ?>"><i data-lucide="plus" class="!mr-2 !h-[14px] !w-[14px]"></i>Agregar Servicios a la Factura</a></li>
                                                        <li>
                                                            <a class="dropdown-item" href="controller/invoice-estado.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&estado_Factura=1">Marcar Pendiente</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="controller/invoice-estado.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&estado_Factura=2">Marcar Pagado</a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="controller/invoice-estado.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>&estado_Factura=3">Marcar Anulado</a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="controller/invoice-delete.php?id_Factura=<?php echo (int) $row['id_Factura']; ?>" data-confirm-delete data-confirm-title="¿Anular esta factura?" data-confirm-text="Los servicios asociados quedarán liberados para volver a facturarse." data-confirm-confirm-text="Sí, anular">
                                                                <i data-lucide="trash-2" class="!mr-2 !h-[14px] !w-[14px]"></i>Anular
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

                    <!-- MODAL EDITAR FECHA DE PAGO -->
                    <div class="modal fade" id="modalFechaPago" tabindex="-1" aria-labelledby="modalFechaPagoLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalFechaPagoLabel">Editar Fecha de Pago</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="controller/invoice-fecha-pago.php" method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="id_Factura" id="modalFechaPagoIdFactura" value="">
                                        <div class="mb-3">
                                            <label for="modalFechaPagoInput" class="dt-label">Fecha de Pago</label>
                                            <input type="date" class="dt-input" name="fecha_Pago" id="modalFechaPagoInput">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="dt-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="dt-btn-add">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
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
            columnDefs: [{ targets: 1, type: 'date' }],
        });

        $('#modalFechaPago').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            $('#modalFechaPagoIdFactura').val(button.data('id-factura'));
            $('#modalFechaPagoInput').val(button.data('fecha-pago'));
        });
    });
</script>

</body>
</html>
