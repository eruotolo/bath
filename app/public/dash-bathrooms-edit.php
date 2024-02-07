<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
include('layouts/config.php');
global $link;

$id_Bath = $_GET['id_Bath'];

$query = "SELECT * FROM bathrooms WHERE id_Bath = $id_Bath";
$query_run = mysqli_query($link, $query);

if($query_run){
    while ($row = mysqli_fetch_array($query_run)){

        ?>

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
                            <h4 class="mb-sm-0 font-size-18">Editar Baño</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-bathrooms.php">Listado de Baños</a></li>
                                    <li class="breadcrumb-item active">Editar Baño</li>
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
                                <h4 class="card-title">Formulario de Registro de Edición de Baño</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>

                            <div class="card-body py-4">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                <form action="controller/bath-update.php" method="post" id="bathForm" class="mt-4 pt-2">

                                    <div class="row mb-4">
                                        <input type="hidden" class="form-control" id="id_Bath" name="id_Bath"  value="<?php echo $row['id_Bath'];?>">
                                        <input type="hidden" class="form-control" id="estado_Bath" name="estado_Bath"  value="<?php echo $row['estado_Bath'];?>">
                                    </div>


                                    <div class="row mb-4">
                                        <label for="codigo_Bath" class="col-sm-3 col-form-label">Código del Baño:</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" id="codigo_Bath" name="codigo_Bath" value="<?php echo $row['codigo_Bath'];?>">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fechaCompra_Bath" class="col-sm-3 col-form-label">Fecha de compra:</label>
                                        <div class="col-sm-6">
                                            <input type="date" class="form-control" id="fechaCompra_Bath" name="fechaCompra_Bath" value="<?php echo $row['fechaCompra_Bath'];?>">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="observacion_Bath" class="col-sm-3 col-form-label">Observaciones:</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" id="observacion_Bath" name="observacion_Bath" value="<?php echo $row['observacion_Bath'];?>">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-sm-9">
                                            <button class="btn btn-primary w-md btn-registrar" type="submit" name="update" style="width: 200px; margin-left: 415px">Actualizar</button>
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

        <?php
    }
}  else{
    echo '<script>alert ("Problema al cargar el Baño")</script>';
}
?>