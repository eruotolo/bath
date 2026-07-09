<?php
session_start();
include 'layouts/session.php';
?>
<?php include 'layouts/head-main.php'; ?>
<?php global $link; include 'layouts/config.php'; ?>
<?php include 'layouts/helpers.php'; ?>

<?php
if (!isset($_SESSION['carga_facturas']) || count($_SESSION['carga_facturas']) === 0) {
    header('Location: dash-invoices-upload.php');
    exit();
}

$filas = $_SESSION['carga_facturas'];

$mensajes_error = [
    'rut_no_encontrado' => 'RUT no encontrado en Clientes',
    'numero_factura_vacio' => 'Falta el número de factura',
    'fecha_invalida' => 'Fecha inválida',
    'monto_invalido' => 'Monto inválido',
];

$cantidad_validas = count(array_filter($filas, fn($f) => $f['error'] === null));
$cantidad_error = count($filas) - $cantidad_validas;
?>

<head>

    <title>Previsualizar Carga de Facturas | Blanco Servicios - Admin & Dashboard Template</title>
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
                            <h4 class="mb-sm-0 font-size-18">Previsualizar Carga de Facturas</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-invoices-list.php">Facturas</a></li>
                                    <li class="breadcrumb-item active">Previsualizar Carga</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <strong><?php echo $cantidad_validas ?></strong> factura(s) lista(s) para cargar.
                            <?php if ($cantidad_error > 0): ?>
                                <strong class="text-danger"><?php echo $cantidad_error ?></strong> fila(s) con error no se van a cargar (marcadas en rojo).
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <form action="controller/invoice-upload-confirm.php" method="POST" id="form-confirmar">

                                    <div class="table-responsive mb-4">
                                        <table class="table align-middle mb-0">
                                            <thead>
                                            <tr>
                                                <th>Nro Factura</th>
                                                <th>Fecha Factura</th>
                                                <th>Cliente Nombre</th>
                                                <th>Obra</th>
                                                <th class="text-end">Monto Factura</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($filas as $indice => $fila): ?>
                                                <tr class="<?php echo $fila['error'] ? 'table-danger' : '' ?>">
                                                    <td><?php echo htmlspecialchars($fila['numero_Factura']) ?></td>
                                                    <td><?php echo htmlspecialchars($fila['fecha_Factura'] ?? $fila['fecha_original']) ?></td>
                                                    <td>
                                                        <?php if ($fila['error']): ?>
                                                            <span class="text-danger"><?php echo htmlspecialchars($mensajes_error[$fila['error']] ?? 'Error desconocido') ?></span>
                                                            <?php if ($fila['error'] === 'rut_no_encontrado'): ?>
                                                                <div class="text-muted" style="font-size: 0.8rem;">RUT en el archivo: <?php echo htmlspecialchars($fila['rut_original']) ?></div>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <?php echo htmlspecialchars($fila['nombre_Cliente']) ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if (!$fila['error']): ?>
                                                            <?php if (count($fila['contratos']) === 0): ?>
                                                                <span class="text-danger">Sin obras activas para este cliente</span>
                                                            <?php else: ?>
                                                                <select name="obra[<?php echo $indice ?>]" class="form-select form-select-sm select-obra" required>
                                                                    <option value="">Seleccionar la obra</option>
                                                                    <?php foreach ($fila['contratos'] as $contrato): ?>
                                                                        <option value="<?php echo (int)$contrato['id_Contrato'] ?>">
                                                                            <?php echo htmlspecialchars($contrato['obra_Contrato']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            —
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end"><?php echo is_numeric($fila['valor_Factura']) ? format_clp($fila['valor_Factura']) : htmlspecialchars($fila['valor_Factura']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <a href="controller/invoice-upload-cancel.php" class="btn btn-secondary">Cancelar</a>
                                        <button type="submit" class="btn btn-primary" id="btn-confirmar" disabled>Confirmar Carga</button>
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

<script>
	function actualizarBotonConfirmar() {
		var selects = document.querySelectorAll('.select-obra');
		var todasCompletas = true;
		selects.forEach(function (select) {
			if (select.value === '') {
				todasCompletas = false;
			}
		});
		document.getElementById('btn-confirmar').disabled = !todasCompletas || selects.length === 0;
	}

	document.querySelectorAll('.select-obra').forEach(function (select) {
		select.addEventListener('change', actualizarBotonConfirmar);
	});

	actualizarBotonConfirmar();
</script>

</body>

</html>
