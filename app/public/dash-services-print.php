<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\FindServiceForPrint;
use App\Infrastructure\Persistence\MysqliServiceRepository;

include('layouts/config.php');
global $link;

$id_Servicio = (int) $_GET['id_Servicio'];

$impresion = (new FindServiceForPrint(new MysqliServiceRepository($link)))->handle($id_Servicio);

if ($impresion !== null) {
    $row = $impresion['service'];
    $banosTratados = $impresion['bathrooms'];

?>

        <head>

            <title>Item Servicio | Blanco Servicios - Admin & Dashboard</title>

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
                                                        <img src="assets/images/logo_zl.png" alt="" height="160">
                                                    </div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <div class="pt-5">
                                                        <h4 class="font-size-18" style="font-weight: bold">Servicios Blanco</h4>
                                                        <p class="mb-1">
                                                            <i class="mdi mdi-email align-middle me-1"></i> jsanchez@expanda.cl
                                                        </p>
                                                        <p>
                                                            <i class="mdi mdi-phone align-middle me-1"></i> +56 9 9293 7371
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex titulo-comprobante justify-content-center titulo-certificado">
                                                <h4>Comprobante de Ejecución N°: <?php echo (int) $row['nro_Servicio']; ?></h4>
                                            </div>

                                        </div>

                                        <hr class="my-4">

                                        <!-- DATOS DEL CUERPO DEL SERVICIO -->

                                        <div class="row">
                                            <div class="col-sm-6">
                                                <h5 class="font-size-14 mb-3"><b>Cliente:</b></h5>
                                                <h5 class="font-size-14 mb-2"><?php echo htmlspecialchars($row['nombre_Cliente']); ?></h5>
                                                <p class="mb-1"><?php echo htmlspecialchars($row['direccion_Cliente']); ?></p>
                                                <p class="mb-1"><?php echo htmlspecialchars($row['email_Cliente']); ?></p>
                                                <p class="mb-1">+56 <?php echo htmlspecialchars($row['telefono_Cliente']); ?></p>
                                                <p class="mb-1"><?php echo htmlspecialchars($row['ciudad_Cliente']); ?> | <?php echo htmlspecialchars($row['region_Cliente']); ?></p>
                                            </div>
                                            <div class="col-sm-6">
                                                <div>
                                                    <h5 class="font-size-15 mb-3"><b>Información:</b></h5>
                                                    <p class="mb-1">
                                                        <b>Obra: </b><?php echo htmlspecialchars($row['obra_Contrato']); ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <b>Dirección de la obra:</b>  <?php echo htmlspecialchars($row['direccion_Contrato']); ?>
                                                    </p>
                                                    <p class="mb-1">
                                                        <b>Fecha del Servicio:</b> <?php echo date("d/m/Y", strtotime($row['fecha_Servicio'])); ?>
                                                    </p>

                                                    <ul class="list-service-print mb-1">
                                                        <li><b>Tipo de Servicio:</b></li>
                                                        <?php
                                                        if($row['instalacion_Tipo'] == 1){ ?>
                                                            <li>Instalación, </li>
                                                        <?php } ?>

                                                        <?php
                                                        if($row['reparacion_Tipo'] == 1){ ?>
                                                            <li>Reparación, </li>
                                                        <?php } ?>

                                                        <?php
                                                        if($row['limpieza_Tipo'] == 1){ ?>
                                                            <li>Limpieza, </li>
                                                        <?php } ?>

                                                        <?php
                                                        if($row['desinfeccion_Tipo'] == 1){ ?>
                                                            <li>Desinfección, </li>
                                                        <?php } ?>

                                                        <?php
                                                        if($row['sanitizacion_Tipo'] == 1){ ?>
                                                            <li>Sanitización, </li>
                                                        <?php } ?>

                                                        <?php
                                                        if($row['higienico_Tipo'] == 1){ ?>
                                                            <li>Entrega Papel Higiénico, </li>
                                                        <?php } ?>

                                                        <?php
                                                        if($row['jabon_Tipo'] == 1){ ?>
                                                            <li>Entrega de Jabón Líquido, </li>
                                                        <?php } ?>

                                                        <?php
                                                        if($row['otros_Tipo'] == 1){ ?>
                                                            <li>Otros Servicios</li>
                                                        <?php } ?>

                                                        <?php
                                                        if($row['retiro_Tipo'] == 1){ ?>
                                                            <li>Retiro de Baños</li>
                                                        <?php } ?>
                                                    </ul>

                                                    <br>

                                                    <ul class="list-service-print mb-1">
                                                        <li><b>Baños Tratados:</b></li>
                                                        <?php foreach ($banosTratados as $bano) { ?>
                                                        <li><?php echo htmlspecialchars($bano['codigo_Bath']); ?></li>
                                                        <?php } ?>
                                                    </ul>


                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4 border rounded mt-5">
                                            <h5 class="font-size-15">Observaciones Generales:</h5>
                                            <p><?php echo htmlspecialchars($row['observaciones_Servicio'] ?? ''); ?></p>
                                        </div>
                                        
                                        <div class="row my-3 sub-pie">
                                            <div class="d-flex justify-content-center firma">
                                                <img src="assets/images/firma_01.png" alt="firma">
                                            </div>
                                        </div>

                                        <hr class="my-2">

                                        <div class="row">
                                            <div class="col-6 pie">
                                                <p class="mb-1">Blanco Servicios e Inversiones SPA</p>
                                                <p class="mb-1">Servicios de disposición final</p>
                                            </div>
                                            <div class="col-6 pie">
                                                <p class="mb-1">RataCop Servicios e Inversiones SPA</p>
                                                <p class="mb-1">Control de Plagas</p>
                                            </div>
                                            <div class="col-12 pie">
                                                <p class="mb-1">Fonos: +56 9 9293 7371 | +56 9 2071 5438 | +56 652 630505 | email: jsanchez@expanda.cl</p>
                                            </div>
                                        </div>

                                        <div class="d-print-none mt-2">
                                            <div class="float-end">
                                                <a href="javascript:window.print()"
                                                   class="btn btn-success waves-effect waves-light me-1"><i
                                                            class="fa fa-print"></i></a>

                                               <!-- <a href="controller/servicio-pdf.php?id_Servicio=<?php /*echo $row['id_Servicio']; */?>" class="btn btn-primary w-md waves-effect waves-light" >Enviar</a>-->
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

} else {
    echo '<script>alert ("Problema al cargar el Servicio")</script>';
}
?>