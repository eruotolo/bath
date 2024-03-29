<?php global $link; 
include 'layouts/session.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include 'layouts/head-main.php'; ?>

<head>

    <title>Nuevo Contrato | Blanco Servicios - Admin & Dashboard</title>

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
                            <h4 class="mb-sm-0 font-size-18">Agregar Nuevo Contrato</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-contracts.php">Contratos</a></li>
                                    <li class="breadcrumb-item active">Agregar Contrato</li>
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
                                <h4 class="card-title">Formulario de Registro de Nuevo Contrato</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>

                            <div class="card-body py-4">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                <form class="needs-validation mt-4 pt-2" method="post" action="controller/contract-new.php" >

                                    <div class="row mb-4">
                                        <label for="id_Cliente" class="col-sm-3 col-form-label">Seleccionar el Cliente:</label>
                                        <div class="col-sm-5">
                                            <select name="id_Cliente" id="id_Cliente" class="form-select">
                                                <option value="" selected>Seleccionar un Cliente</option>
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
                                        <label for="obra_Contrato" class="col-sm-3 col-form-label">Nombre del contrato / OC compra:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="obra_Contrato" name="obra_Contrato" placeholder="Ingrese el nombre de la obra" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="direccion_Contrato" class="col-sm-3 col-form-label">Dirección contrato / OC compra:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="direccion_Contrato" name="direccion_Contrato" placeholder="Ingrese la dirección de la obra" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="estado_Contrato" class="col-sm-3 col-form-label">Estado del contrato / OC compra:</label>
                                        <div class="col-sm-5">
                                            <select name="estado_Contrato" id="estado_Contrato" class="form-select">
                                                <option value="">Seleccione el estado</option>
                                                <option value="2">Activo</option>
                                                <option value="1">Inactivo</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fechaInicio_Contrato" class="col-sm-3 col-form-label">Fecha de inicio del contrato / OC compra:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="date" id="fechaInicio_Contrato" name="fechaInicio_Contrato" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fechaFin_Contrato" class="col-sm-3 col-form-label">Fecha de inicio del contrato / OC compra:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="date" id="fechaFin_Contrato" name="fechaFin_Contrato" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="valorMensual_Contrato" class="col-sm-3 col-form-label">Valor mensual del contrato / OC compra:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="number" id="valorMensual_Contrato" name="valorMensual_Contrato" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="valorTotal_Contrato" class="col-sm-3 col-form-label">Valor total del contrato / OC compra:</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="number" id="valorTotal_Contrato" name="valorTotal_Contrato" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="observacion_Contrato" class="col-sm-3 col-form-label">Observaciones:</label>
                                        <div class="col-sm-5">
                                            <textarea name="observacion_Contrato" id="observacion_Contrato" cols="70" rows="15" class="form-control"></textarea>
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