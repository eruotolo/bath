<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php global $link; include 'layouts/config.php'; ?>

<head>

    <title>Cargar Facturas | Blanco Servicios - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Cargar Facturas</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-invoices-list.php">Facturas</a></li>
                                    <li class="breadcrumb-item active">Cargar Facturas</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <?php if (isset($_GET['error'])): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <?php
                            $errores = [
                                'sin_archivo' => 'No se recibió ningún archivo. Elegí un archivo .xlsx para continuar.',
                                'formato_invalido' => 'El archivo no es un .xlsx válido. Descargá la plantilla y completala sin cambiar el formato.',
                                'sin_filas' => 'El archivo no tiene ninguna fila de datos para cargar (solo el encabezado).',
                            ];
                            echo htmlspecialchars($errores[$_GET['error']] ?? 'Ocurrió un error al procesar el archivo.');
                            ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Carga masiva de facturas desde Excel</h4>
                                <p class="card-title-desc">
                                    Subí un archivo <code>.xlsx</code> con las facturas a cargar. Antes de guardar nada,
                                    vas a poder revisar y corregir los datos en una pantalla de previsualización.
                                </p>
                            </div>
                            <div class="card-body">

                                <div class="mb-4">
                                    <a href="assets/templates/plantilla-facturas.xlsx" class="btn btn-light" download>
                                        <i class="bx bx-download me-1"></i> Descargar plantilla modelo
                                    </a>
                                    <p class="text-muted mt-2 mb-0" style="font-size: 0.85rem;">
                                        La plantilla tiene 4 columnas en este orden: <strong>RUT del Cliente</strong>,
                                        <strong>Numero de Factura</strong>, <strong>Fecha Factura</strong>, <strong>Monto</strong>.
                                        No cambies el orden ni agregues columnas nuevas.
                                    </p>
                                </div>

                                <form action="controller/invoice-upload-parse.php" method="POST" enctype="multipart/form-data">
                                    <div class="row mb-4">
                                        <label for="archivo_facturas" class="col-sm-3 col-form-label">Archivo Excel (.xlsx):</label>
                                        <div class="col-sm-6">
                                            <input type="file" class="form-control" id="archivo_facturas" name="archivo_facturas" accept=".xlsx" required>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <button class="btn btn-primary" type="submit" style="width: 200px">Continuar</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->


        <?php include 'layouts/footer.php'; ?>
    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->


<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- /Right-bar -->

<!-- JAVASCRIPT -->

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>

</body>

</html>
