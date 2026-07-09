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

    <title>Item Contrato | Blanco Servicios - Admin & Dashboard</title>

    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Item Contrato</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-contracts.php">Contrato</a></li>
                                    <li class="breadcrumb-item active">Item Contrato</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header">
                                <div class="col-6">
                                    <h4 class="card-title">Formulario de Edición de Contrato</h4>
                                    <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                                </div>
                            </div>

                            <div class="card-body py-4 d-flex justify-content-center">
                                <div class="col-12">
                                    <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                    <form class="needs-validation" method="post" enctype="multipart/form-data" style="margin-top: 50px; margin-left: 15%" action="controller/contract-update.php">

                                        <input type="text" class="form-control" id="id_Contrato" name="id_Contrato" value="<?php echo $contract->id;?>" hidden>

                                        <input type="text" class="form-control" id="id_Cliente" name="id_Cliente" value="<?php echo $contract->customerId;?>" hidden>

                                        <div class="row mb-4">
                                            <label for="id_Cliente" class="col-sm-4 col-form-label">Nombre del Cliente:</label>
                                            <div class="col-sm-5">
                                                <select name="id_Cliente" id="id_Cliente" class="form-select" data-enhanced-select data-search-placeholder="Buscar cliente...">
                                                    <?php
                                                    $sql = "SELECT * From clientes";
                                                    $result = mysqli_query($link, $sql);
                                                    $clientesOptions = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                                    foreach ($clientesOptions as $cliente){ ?>
                                                        <option value="<?php echo $cliente['id_Cliente']; ?>" <?php if ($cliente['id_Cliente'] == $contract->customerId){ echo 'selected'; } ?>><?php echo htmlspecialchars($cliente['nombre_Cliente']); ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="obra_Contrato" class="col-sm-4 col-form-label">Nombre de la Obra:</label>
                                            <div class="col-sm-5">
                                                <input type="text" class="form-control" id="obra_Contrato" name="obra_Contrato" value="<?php echo htmlspecialchars($contract->obra);?>">
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="direccion_Contrato" class="col-sm-4 col-form-label">Dirección de la Obra:</label>
                                            <div class="col-sm-5">
                                                <input type="text" class="form-control" id="direccion_Contrato" name="direccion_Contrato" value="<?php echo htmlspecialchars($contract->address);?>">
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="estado_Contrato" class="col-sm-4 col-form-label">Estado de la Obra:</label>
                                            <div class="col-sm-5">
                                                <select name="estado_Contrato" id="estado_Contrato" class="form-select" data-enhanced-select>
                                                    <option value="0" <?php if ($contract->state === 0) echo 'selected'; ?>>Eliminado</option>
                                                    <option value="1" <?php if ($contract->state === 1) echo 'selected'; ?>>Terminado</option>
                                                    <option value="2" <?php if ($contract->state === 2) echo 'selected'; ?>>Activo</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="fechaInicio_Contrato" class="col-sm-4 col-form-label">Fecha de Inicio de la Obra:</label>
                                            <div class="col-sm-5">
                                                <input type="date" class="form-control" id="fechaInicio_Contrato" name="fechaInicio_Contrato" value="<?php echo htmlspecialchars($contract->startDate);?>">
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="fechaFin_Contrato" class="col-sm-4 col-form-label">Fecha de Fin de la Obra:</label>
                                            <div class="col-sm-5">
                                                <input type="date" class="form-control" id="fechaFin_Contrato" name="fechaFin_Contrato" value="<?php echo htmlspecialchars($contract->endDate);?>" data-datepicker-min-from="#fechaInicio_Contrato">
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="valorMensual_Contrato" class="col-sm-4 col-form-label">Valor Mensual:</label>
                                            <div class="col-sm-5">
                                                <input type="number" class="form-control text-end" id="valorMensual_Contrato" name="valorMensual_Contrato" value="<?php echo $contract->monthlyValue;?>">
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="valorTotal_Contrato" class="col-sm-4 col-form-label">Valor Total:</label>
                                            <div class="col-sm-5">
                                                <input type="number" class="form-control text-end" id="valorTotal_Contrato" name="valorTotal_Contrato" value="<?php echo $contract->totalValue;?>">
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <label for="observacion_Contrato" class="col-sm-4 col-form-label">Observaciones:</label>
                                            <div class="col-sm-5">
                                                <textarea class="form-control" id="observacion_Contrato" name="observacion_Contrato" rows="15" ><?php echo htmlspecialchars($contract->observation ?? '');?></textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-4" style="margin-top: 50px">
                                            <div class="col-9 d-flex justify-content-center">
                                                <button class="btn btn-primary" style="width: 200px" type="submit" name="update">Actualizar</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'layouts/footer.php'; ?>
    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->


<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- /Right-bar -->

<!-- JAVASCRIPT -->

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>

</body>

</html>

<?php
} else{
    echo '<script>alert ("Problema al cargar el Contrato")</script>';
}
?>