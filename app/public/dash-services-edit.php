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