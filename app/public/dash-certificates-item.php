<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Certificate\FindCertificateForPrint;
use App\Infrastructure\Persistence\MysqliCertificateRepository;

global $link;

include('layouts/config.php');

$id_Certificado = (int) $_GET['id_Certificado'];
$id_Contrato = (int) $_GET['id_Contrato'];

$useCase = new FindCertificateForPrint(new MysqliCertificateRepository($link));
$row = $useCase->handle($id_Certificado, $id_Contrato);

if ($row) {
    $certificado = $row['certificado'];
?>

<head>
    <title>Item Certificado | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="dt-page-title">
                    <h1>Item Certificado</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-certificates.php">Certificados</a></li>
                        <li class="active">Item Certificado</li>
                    </ol>
                </div>

                <div class="dt-card print:shadow-none print:border-0">
                    <div class="dt-card-body print:p-0">

                        <div class="print:flex print:items-start print:gap-0 flex items-start gap-4 justify-between">
                            <div class="flex-1 print:flex-none">
                                <div class="mb-4 ml-12 print:ml-12">
                                    <img src="assets/images/logo_zl.png" alt="" height="120">
                                </div>
                            </div>
                            <div class="shrink-0 print:shrink-0">
                                <div class="mb-4 mr-12 print:mr-12">
                                    <img src="assets/images/logo_rc.png" height="120" alt="Rata">
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col items-center mt-7 mb-12 print:mt-7 print:mb-12">
                            <h4 class="text-[26px] font-bold uppercase leading-[36px] tracking-wide print:text-[26px]">Certificado de disposición final de residuos</h4>
                            <h5 class="mt-2 text-base font-semibold">NRO: <?php echo htmlspecialchars($certificado, ENT_QUOTES, 'UTF-8'); ?> - Fecha: <?php echo date('d/m/Y', strtotime($row['fechahoy_Certificado'])); ?></h5>
                        </div>

                        <div class="px-8 print:px-8">
                            <p>BLANCO SERVICIOS E INVERSIONES SPA, RUT 76.654.452-7, con domicilio en Nercon Alto S/N, Castro, Chiloé, deja constancia que realizó una disposición final de residuos a la empresa:</p>

                            <div class="my-7 print:my-7 space-y-1">
                                <p class="text-sm leading-5"><b>Cliente</b>: <?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm leading-5"><b>Rut</b>: <?php echo htmlspecialchars($row['rut_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm leading-5"><b>Fecha del Servicio</b>: <?php echo htmlspecialchars($row['fecha_Servicio'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-sm leading-5"><b>Cantidad metros cúbicos</b>: <?php echo (int) $row['mts_Certificado']; ?> Mts</p>
                                <p class="text-sm leading-5"><b>Sector de Origen</b>: <?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>

                            <p>Los residuos retirados fueron trasladados y posteriormente tratados en la planta de tratamiento en Castro, de la empresa SURALIS, de acuerdo a contrato vigente.</p>

                            <p>Los residuos fueron gestionados según lo dispuesto por la legislación chilena y a la normativa que regula este tipo de servicios. Resolución sanitaria del Camión transportador Nro. 2310428351 de fecha 17/10/2023.</p>

                            <p>Se extiende el presente documento, a petición del cliente para ser utilizada como respaldo frente fiscalización.</p>

                            <div class="flex justify-center my-4 print:my-4">
                                <img src="assets/images/firma.jpeg" alt="firma" class="h-24 print:h-24">
                            </div>
                        </div>

                        <hr class="my-2 print:my-2">

                        <div class="px-8 print:px-8 grid grid-cols-2 gap-8 print:gap-8 text-center text-xs">
                            <div>
                                <p class="m-0">Blanco Servicios e Inversiones SPA</p>
                                <p class="m-0">Servicios de disposición final</p>
                            </div>
                            <div>
                                <p class="m-0">RataCop Servicios e Inversiones SPA</p>
                                <p class="m-0">Control de Plagas</p>
                            </div>
                            <div class="col-span-2">
                                <p class="m-0">Fonos: +56 9 9293 7371 | +56 9 2071 5438 | +56 652 630505 | email: jsanchez@expanda.cl</p>
                            </div>
                        </div>

                        <div class="mt-3 print:hidden text-right">
                            <a href="javascript:window.print()" class="dt-btn-add">
                                <i data-lucide="printer" class="!mr-1.5 !h-3.5 !w-3.5"></i> Imprimir
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>

</body>

</html>

<?php
} else {
    echo '<script>alert("Problema al cargar el Servicio")</script>';
}
?>
