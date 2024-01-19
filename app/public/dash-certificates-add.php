<?php global $link;
include 'layouts/session.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include 'layouts/head-main.php'; ?>

<head>

    <title>Nuevo Contrato | Chubby - Admin & Dashboard</title>

    <?php include 'layouts/head.php'; ?>

    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>
<!-- Begin page -->

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
                            <h4 class="mb-sm-0 font-size-18">Agregar Nuevo Certificado</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-certificates.php">Certificado</a></li>
                                    <li class="breadcrumb-item active">Agregar Certificado</li>
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
                                <h4 class="card-title">Formulario de Registro de Nuevo Certificado</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>

                            <div class="card-body py-4">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                <form class="needs-validation mt-4 pt-2" method="post" action="controller/certificate-new.php" >

                                    <div class="row mb-4">
                                        <label for="id_Cliente" class="col-sm-3 col-form-label">Seleccionar el Cliente:</label>
                                        <div class="col-sm-5">
                                            <select name="id_Cliente" id="id_Cliente" class="form-select">
                                                <?php
                                                $sql = "SELECT * FROM clientes";
                                                $result = mysqli_query($link, $sql);
                                                $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                                foreach ($clientes as $cliente) { ?>
                                                    <option value="<?php echo $cliente['id_Cliente']; ?>" >
                                                        <?php echo $cliente['nombre_Cliente']?>
                                                    </option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="id_Contrato" class="col-sm-3 col-form-label">Seleccionar la obra:</label>
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
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fecha_Servicio" class="col-sm-3 col-form-label">Fecha Servicio:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="fecha_Servicio" name="fecha_Servicio" placeholder="Ingrese las Fecha del Servicio Campo Libre" required>
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
