<?php global $link; 
include 'layouts/session.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include 'layouts/head-main.php'; ?>

<head>

    <title>Nuevo Baño | Chubby - Admin & Dashboard</title>

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
                            <h4 class="mb-sm-0 font-size-18">Agregar Nuevo Baño</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-bathrooms.php">Listado de Baños</a></li>
                                    <li class="breadcrumb-item active">Agregar Nuevo Baño</li>
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
                                <h4 class="card-title">Formulario de Registro de Nuevo Baño</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>

                            <div class="card-body py-4">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                <form class="needs-validation mt-4 pt-2" method="post" action="controller/bath-new.php">

                                    <div class="row mb-4">
                                        <label for="codigo_Bath" class="col-sm-3 col-form-label">Código del Baño</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="codigo_Bath" name="codigo_Bath" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fechaCompra_Bath" class="col-sm-3 col-form-label">Fecha de compra:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="date" id="fechaCompra_Bath" name="fechaCompra_Bath" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="estado_Bath" class="col-sm-3 col-form-label">Estado del baño:</label>
                                        <div class="col-sm-5">
                                            <select name="estado_Bath" id="estado_Bath" class="form-select">
                                                <option value="">Seleccione el estado</option>
                                                <option value="1">Activo</option>
                                                <option value="0">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="observacion_Bath" class="col-sm-3 col-form-label">Observaciones:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="observacion_Bath" name="observacion_Bath" required>
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