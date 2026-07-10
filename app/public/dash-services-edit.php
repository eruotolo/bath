<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\FindServiceWithContractAndCustomer;
use App\Infrastructure\Persistence\MysqliServiceRepository;

include 'layouts/config.php';
global $link;

$id_Servicio = (int) $_GET['id_Servicio'];

$row = (new FindServiceWithContractAndCustomer(new MysqliServiceRepository($link)))->handle($id_Servicio);

if ($row !== null) {
    $nro_Servicio = $row['nro_Servicio'];
?>

    <head>
        <title>Editar Servicio | Chubby - Admin & Dashboard</title>

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

                        <!-- STAR PAGE TITULO -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Item Servicio</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dash-services.php">Servicios</a></li>
                                            <li class="breadcrumb-item active">Item Servicio</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- STAR PAGE CONTENT -->

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Formulario de Edición del Servicio | Seguimiento</h4>
                                        <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                        <form action="controller/service-update.php" class="needs-validation mt-4 pt-2" method="post" enctype="multipart/form-data">
                                            <input class="form-control" type="hidden" id="id_Servicio" name="id_Servicio" value="<?php echo $row['id_Servicio']?>" readonly >
                                            <input class="form-control" type="hidden" id="nro_Servicio" name="nro_Servicio" value="<?php echo $row['nro_Servicio']?>" readonly >

                                            <div class="row mb-4">
                                                <label for="id_Cliente" class="col-sm-3 col-form-label">Cliente:</label>
                                                <div class="col-sm-6">
                                                    <input class="form-control" type="text" id="id_Cliente" name="id_Cliente" value="<?php echo htmlspecialchars($row['nombre_Cliente'])?>" readonly>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <label for="id_Contrato" class="col-sm-3 col-form-label">Contrato:</label>
                                                <div class="col-sm-6">
                                                    <input class="form-control" type="hidden" id="id_Contrato" name="id_Contrato" value="<?php echo (int) $row['id_Contrato']?>" readonly>
                                                    <input class="form-control" type="text" value="<?php echo htmlspecialchars($row['obra_Contrato'])?>" readonly>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <label for="tipo_servicio" class="col-sm-3 col-form-label">Tipo de Servicios: *</label>
                                                <div class="col-sm-2">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="instalacion_Tipo" name="instalacion_Tipo" <?php echo $row['instalacion_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="instalacion_Tipo">
                                                            Instalación
                                                        </label>
                                                    </div>

                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="reparacion_Tipo" name="reparacion_Tipo" <?php echo $row['reparacion_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="reparacion_Tipo">
                                                            Reparación
                                                        </label>
                                                    </div>

                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="limpieza_Tipo" name="limpieza_Tipo" <?php echo $row['limpieza_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="limpieza_Tipo">
                                                            Limpieza
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-2">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="desinfeccion_Tipo" name="desinfeccion_Tipo" <?php echo $row['desinfeccion_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="desinfeccion_Tipo">
                                                            Desinfección
                                                        </label>
                                                    </div>

                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="sanitizacion_Tipo" name="sanitizacion_Tipo" <?php echo $row['sanitizacion_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="sanitizacion_Tipo">
                                                            Sanitización
                                                        </label>
                                                    </div>

                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="higienico_Tipo" name="higienico_Tipo" <?php echo $row['higienico_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="higienico_Tipo">
                                                            Entrega Papel Higiénico
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-2">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="jabon_Tipo" name="jabon_Tipo" <?php echo $row['jabon_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="jabon_Tipo">
                                                            Entrega de Jabón Liquido
                                                        </label>
                                                    </div>

                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="otros_Tipo" name="otros_Tipo" <?php echo $row['otros_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="otros_Tipo">
                                                            Otros
                                                        </label>
                                                    </div>

                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" value="1" id="retiro_Tipo" name="retiro_Tipo" <?php echo $row['retiro_Tipo'] == 1 ? 'checked' : ''; ?>>
                                                        <label class="form-check-label" for="retiro_Tipo">
                                                            Retiro de Baños
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <label for="fecha_Servicio" class="col-sm-3 col-form-label">Fecha del Servicio: *</label>
                                                <div class="col-sm-6">
                                                    <input class="form-control" type="date" id="fecha_Servicio" name="fecha_Servicio" value="<?php echo htmlspecialchars($row['fecha_Servicio'])?>">
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <label for="observaciones_Servicio" class="col-sm-3 col-form-label">Observaciones: *</label>
                                                <div class="col-sm-6">
                                                    <textarea class="form-control" id="observaciones_Servicio" name="observaciones_Servicio" rows="5"><?php echo htmlspecialchars($row['observaciones_Servicio'] ?? '')?></textarea>
                                                </div>
                                            </div>

                                            <div class="row justify-content-end">
                                                <div class="col-sm-9">
                                                    <div>
                                                        <button class="btn btn-primary" type="submit" style="width: 200px" name="update">Actualizar</button>
                                                    </div>
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
        </div>
        <!-- END layout-wrapper -->

        <!-- Right Sidebar -->
        <?php include 'layouts/right-sidebar.php'; ?>
        <!-- /Right-bar -->

        <!-- JAVASCRIPT -->

        <?php include 'layouts/vendor-scripts.php'; ?>
        <script src="assets/js/app.js"></script>


    </body>

<?php

} else {
    echo '<script>alert ("Problema al cargar el Servicio")</script>';
}
?>