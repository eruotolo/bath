<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Service\FindServiceWithContractAndCustomer;
use App\Infrastructure\Persistence\MysqliServiceRepository;

include 'layouts/config.php';
global $link;

$id_Servicio = (int) $_GET['id_Servicio'];

$row = (new FindServiceWithContractAndCustomer(new MysqliServiceRepository($link)))->handle($id_Servicio);

if ($row !== null) {
    $nro_Servicio = $row['nro_Servicio'];
?>

<head>
    <title>Editar Servicio | Chubby - Admin & Dashboard</title>
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
                    <h1>Editar Servicio</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-services.php">Servicios</a></li>
                        <li class="active">Editar Servicio</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Edición del Servicio | Seguimiento</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form action="controller/service-update.php" class="needs-validation mt-4 pt-2" method="post" enctype="multipart/form-data">
                            <input class="dt-input" type="hidden" id="id_Servicio" name="id_Servicio" value="<?php echo (int) $row['id_Servicio']; ?>" readonly>
                            <input class="dt-input" type="hidden" id="nro_Servicio" name="nro_Servicio" value="<?php echo (int) $row['nro_Servicio']; ?>" readonly>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="id_Cliente" class="dt-label">Cliente</label>
                                    <input class="dt-input" type="text" id="id_Cliente" name="id_Cliente" value="<?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                </div>

                                <div class="mb-4">
                                    <label for="id_Contrato" class="dt-label">Contrato</label>
                                    <input class="dt-input" type="hidden" id="id_Contrato" name="id_Contrato" value="<?php echo (int) $row['id_Contrato']; ?>" readonly>
                                    <input class="dt-input" type="text" value="<?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label class="dt-label">Tipo de Servicios</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                                        <?php
                                        $tipos = [
                                            'instalacion_Tipo' => 'Instalación',
                                            'reparacion_Tipo' => 'Reparación',
                                            'limpieza_Tipo' => 'Limpieza',
                                            'desinfeccion_Tipo' => 'Desinfección',
                                            'sanitizacion_Tipo' => 'Sanitización',
                                            'higienico_Tipo' => 'Entrega Papel Higiénico',
                                            'jabon_Tipo' => 'Entrega de Jabón Liquido',
                                            'otros_Tipo' => 'Otros',
                                            'retiro_Tipo' => 'Retiro de Baños',
                                        ];
                                        foreach ($tipos as $name => $label): ?>
                                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50 hover:border-primary-300 transition-colors">
                                                <input type="checkbox" value="1" id="<?php echo $name; ?>" name="<?php echo $name; ?>" <?php echo isset($row[$name]) && $row[$name] == 1 ? 'checked' : ''; ?> class="!h-4 !w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                <span class="font-sans text-xs text-slate-700"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="fecha_Servicio" class="dt-label">Fecha del Servicio</label>
                                    <input class="dt-input" type="date" id="fecha_Servicio" name="fecha_Servicio" value="<?php echo htmlspecialchars($row['fecha_Servicio'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="observaciones_Servicio" class="dt-label">Observaciones</label>
                                    <textarea class="dt-input" id="observaciones_Servicio" name="observaciones_Servicio" rows="4"><?php echo htmlspecialchars($row['observaciones_Servicio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
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

<?php
} else {
    echo '<script>alert("Problema al cargar el Servicio")</script>';
}
?>
