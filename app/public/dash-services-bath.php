<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\FindServiceWithContractAndCustomer;
use App\Application\Service\ListAssignedBathrooms;
use App\Application\Bathroom\ListBathroomsByContract;
use App\Infrastructure\Persistence\MysqliServiceRepository;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

include('layouts/config.php');
global $link;

$id_Servicio = (int) $_GET['id_Servicio'];

$row = (new FindServiceWithContractAndCustomer(new MysqliServiceRepository($link)))->handle($id_Servicio);

if ($row !== null) {
    $banosDelContrato = (new ListBathroomsByContract(new MysqliBathroomRepository($link)))->handle((int) $row['id_Contrato']);
    $banosAsignados = (new ListAssignedBathrooms(new MysqliServiceRepository($link)))->handle($id_Servicio);
?>

<head>
    <title>Item Servicio Agregar Baños | Blanco Servicios - Admin & Dashboard</title>
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
                    <h1>Item Servicios</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-services.php">Servicios</a></li>
                        <li class="active">Item Servicios</li>
                    </ol>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <div class="dt-card">
                        <div class="dt-card-header">
                            <h5 class="dt-card-title">Información del Servicio</h5>
                            <p class="dt-card-desc">Datos relevantes al servicio</p>
                        </div>
                        <div class="dt-card-body py-6">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-3">
                                <label class="dt-label sm:col-span-1 self-center !mb-0">Número de Servicio:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo (int) $row['nro_Servicio']; ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Nombre del Cliente:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Obra:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Dirección de la Obra:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo htmlspecialchars($row['direccion_Contrato'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Fecha de Inicio de la Obra:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo date('d/m/Y', strtotime($row['fechaInicio_Contrato'])); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Fecha de Fin de la Obra:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo date('d/m/Y', strtotime($row['fechaFin_Contrato'])); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-start pt-3">Servicio Realizado:</label>
                                <div class="sm:col-span-2 pt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                                    <ul class="m-0 list-none space-y-1 text-slate-700">
                                        <?php
                                        $tipos = [
                                            'instalacion_Tipo' => 'Instalación',
                                            'reparacion_Tipo' => 'Reparación',
                                            'limpieza_Tipo' => 'Limpieza',
                                            'desinfeccion_Tipo' => 'Desinfección',
                                            'sanitizacion_Tipo' => 'Sanitización',
                                            'higienico_Tipo' => 'Entrega Papel Higiénico',
                                            'jabon_Tipo' => 'Entrega de Jabón Liquido',
                                            'otros_Tipo' => 'Otros Servicios',
                                        ];
                                        foreach ($tipos as $field => $label):
                                            if (isset($row[$field]) && $row[$field] == 1): ?>
                                                <li><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endif;
                                        endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dt-card">
                        <div class="dt-card-header flex items-start justify-between gap-4">
                            <div>
                                <h5 class="dt-card-title">Baños de la Obra</h5>
                                <p class="dt-card-desc">Baños relacionados a la Obra</p>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#assignBath" data-bs-whatever="@fat" class="dt-btn-secondary">
                                <i data-lucide="plus" class="!mr-1.5 !h-3.5 !w-3.5"></i> Agregar Nuevo Baño
                            </a>
                        </div>

                        <!-- MODAL AGREGAR BATH -->
                        <div class="modal fade modal-md" id="assignBath" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Asignar Baño Al Servicio</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body py-5">
                                        <form action="controller/service-bath-add.php" class="mt-4 pt-2" method="post" enctype="multipart/form-data">
                                            <input type="number" class="dt-input" id="id_Servicio" name="id_Servicio" value="<?php echo (int) $row['id_Servicio']; ?>" hidden>

                                            <div class="mb-4">
                                                <label class="dt-label">Seleccionar el/los baño/s:</label>
                                                <div class="space-y-2 mt-2 rounded-lg border border-slate-200 p-3">
                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <input type="checkbox" id="checkTodos" class="!h-4 !w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                        <span class="font-sans text-xs font-bold uppercase tracking-wider text-slate-700">TODOS LOS BAÑOS</span>
                                                    </label>
                                                    <?php foreach ($banosDelContrato as $bano): ?>
                                                        <label class="flex items-center gap-2 cursor-pointer" for="bath_<?php echo (int) $bano['id_Bath']; ?>">
                                                            <input type="checkbox" name="id_Bath[]" value="<?php echo (int) $bano['id_Bath']; ?>" id="bath_<?php echo (int) $bano['id_Bath']; ?>" class="!h-4 !w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                            <span class="font-mono text-sm text-slate-700"><?php echo htmlspecialchars($bano['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                                <script>
                                                    var checkTodos = document.getElementById('checkTodos');
                                                    var checkboxes = document.querySelectorAll('input[name="id_Bath[]"]');
                                                    if (checkTodos && checkboxes.length > 0) {
                                                        checkTodos.addEventListener('change', function () {
                                                            checkboxes.forEach(function (cb) { cb.checked = checkTodos.checked; });
                                                        });
                                                    }
                                                </script>
                                            </div>

                                            <div class="flex justify-end">
                                                <button class="dt-btn-add" type="submit" name="update">Asignar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dt-card-body p-0">
                            <?php if (empty($banosAsignados)): ?>
                                <p class="m-0 p-6 text-center text-sm text-slate-500">Sin baños asignados a este servicio.</p>
                            <?php else: ?>
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr>
                                            <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Código Baño</th>
                                            <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Fecha de compra</th>
                                            <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($banosAsignados as $asignado): ?>
                                            <tr>
                                                <td class="border-b border-slate-100 px-6 py-4 font-mono text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($asignado['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="border-b border-slate-100 px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($asignado['fechaCompra_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="border-b border-slate-100 px-6 py-4 text-center" style="width: 70px;">
                                                    <a href="controller/service-bath-remove.php?id_Relacion=<?php echo (int) $asignado['id_Relacion']; ?>&id_Servicio=<?php echo (int) $asignado['id_Servicio']; ?>" class="dt-cell-action" title="Eliminar">
                                                        <i data-lucide="trash-2"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
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
    echo '<script>alert("Problema al cargar el Servicio")</script>';
}
?>
