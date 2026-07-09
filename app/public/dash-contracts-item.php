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

<!-- Begin page -->
        <div id="layout-wrapper">

            <?php include 'layouts/menu.php'; ?>
            <!-- Start right Content here -->

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
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-12">
                                                <h5 class="card-title">Información del Contrato</h5>
                                                <p class="card-title-desc">Datos relevantes al contrato</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">

                                                <div class="row mb-4">
                                                    <label for="id_Contrato" class="col-sm-4 col-form-label">Id Contrato:</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" value="<?php echo (int) $contrato['id_Contrato'] ?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <label for="nombre_Cliente" class="col-sm-4 col-form-label">Nombre Cliente:</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrato['nombre_Cliente']) ?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <label for="obra_Contrato" class="col-sm-4 col-form-label">Nombre de la Obra:</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrato['obra_Contrato']) ?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <label for="direccion_Contrato" class="col-sm-4 col-form-label">Dirección de la Obra:</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrato['direccion_Contrato']) ?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <label for="fechaInicio_Contrato" class="col-sm-4 col-form-label">Fecha de Inicio de la Obra:</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrato['fechaInicio_Contrato']);?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <label for="fechaFin_Contrato" class="col-sm-4 col-form-label">Fecha de Fin de la Obra:</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($contrato['fechaFin_Contrato']);?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <label for="valorMensual_Contrato" class="col-sm-4 col-form-label">Valor Mensual:</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" value="<?php echo (int) $contrato['valorMensual_Contrato'];?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <label for="valorTotal_Contrato" class="col-sm-4 col-form-label">Valor Total:</label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" value="<?php echo (int) $contrato['valorTotal_Contrato'];?>" readonly>
                                                    </div>
                                                </div>

                                                <div class="row mb-4">
                                                    <label for="observacion_Contrato" class="col-sm-4 col-form-label">Observaciones:</label>
                                                    <div class="col-sm-7">
                                                        <textarea class="form-control" rows="10" readonly><?php echo htmlspecialchars($contrato['observacion_Contrato'] ?? '');?></textarea>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="card-title">Baños de la Obra</h5>
                                                <p class="card-title-desc">Baños relacionados a la Obra</p>
                                            </div>
                                            <div class="col-6">
                                                <a href="#"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#nuevoAssign"
                                                   data-bs-whatever="@fat"
                                                   class="btn btn-light float-end">
                                                    <i class="bx bx-plus me-1"></i> Agregar Nuevo Baño</a>
                                            </div>
                                            <!-- MODAL EDITA CLIENTE-->
                                            <?php include 'layouts/modal-nuevo-assign-bath.php'; ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <table class="table table-nowrap align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Id</th>
                                                        <th>Código Baño</th>
                                                        <th>Fecha de compra</th>
                                                        <th>Asignado</th>
                                                        <th>Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        foreach ($banos as $bano) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo (int) $bano['id_Relacion'] ?></td>
                                                        <td><?php echo htmlspecialchars($bano['codigo_Bath']) ?></td>
                                                        <td><?php echo htmlspecialchars($bano['fechaCompra_Bath']) ?></td>

                                                        <?php
                                                        if ($bano['asignado_Bath'] == 1) { ?>
                                                            <td>
                                                                <div class="badge item-activo">Asignado</div>
                                                            </td>
                                                            <?php
                                                        } else {
                                                            ?>
                                                            <td>
                                                                <div class="badge item-disponible">Disponible</div>
                                                            </td>
                                                            <?php
                                                        }
                                                        ?>
                                                        <td style="width: 70px; text-align: center">
                                                            <!-- Botón para eliminar relación -->
                                                            <a href="controller/contract-bath-notassign.php?id_Contrato=<?php echo (int) $bano['id_Contrato'] ?>&id_Bath=<?php echo (int) $bano['id_Bath'] ?>&id_Relacion=<?php echo (int) $bano['id_Relacion'] ?>"
                                                               class="btn btn-outline-secondary btn-sm delete-contacto" title="No Asignar">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                        }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>


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
