<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

include('layouts/config.php');
global $link;

$id_Servicio = $_GET['id_Servicio'];

$query = "SELECT * FROM servicios SR
    JOIN contratos CT ON SR.id_Contrato = CT.id_Contrato
    JOIN bathrooms BT ON SR.id_Bath = BT.id_Bath
    JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente WHERE id_Servicio = $id_Servicio";

$query_run = mysqli_query($link, $query);

if ($query_run){
    while ($row = mysqli_fetch_array($query_run)) { ?>

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

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Item Servicio</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dash-services.php">Servicios</a></li>
                                        <li class="breadcrumb-item active">Editar Servicios</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- start page contenido -->

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

                                        <form action="" method="post" style="margin-top: 50px; margin-left: 15%">

                                            <input type="text" class="form-control" id="id_Servicio" name="id_Servicio" value="<?php echo $row['id_Servicio'];?>" hidden>

                                            <div class="row mb-4">
                                                <label for="id_Contrato" class="col-sm-4 col-form-label">Obra Contrato:</label>
                                                <div class="col-sm-5">
                                                    <select name="id_Contrato" id="id_Contrato" class="form-select">
                                                        <?php
                                                            $sql = "SELECT * FROM contratos";
                                                            $result = mysqli_query($link, $sql);
                                                            $contratros = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                                            foreach ($contratros as $contratro) {
                                                        ?>
                                                                <option value="<?php echo $contratro['id_Contrato']; ?>" <?php if ($contratro['id_Contrato'] == $row['id_Contrato']){ echo 'selected'; } ?>>
                                                                    <?php echo $contratro['obra_Contrato']; ?>
                                                                </option>
                                                        <?php
                                                            }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-4">
                                                <label for="id_Contrato" class="col-sm-4 col-form-label">Baño Contrato:</label>
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
    }
}else{
    echo '<script>alert ("Problema al cargar el Servicio")</script>';
}
?>