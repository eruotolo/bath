<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php global $link; include 'layouts/config.php'; ?>

<head>
    <title>Cargar Facturas | Blanco Servicios - Admin & Dashboard Template</title>
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
                    <h1>Cargar Facturas</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-invoices-list.php">Facturas</a></li>
                        <li class="active">Cargar Facturas</li>
                    </ol>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="dt-alert dt-alert-danger">
                        <?php
                        $errores = [
                            'sin_archivo' => 'No se recibió ningún archivo. Elegí un archivo .xlsx para continuar.',
                            'formato_invalido' => 'El archivo no es un .xlsx válido. Descargá la plantilla y completala sin cambiar el formato.',
                            'sin_filas' => 'El archivo no tiene ninguna fila de datos para cargar (solo el encabezado).',
                        ];
                        echo htmlspecialchars($errores[$_GET['error']] ?? 'Ocurrió un error al procesar el archivo.', ENT_QUOTES, 'UTF-8');
                        ?>
                    </div>
                <?php endif; ?>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Carga masiva de facturas desde Excel</h4>
                        <p class="dt-card-desc">
                            Subí un archivo <code>.xlsx</code> con las facturas a cargar. Antes de guardar nada,
                            vas a poder revisar y corregir los datos en una pantalla de previsualización.
                        </p>
                    </div>
                    <div class="dt-card-body py-8">

                        <div class="mb-6">
                            <a href="assets/templates/plantilla-facturas.xlsx" class="dt-btn-secondary" download>
                                <i data-lucide="download" class="!mr-1.5 !h-3.5 !w-3.5"></i> Descargar plantilla modelo
                            </a>
                            <p class="mt-2 mb-0 text-xs text-slate-500">
                                La plantilla tiene 4 columnas en este orden: <strong>RUT del Cliente</strong>,
                                <strong>Numero de Factura</strong>, <strong>Fecha Factura</strong>, <strong>Monto</strong>.
                                No cambies el orden ni agregues columnas nuevas.
                            </p>
                        </div>

                        <form action="controller/invoice-upload-parse.php" method="POST" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-2 items-end mb-6">
                                <label for="archivo_facturas" class="dt-label md:col-span-1 self-center !mb-0">Archivo Excel (.xlsx):</label>
                                <div class="md:col-span-2">
                                    <input type="file" class="dt-input file:mr-3 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:font-semibold file:text-primary-700" id="archivo_facturas" name="archivo_facturas" accept=".xlsx" required>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button class="dt-btn-add !w-auto" type="submit">Continuar</button>
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
