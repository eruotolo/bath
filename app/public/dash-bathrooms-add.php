<?php global $link;
include 'layouts/session.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Nuevo Baño | Blanco Servicios - Admin & Dashboard</title>
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
                    <h1>Agregar Nuevo Baño</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-bathrooms.php">Listado de Baños</a></li>
                        <li class="active">Agregar Nuevo Baño</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Registro de Nuevo Baño</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form class="needs-validation mt-4 pt-2" method="post" action="controller/bath-new.php">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="codigo_Bath" class="dt-label">Código del Baño</label>
                                    <input class="dt-input" type="text" id="codigo_Bath" name="codigo_Bath" required>
                                </div>

                                <div class="mb-4">
                                    <label for="fechaCompra_Bath" class="dt-label">Fecha de compra</label>
                                    <input class="dt-input" type="date" id="fechaCompra_Bath" name="fechaCompra_Bath" required data-datepicker-max="today">
                                </div>

                                <div class="mb-4">
                                    <label for="estado_Bath" class="dt-label">Estado del baño</label>
                                    <select name="estado_Bath" id="estado_Bath" class="dt-select" data-enhanced-select>
                                        <option value="">Seleccione el estado</option>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="observacion_Bath" class="dt-label">Observaciones</label>
                                    <input class="dt-input" type="text" id="observacion_Bath" name="observacion_Bath" required>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button class="dt-btn-add" type="submit" name="crear">Crear</button>
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
