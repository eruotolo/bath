<?php global $link;
include 'layouts/session.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include 'layouts/head-main.php'; ?>

<head>

    <title>Nuevo Servicio | Seguimiento | Chubby - Admin & Dashboard</title>

    <?php include 'layouts/head.php'; ?>

    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>
<!-- Begin page -->
<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- STAR PAGE TITULO -->

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Agregar Nuevo Servicio | Seguimiento</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-services.php">Servicio | Seguimiento</a></li>
                                    <li class="breadcrumb-item active">Agregar Nuevo Servicio | Seguimiento</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- STAR PAGE CONTENIDO -->

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header">
                                <h4 class="card-title">Formulario de Registro de Nuevo Servicio | Seguimiento</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>

                            <div class="card-body py-4">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                <form class="needs-validation mt-4 pt-2" method="post" action="controller/service-new.php">

                                    <div class="row mb-4">
                                        <label for="id_Contrato" class="col-sm-3 col-form-label">Seleccione el Contrato:</label>
                                        <div class="col-sm-6">
                                            <select name="id_Contrato" id="id_Contrato" class="form-select">
                                                <?php
                                                    $sql = "SELECT * FROM contratos";
                                                    $result = mysqli_query($link, $sql);
                                                    $contratos = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                                    foreach ($contratos as $contrato) {
                                                ?>
                                                        <option value="<?php echo $contrato['id_Contrato']; ?>">
                                                            <?php echo $contrato['obra_Contrato']?>
                                                        </option>
                                                <?php
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fecha_Servicio" class="col-sm-3 col-form-label">Tipo de Servicios:</label>
                                        <div class="col-sm-2">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="instalacion_Tipo" name="instalacion_Tipo">
                                                <label class="form-check-label" for="instalacion_Tipo">
                                                    Instalación
                                                </label>
                                            </div>

                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="reparacion_Tipo" name="reparacion_Tipo">
                                                <label class="form-check-label" for="reparacion_Tipo">
                                                    Reparación
                                                </label>
                                            </div>

                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="limpieza_Tipo" name="limpieza_Tipo">
                                                <label class="form-check-label" for="limpieza_Tipo">
                                                    Limpieza
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-sm-2">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="desinfeccion_Tipo" name="desinfeccion_Tipo">
                                                <label class="form-check-label" for="desinfeccion_Tipo">
                                                    Desinfección
                                                </label>
                                            </div>

                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="sanitizacion_Tipo" name="sanitizacion_Tipo">
                                                <label class="form-check-label" for="sanitizacion_Tipo">
                                                    Sanitización
                                                </label>
                                            </div>

                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="higienico_Tipo" name="higienico_Tipo">
                                                <label class="form-check-label" for="higienico_Tipo">
                                                    Entrega Papel Higiénico
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-sm-2">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="jabon_Tipo" name="jabon_Tipo">
                                                <label class="form-check-label" for="jabon_Tipo">
                                                    Entrega de Jabón Liquido
                                                </label>
                                            </div>

                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" value="1" id="otros_Tipo" name="otros_Tipo">
                                                <label class="form-check-label" for="otros_Tipo">
                                                    Otros
                                                </label>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row mb-4">
                                        <label for="fecha_Servicio" class="col-sm-3 col-form-label">Fecha del Servicio:</label>
                                        <div class="col-sm-6">
                                            <input class="form-control" type="date" id="fecha_Servicio" name="fecha_Servicio" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="observaciones_Servicio" class="col-sm-3 col-form-label">Observaciones:</label>
                                        <div class="col-sm-6">
                                            <textarea class="form-control" id="observaciones_Servicio" name="observaciones_Servicio" rows="5" required></textarea>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button class="btn btn-primary" type="submit" style="width: 200px" name="crear">Crear</button>
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

</html>