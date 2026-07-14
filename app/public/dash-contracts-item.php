<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Contract\FindContractWithCustomer;
use App\Application\Bathroom\ListBathroomsByContract;
use App\Infrastructure\Persistence\MysqliContractRepository;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;

include('layouts/config.php');

$id_Contrato = (int) $_GET['id_Contrato'];

$contrato = (new FindContractWithCustomer(new MysqliContractRepository($link)))->handle($id_Contrato);

if ($contrato !== null) {
    $banos = (new ListBathroomsByContract(new MysqliBathroomRepository($link)))->handle($id_Contrato);
?>

<head>
    <title>Item Contrato | Blanco Servicios - Admin & Dashboard</title>
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
                    <h1>Item Contrato</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-contracts.php">Contrato</a></li>
                        <li class="active">Item Contrato</li>
                    </ol>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Informacion del Contrato -->
                    <div class="dt-card">
                        <div class="dt-card-header">
                            <h5 class="dt-card-title">Información del Contrato</h5>
                            <p class="dt-card-desc">Datos relevantes al contrato</p>
                        </div>
                        <div class="dt-card-body py-6">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-3">
                                <label class="dt-label sm:col-span-1 self-center !mb-0">Id Contrato:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo (int) $contrato['id_Contrato']; ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Nombre Cliente:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo htmlspecialchars($contrato['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Nombre de la Obra:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo htmlspecialchars($contrato['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Dirección de la Obra:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo htmlspecialchars($contrato['direccion_Contrato'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Fecha de Inicio de la Obra:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo htmlspecialchars($contrato['fechaInicio_Contrato'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Fecha de Fin de la Obra:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo htmlspecialchars($contrato['fechaFin_Contrato'], ENT_QUOTES, 'UTF-8'); ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Valor Mensual:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo (int) $contrato['valorMensual_Contrato']; ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-center !mb-0">Valor Total:</label>
                                <input type="text" class="dt-input sm:col-span-2" value="<?php echo (int) $contrato['valorTotal_Contrato']; ?>" readonly>

                                <label class="dt-label sm:col-span-1 self-start pt-3">Observaciones:</label>
                                <textarea class="dt-input sm:col-span-2" rows="6" readonly><?php echo htmlspecialchars($contrato['observacion_Contrato'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Baños de la Obra -->
                    <div class="dt-card">
                        <div class="dt-card-header flex items-start justify-between gap-4">
                            <div>
                                <h5 class="dt-card-title">Baños de la Obra</h5>
                                <p class="dt-card-desc">Baños relacionados a la Obra</p>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#nuevoAssign" data-bs-whatever="@fat" class="dt-btn-secondary">
                                <i data-lucide="plus" class="!mr-1.5 !h-3.5 !w-3.5"></i> Agregar Nuevo Baño
                            </a>
                        </div>
                        <?php include 'layouts/modal-nuevo-assign-bath.php'; ?>

                        <div class="dt-card-body p-0">
                            <?php if (empty($banos)): ?>
                                <p class="m-0 p-6 text-center text-sm text-slate-500">Sin baños asignados a esta obra.</p>
                            <?php else: ?>
                                <table class="w-full border-collapse text-left">
                                    <thead>
                                        <tr>
                                            <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Id</th>
                                            <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Código Baño</th>
                                            <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Fecha de compra</th>
                                            <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Asignado</th>
                                            <th class="border-b border-slate-100 px-6 py-4 font-mono text-[10px] font-bold uppercase tracking-wider text-slate-400">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($banos as $bano): ?>
                                            <tr>
                                                <td class="border-b border-slate-100 px-6 py-4 font-mono text-xs text-slate-700"><?php echo (int) $bano['id_Relacion']; ?></td>
                                                <td class="border-b border-slate-100 px-6 py-4 font-mono text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($bano['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="border-b border-slate-100 px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($bano['fechaCompra_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="border-b border-slate-100 px-6 py-4">
                                                    <?php if ($bano['asignado_Bath'] == 1): ?>
                                                        <span class="item-activo inline-block rounded-full px-2 py-0.5">Asignado</span>
                                                    <?php else: ?>
                                                        <span class="item-disponible inline-block rounded-full px-2 py-0.5">Disponible</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="border-b border-slate-100 px-6 py-4 text-center" style="width: 70px;">
                                                    <a href="controller/contract-bath-notassign.php?id_Contrato=<?php echo (int) $bano['id_Contrato']; ?>&id_Bath=<?php echo (int) $bano['id_Bath']; ?>&id_Relacion=<?php echo (int) $bano['id_Relacion']; ?>" class="dt-cell-action delete-contacto" title="No Asignar">
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
    echo '<script>alert("Problema al cargar el Contrato")</script>';
}
?>
