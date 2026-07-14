<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Contract\FindContract;
use App\Infrastructure\Persistence\MysqliContractRepository;

global $link;

include('layouts/config.php');

$id_Contrato = (int) $_GET['id_Contrato'];

$useCase = new FindContract(new MysqliContractRepository($link));
$contract = $useCase->handle($id_Contrato);

if ($contract !== null) {
?>

<head>
    <title>Editar Contrato | Blanco Servicios - Admin & Dashboard</title>
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
                    <h1>Editar Contrato</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-contracts.php">Contrato</a></li>
                        <li class="active">Editar Contrato</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Edición de Contrato</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form class="needs-validation" method="post" enctype="multipart/form-data" action="controller/contract-update.php">
                            <input type="text" class="dt-input" id="id_Contrato" name="id_Contrato" value="<?php echo (int) $contract->id; ?>" hidden>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="id_Cliente" class="dt-label">Nombre del Cliente</label>
                                    <select name="id_Cliente" id="id_Cliente" class="dt-select" data-enhanced-select data-search-placeholder="Buscar cliente...">
                                        <?php
                                        $sql = "SELECT * FROM clientes";
                                        $result = mysqli_query($link, $sql);
                                        $clientesOptions = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                        foreach ($clientesOptions as $cliente): ?>
                                            <option value="<?php echo (int) $cliente['id_Cliente']; ?>" <?php if ($cliente['id_Cliente'] == $contract->customerId) echo 'selected'; ?>><?php echo htmlspecialchars($cliente['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="obra_Contrato" class="dt-label">Nombre de la Obra</label>
                                    <input type="text" class="dt-input" id="obra_Contrato" name="obra_Contrato" value="<?php echo htmlspecialchars($contract->obra, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="direccion_Contrato" class="dt-label">Dirección de la Obra</label>
                                    <input type="text" class="dt-input" id="direccion_Contrato" name="direccion_Contrato" value="<?php echo htmlspecialchars($contract->address, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="mb-4">
                                    <label for="estado_Contrato" class="dt-label">Estado de la Obra</label>
                                    <select name="estado_Contrato" id="estado_Contrato" class="dt-select" data-enhanced-select>
                                        <option value="0" <?php if ($contract->state === 0) echo 'selected'; ?>>Eliminado</option>
                                        <option value="1" <?php if ($contract->state === 1) echo 'selected'; ?>>Terminado</option>
                                        <option value="2" <?php if ($contract->state === 2) echo 'selected'; ?>>Activo</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="fechaInicio_Contrato" class="dt-label">Fecha de Inicio</label>
                                    <input type="date" class="dt-input" id="fechaInicio_Contrato" name="fechaInicio_Contrato" value="<?php echo htmlspecialchars($contract->startDate, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="mb-4">
                                    <label for="fechaFin_Contrato" class="dt-label">Fecha de Fin</label>
                                    <input type="date" class="dt-input" id="fechaFin_Contrato" name="fechaFin_Contrato" value="<?php echo htmlspecialchars($contract->endDate, ENT_QUOTES, 'UTF-8'); ?>" data-datepicker-min-from="#fechaInicio_Contrato">
                                </div>

                                <div class="mb-4">
                                    <label for="valorMensual_Contrato" class="dt-label">Valor Mensual</label>
                                    <input type="number" class="dt-input text-end" id="valorMensual_Contrato" name="valorMensual_Contrato" value="<?php echo (int) $contract->monthlyValue; ?>">
                                </div>

                                <div class="mb-4">
                                    <label for="valorTotal_Contrato" class="dt-label">Valor Total</label>
                                    <input type="number" class="dt-input text-end" id="valorTotal_Contrato" name="valorTotal_Contrato" value="<?php echo (int) $contract->totalValue; ?>">
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="observacion_Contrato" class="dt-label">Observaciones</label>
                                    <textarea class="dt-input" id="observacion_Contrato" name="observacion_Contrato" rows="4"><?php echo htmlspecialchars($contract->observation ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button class="dt-btn-add" type="submit" name="update">Actualizar</button>
                            </div>
                        </form>

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
