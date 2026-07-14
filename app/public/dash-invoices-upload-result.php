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

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="dt-page-title">
                    <h1>Resultado de la Carga</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-invoices-list.php">Facturas</a></li>
                        <li class="active">Resultado de la Carga</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-body py-8">

                        <div class="dt-alert dt-alert-success">
                            <strong><?php echo (int) $resultado['cargadas']; ?></strong> factura(s) cargada(s) correctamente.
                        </div>

                        <?php if (count($resultado['rechazadas']) > 0): ?>
                            <div class="dt-alert dt-alert-warning">
                                <strong><?php echo count($resultado['rechazadas']); ?></strong> fila(s) rechazada(s):
                            </div>

                            <div class="table-card mb-6 overflow-x-auto">
                                <table class="table align-middle w-full mb-0">
                                    <thead>
                                        <tr>
                                            <th>Número de Factura</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resultado['rechazadas'] as $rechazo): ?>
                                            <tr>
                                                <td class="font-mono text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($rechazo['numero_Factura'] ?: '(sin número)', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-rose-600"><?php echo htmlspecialchars($rechazo['motivo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <a href="dash-invoices-list.php" class="dt-btn-add">Ir al Listado de Facturas</a>

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
