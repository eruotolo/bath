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

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="dt-page-title print:hidden">
                    <h1>Item Servicio</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-services.php">Servicios</a></li>
                        <li class="active">Item Servicios</li>
                    </ol>
                </div>

                <div class="dt-card print:shadow-none print:border-0">
                    <div class="dt-card-body print:p-0">

                        <div class="flex items-start justify-between gap-4 print:gap-0">
                            <div class="flex-1 print:flex-none">
                                <div class="mb-4">
                                    <img src="assets/images/logo_zl.png" alt="" class="h-40 w-auto">
                                </div>
                            </div>
                            <div class="shrink-0 pt-5 print:shrink-0">
                                <h4 class="text-lg font-bold print:text-lg">Servicios Blanco</h4>
                                <p class="mb-1 flex items-center gap-1 text-sm">
                                    <i data-lucide="mail" class="!h-4 !w-4"></i> jsanchez@expanda.cl
                                </p>
                                <p class="mb-1 flex items-center gap-1 text-sm">
                                    <i data-lucide="phone" class="!h-4 !w-4"></i> +56 9 9293 7371
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-center mt-7 mb-12 print:mt-7 print:mb-12">
                            <h4 class="text-[26px] font-bold uppercase tracking-wide">Comprobante de Ejecución N°: <?php echo (int) $row['nro_Servicio']; ?></h4>
                        </div>

                        <hr class="my-4 print:my-4 border-slate-200">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h5 class="font-bold text-base mb-3">Cliente:</h5>
                                <h5 class="text-base mb-2"><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                <p class="mb-1 text-sm"><?php echo htmlspecialchars($row['direccion_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mb-1 text-sm"><?php echo htmlspecialchars($row['email_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mb-1 text-sm">+56 <?php echo htmlspecialchars($row['telefono_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mb-1 text-sm"><?php echo htmlspecialchars($row['ciudad_Cliente'], ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($row['region_Cliente'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <div>
                                <h5 class="font-bold text-base mb-3">Información:</h5>
                                <p class="mb-1 text-sm"><b>Obra:</b> <?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mb-1 text-sm"><b>Dirección de la obra:</b> <?php echo htmlspecialchars($row['direccion_Contrato'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="mb-1 text-sm"><b>Fecha del Servicio:</b> <?php echo date('d/m/Y', strtotime($row['fecha_Servicio'])); ?></p>

                                <?php
                                $tipos = [
                                    'instalacion_Tipo' => 'Instalación',
                                    'reparacion_Tipo' => 'Reparación',
                                    'limpieza_Tipo' => 'Limpieza',
                                    'desinfeccion_Tipo' => 'Desinfección',
                                    'sanitizacion_Tipo' => 'Sanitización',
                                    'higienico_Tipo' => 'Entrega Papel Higiénico',
                                    'jabon_Tipo' => 'Entrega de Jabón Líquido',
                                    'otros_Tipo' => 'Otros Servicios',
                                    'retiro_Tipo' => 'Retiro de Baños',
                                ];
                                $tiposActivos = [];
                                foreach ($tipos as $field => $label) {
                                    if (isset($row[$field]) && $row[$field] == 1) {
                                        $tiposActivos[] = $label;
                                    }
                                }
                                ?>
                                <p class="mb-1 mt-2 text-sm">
                                    <b>Tipo de Servicio:</b>
                                    <span><?php echo htmlspecialchars(implode(', ', $tiposActivos), ENT_QUOTES, 'UTF-8'); ?></span>
                                </p>

                                <p class="mb-1 mt-3 text-sm">
                                    <b>Baños Tratados:</b>
                                    <span class="font-mono"><?php echo htmlspecialchars(implode(', ', array_column($banosTratados, 'codigo_Bath')), ENT_QUOTES, 'UTF-8'); ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-lg border border-slate-200 p-4 print:border-slate-300">
                            <h5 class="text-base font-semibold mb-2">Observaciones Generales:</h5>
                            <p class="text-sm m-0"><?php echo htmlspecialchars($row['observaciones_Servicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>

                        <div class="my-3 print:my-3">
                            <img src="assets/images/firma_01.png" alt="firma" class="w-full">
                        </div>

                        <hr class="my-2 print:my-2 border-slate-200">

                        <div class="grid grid-cols-2 gap-4 print:gap-4 text-center text-xs">
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
<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>

</body>

</html>

<?php
} else {
    echo '<script>alert("Problema al cargar el Servicio")</script>';
}
?>
