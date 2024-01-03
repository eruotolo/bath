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
                                        <div class="col-sm-5">
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
                                        <label for="id_Bath" class="col-sm-3 col-form-label">Seleccione Baño:</label>
                                        <div class="col-sm-5">
                                            <select name="id_Bath" id="id_Bath" class="form-select">
                                                <?php
                                                    $sql = "SELECT * FROM bathrooms";
                                                    $result = mysqli_query($link, $sql);
                                                    $bathrooms = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                                    foreach ($bathrooms as $bath) {
                                                ?>
                                                        <option value="<?php echo $bath['id_Bath']; ?>">
                                                            <?php echo $bath['codigo_Bath']?>
                                                        </option>
                                                <?php
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="tipo_Servicio" class="col-sm-3 col-form-label">Tipo de Servicio:</label>
                                        <div class="col-sm-5">
                                            <select name="tipo_Servicio" id="tipo_Servicio" class="form-select">
                                                <option value="Mantenimiento">Mantenimiento</option>
                                                <option value="Limpieza">Limpieza</option>
                                                <option value="Reparación">Reparación</option>
                                                <option value="Instalación">Instalación</option>
                                                <option value="Otros">Otros</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fecha_Servicio" class="col-sm-3 col-form-label">Fecha del Servicio:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="date" id="fecha_Servicio" name="fecha_Servicio" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="estado_Servicio" class="col-sm-3 col-form-label">Estado del Servicio:</label>
                                        <div class="col-sm-5">
                                            <select name="estado_Servicio" id="estado_Servicio" class="form-select">
                                                <option value="">Seleccionar el estado</option>
                                                <option value="0">Inactivo</option>
                                                <option value="1">Activo</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="observaciones_Servicio" class="col-sm-3 col-form-label">Observaciones:</label>
                                        <div class="col-sm-5">
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