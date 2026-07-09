<?php
session_start();
include 'layouts/session.php';
?>
<?php include 'layouts/head-main.php'; ?>
<?php global $link; include 'layouts/config.php'; ?>

<?php
if (!isset($_SESSION['carga_resultado'])) {
    header('Location: dash-invoices-list.php');
    exit();
}

$resultado = $_SESSION['carga_resultado'];
unset($_SESSION['carga_resultado']);
?>

<head>

    <title>Resultado de la Carga | Blanco Servicios - Admin & Dashboard Template</title>
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
                            <h4 class="mb-sm-0 font-size-18">Resultado de la Carga</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-invoices-list.php">Facturas</a></li>
                                    <li class="breadcrumb-item active">Resultado de la Carga</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <div class="alert alert-success">
                                    <strong><?php echo (int)$resultado['cargadas'] ?></strong> factura(s) cargada(s) correctamente.
                                </div>

                                <?php if (count($resultado['rechazadas']) > 0): ?>
                                    <div class="alert alert-warning">
                                        <strong><?php echo count($resultado['rechazadas']) ?></strong> fila(s) rechazada(s):
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-nowrap align-middle mb-4">
                                            <thead>
                                            <tr>
                                                <th>Número de Factura</th>
                                                <th>Motivo</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($resultado['rechazadas'] as $rechazo): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($rechazo['numero_Factura'] ?: '(sin número)') ?></td>
                                                    <td class="text-danger"><?php echo htmlspecialchars($rechazo['motivo']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>

                                <a href="dash-invoices-list.php" class="btn btn-primary">Ir al Listado de Facturas</a>

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
