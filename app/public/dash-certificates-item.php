<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

global $link;

include('layouts/config.php');

$id_Certificado = $_GET['id_Certificado'];

$query = "SELECT * FROM certificados CR
    JOIN clientes CL ON CR.id_Cliente = CL.id_Cliente
    JOIN contratos CT ON CL.id_Cliente = CT.id_Cliente
WHERE id_Certificado = $id_Certificado";

$query_run = mysqli_query($link, $query);

if ($query_run) {
    $row = mysqli_fetch_array($query_run);
    // Generar el número de certificado
    $fechaHoy = date("dmY", strtotime($row['fechahoy_Certificado']));
    $certificado = $fechaHoy . 'A' . $row['nro_Certificado'];
    ?>

    <head>

        <title>Item Certificado | Blanco Servicios - Admin & Dashboard</title>

        <?php include 'layouts/head.php'; ?>
        <?php include 'layouts/head-style.php'; ?>

    </head>

    <?php include 'layouts/body.php'; ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'layouts/menu.php'; ?>
        <!-- Start right Content here -->
        <?php echo $certificado ?>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Item Certificado</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dash-certificates.php">Certificados</a></li>
                                        <li class="breadcrumb-item active">Item Certificado</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                            <div class="card">
                                <div class="card-body">

                                    <div class="certificado-title">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="mb-4 logo-zl">
                                                    <img src="assets/images/logo_zl.png" alt="" height="120">
                                                </div>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <div class="mb-4 logo-rc">
                                                    <img src="assets/images/logo_rc.png" height="120" alt="Rata">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column titulo-certificado align-items-center">
                                        <h4>Certificado de disposición final de residuos</h4>
                                        <h5>NRO: <?php echo $certificado ?> - Fecha:
                                            <?php echo date("d/m/Y", strtotime($row['fechahoy_Certificado'])); ?>
                                        </h5>
                                    </div>

                                    <div class="row contenido-certificado">
                                        <div class="col-12">
                                            <p>BLANCO SERVICIOS E INVERSIONES SPA, RUT 76.654.452-7, con domicilio en Nercon Alto S/N, Castro, Chiloé, deja constancia que realizó una disposición final de residuos  a la empresa:</p>
                                        </div>
                                        <div class="col-12 datos-certificados">
                                            <p><b>Cliente</b>: <?php echo $row['nombre_Cliente']; ?></p>
                                            <p><b>Rut</b>: <?php echo $row['rut_Cliente']; ?></p>
                                            <p><b>Fecha del Servicio</b>: <?php echo $row['fecha_Servicio']; ?></p>
                                            <p><b>Cantidad metros cúbicos</b>: 3 Mts</p>
                                            <p><b>Sector de Origen</b>: <?php echo $row['obra_Contrato']; ?></p>
                                        </div>
                                        <div class="col-12">
                                            <p>Lo residuos retirados fueron trasladados y posteriormente tratados en la planta de tratamiento en Castro, de la empresa SURALIS, de acuerdo a contrato vigente.</p>

                                            <p>Los residuos fueron gestionados según lo dispuesto por la legislación chilena y a la normativa que regula este tipo de servicios. Resolución sanitaria del Camión transportador Nro. 2310428351 de fecha 17/10/2023.</p>

                                            <p>Se extiende el presente documento, a petición del cliente para ser utilizada como respaldo frente fiscalización. </p>
                                        </div>

                                        <div class="d-flex justify-content-center firma-blanco">
                                            <img src="assets/images/firma.jpeg" alt="firma">
                                        </div>
                                    </div>

                                    <hr class="my-1">

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
