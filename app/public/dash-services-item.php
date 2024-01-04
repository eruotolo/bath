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

if ($query_run) {
    while ($row = mysqli_fetch_array($query_run)) { ?>

        <head>

            <title>Item Servicio | Chubby - Admin & Dashboard</title>

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
                                            <li class="breadcrumb-item active">Item Servicios</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- start page contenido -->

                        <div class="row mt-4">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="invoice-title">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <div class="mb-4">
                                                        <img src="assets/images/logo_zl.svg" alt="" height="160">
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="pt-5">
                                                        <h4 class="font-size-16">Servicio N.º: <?php echo $row['id_Servicio']; ?></h4>
                                                        <p class="mb-1 pt-2">Blanco 60 Local 1 - Castro - Chiloé</p>
                                                        <p class="mb-1">
                                                            <i class="mdi mdi-email align-middle me-1"></i> jsanchez@expanda.cl
                                                        </p>
                                                        <p>
                                                            <i class="mdi mdi-phone align-middle me-1"></i> +56 9 9293 7371
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">

                                        <!-- DATOS DEL CUERPO DEL SERVICIO -->

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h5 class="font-size-15 mb-3"><b>Cliente:</b></h5>
                                                <h5 class="font-size-14 mb-2"><?php echo $row['nombre_Cliente']; ?></h5>
                                                <p class="mb-1"><?php echo $row['direccion_Cliente']; ?></p>
                                                <p class="mb-1"><?php echo $row['email_Cliente']; ?></p>
                                                <p class="mb-1">+56 <?php echo $row['telefono_Cliente']; ?></p>
                                                <p class="mb-1"><?php echo $row['ciudad_Cliente']; ?> | <?php echo $row['region_Cliente']; ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <div>
                                                    <h5 class="font-size-15 mb-3"><b>Información:</b></h5>
                                                    <p class="mb-1">
                                                        <b>Número de Contrato:</b>  <?php echo $row['id_Contrato']; ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <b>Obra: </b><?php echo $row['obra_Contrato']; ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <b>Fecha del Servicio:</b> <?php echo date("d/m/Y", strtotime($row['fecha_Servicio'])); ?>
                                                    </p>

                                                    <p class="mb-1">
                                                        <b>Tipo de Servicio:</b> <?php echo $row['tipo_Servicio']; ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <b>Codigo Baño:</b>  <?php echo $row['codigo_Bath']; ?>
                                                    </p>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="py-2 mt-3">
                                            <h5 class="font-size-15">Observaciones</h5>
                                        </div>

                                        <div class="p-4 border rounded">
                                            <p><?php echo $row['observaciones_Servicio']; ?></p>
                                        </div>

                                        <div class="d-print-none mt-3">
                                            <div class="float-end">
                                                <a href="javascript:window.print()"
                                                   class="btn btn-success waves-effect waves-light me-1"><i
                                                            class="fa fa-print"></i></a>

                                                <a href="controller/servicio-pdf.php?id_Servicio=<?php echo $row['id_Servicio']; ?>" class="btn btn-primary w-md waves-effect waves-light" >Enviar</a>
                                            </div>
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
} else {
    echo '<script>alert ("Problema al cargar el Servicio")</script>';
}
?>