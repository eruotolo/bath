<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Invoice\FindInvoiceWithCustomerAndContract;
use App\Application\Invoice\ListAssignedServices;
use App\Application\Invoice\ListUnbilledServicesForContract;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

include('layouts/config.php');
global $link;

$id_Factura = (int) $_GET['id_Factura'];
$id_Contrato = (int) $_GET['id_Contrato'];

$invoiceRepository = new MysqliInvoiceRepository($link);
$row = (new FindInvoiceWithCustomerAndContract($invoiceRepository))->handle($id_Factura, $id_Contrato);

if ($row !== null) {
    $serviciosDisponibles = (new ListUnbilledServicesForContract($invoiceRepository))->handle((int) $row['id_Cliente'], (int) $row['id_Contrato']);
    $serviciosAsignados = (new ListAssignedServices($invoiceRepository))->handle($id_Factura);
?>

<head>
    <title>Detalle de factura | Blanco Servicios - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="dt-page-title">
                    <h1>Detalle de Factura</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-invoices-list.php">Facturas</a></li>
                        <li class="active">Detalle de Factura</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-body py-8">

                        <div class="flex items-start pt-4 gap-4">
                            <div class="flex-1">
                                <div class="ml-12 mb-4">
                                    <img src="assets/images/logo_zl.png" alt="" height="120">
                                </div>
                            </div>
                            <div class="shrink-0">
                                <div class="mr-12 mb-4">
                                    <img src="assets/images/logo_rc.png" height="120" alt="Rata">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start gap-4">
                            <div class="flex-1 ml-12">
                                <p class="mb-1 font-sans">Blanco Servicios e Inversiones Spa</p>
                                <p class="mb-1 font-sans flex items-center gap-1"><i data-lucide="mail" class="!h-4 !w-4"></i> jsanchez@expanda.cl</p>
                                <p class="font-sans flex items-center gap-1"><i data-lucide="phone" class="!h-4 !w-4"></i> +56 9 9293 7371</p>
                            </div>
                            <div class="shrink-0 mr-12">
                                <div class="mb-4">
                                    <h4 class="text-right text-2xl font-bold">N° DE FACTURA: <?php echo htmlspecialchars($row['numero_Factura'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 border-slate-200">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-4">
                            <div>
                                <h5 class="font-sans text-base font-semibold mb-3">Factura a:</h5>
                                <h5 class="font-sans text-base mb-2"><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                <p class="mb-1 text-sm">RUT: <?php echo htmlspecialchars($row['rut_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mb-1 text-sm">Dir: <?php echo htmlspecialchars($row['direccion_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mb-1 text-sm">Email: <?php echo htmlspecialchars($row['email_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm">Teléfono: +<?php echo htmlspecialchars($row['telefono_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div>
                                <div>
                                    <h5 class="font-sans text-base font-semibold">Fecha de factura:</h5>
                                    <p class="font-sans text-sm"><?php echo date('d/m/Y', strtotime($row['fecha_Factura'])); ?></p>
                                </div>

                                <div class="mt-4">
                                    <h5 class="font-sans text-base font-semibold">Contrato:</h5>
                                    <p class="mb-1 text-sm"><?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-sm">Dir: <?php echo htmlspecialchars($row['direccion_Contrato'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="py-2 mt-6">
                            <h5 class="font-sans text-base font-semibold">Detalle de Factura:</h5>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4">
                            <div class="flex justify-end mb-3">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#nuevoServicio" data-bs-whatever="@fat" class="dt-btn-secondary">
                                    <i data-lucide="plus" class="!mr-1.5 !h-3.5 !w-3.5"></i> Agregar Servicios
                                </a>
                            </div>

                            <div class="modal fade modal-md" id="nuevoServicio" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Asignar Servicio a la Factura</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-5">
                                            <form action="controller/invoice-service-add.php" class="mt-4 pt-2" method="post" enctype="multipart/form-data">
                                                <input type="number" class="dt-input" id="id_Factura" name="id_Factura" value="<?php echo (int) $row['id_Factura']; ?>" hidden>
                                                <input type="number" class="dt-input" id="id_Contrato" name="id_Contrato" value="<?php echo (int) $row['id_Contrato']; ?>" hidden>

                                                <div class="mb-4">
                                                    <label for="id_Servicio" class="dt-label">Selecciona el servicio:</label>
                                                    <select name="id_Servicio" id="id_Servicio" class="dt-select" data-enhanced-select data-search-placeholder="Buscar servicio...">
                                                        <option value="">Selecciona un servicio</option>
                                                        <?php foreach ($serviciosDisponibles as $row1): ?>
                                                            <option value="<?php echo (int) $row1['id_Servicio']; ?>">
                                                                <?php echo date('d/m/Y', strtotime($row1['fecha_Servicio'])); ?> | N°: <?php echo (int) $row1['nro_Servicio']; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="flex justify-end">
                                                    <button class="dt-btn-add" type="submit" name="update">Asignar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto pt-4 pb-4">
                                <table class="table align-middle mb-0 w-full">
                                    <thead>
                                        <tr>
                                            <th>Número de Servicio</th>
                                            <th>Fecha de Servicio</th>
                                            <th>Observaciones</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($serviciosAsignados as $rows): ?>
                                            <tr>
                                                <td class="font-mono text-sm font-semibold text-slate-700"><?php echo (int) $rows['nro_Servicio']; ?></td>
                                                <td class="font-sans text-sm"><?php echo date('d/m/Y', strtotime($rows['fecha_Servicio'])); ?></td>
                                                <td class="font-sans text-sm"><?php echo htmlspecialchars($rows['observaciones_Servicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-center" style="width: 70px;">
                                                    <a href="controller/invoice-service-remove.php?id_Relacion=<?php echo (int) $rows['id_Relacion']; ?>&id_Factura=<?php echo (int) $rows['id_Factura']; ?>" class="dt-cell-action" title="Eliminar" aria-label="Eliminar servicio de la factura" data-confirm-delete data-confirm-title="¿Eliminar este servicio de la factura?" data-confirm-text="El servicio quedará liberado para volver a facturarse.">
                                                        <i data-lucide="trash-2" aria-hidden="true"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <hr class="my-4 border-slate-200">

                            <div class="flex justify-end">
                                <h4 class="text-xl font-bold"><b>Total:</b> $<?php echo number_format($row['valor_Factura'], 0, ',', '.'); ?></h4>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>

</body>

</html>

<?php
} else {
    echo '<script>alert("Problema al cargar la Factura")</script>';
}
?>
