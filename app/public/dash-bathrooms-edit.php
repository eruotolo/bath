<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bathroom\FindBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

include('layouts/config.php');
global $link;

$id_Bath = (int) $_GET['id_Bath'];

$useCase = new FindBathroom(new MysqliBathroomRepository($link));
$bathroom = $useCase->handle($id_Bath);

if ($bathroom !== null) {
    ?>

<head>
    <title>Editar Baño | Chubby - Admin & Dashboard</title>
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
                    <h1>Editar Baño</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-bathrooms.php">Listado de Baños</a></li>
                        <li class="active">Editar Baño</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Edición de Baño</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form action="controller/bath-update.php" method="post" id="bathForm" class="needs-validation mt-4 pt-2">
                            <input type="hidden" class="dt-input" id="id_Bath" name="id_Bath" value="<?php echo (int) $bathroom->id; ?>">
                            <input type="hidden" class="dt-input" id="estado_Bath" name="estado_Bath" value="<?php echo (int) $bathroom->estadoBath; ?>">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="codigo_Bath" class="dt-label">Código del Baño</label>
                                    <input type="text" class="dt-input" id="codigo_Bath" name="codigo_Bath" value="<?php echo htmlspecialchars($bathroom->codigoBath, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="mb-4">
                                    <label for="fechaCompra_Bath" class="dt-label">Fecha de compra</label>
                                    <input type="date" class="dt-input" id="fechaCompra_Bath" name="fechaCompra_Bath" value="<?php echo htmlspecialchars($bathroom->fechaCompraBath, ENT_QUOTES, 'UTF-8'); ?>" data-datepicker-max="today">
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="observacion_Bath" class="dt-label">Observaciones</label>
                                    <input type="text" class="dt-input" id="observacion_Bath" name="observacion_Bath" value="<?php echo htmlspecialchars($bathroom->observacionBath, ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button class="dt-btn-add" type="submit" name="update">Actualizar</button>
                            </div>
                        </form>
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
    echo '<script>alert("Problema al cargar el Baño")</script>';
}
?>
