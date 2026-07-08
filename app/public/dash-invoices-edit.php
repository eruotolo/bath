<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php global $link; include 'layouts/config.php'; ?>

<?php
if (!isset($_GET['id_Factura']) || !is_numeric($_GET['id_Factura'])) {
    header("Location: dash-invoices-list.php");
    exit();
}

$id_Factura = intval($_GET['id_Factura']);

$sql = "SELECT * FROM facturas WHERE id_Factura = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_Factura);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$factura = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$factura) {
    header("Location: dash-invoices-list.php");
    exit();
}
?>

<head>

    <title>Editar Factura | Blanco Servicios - Admin & Dashboard Template</title>
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
                            <h4 class="mb-sm-0 font-size-18">Editar Factura</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-invoices-list.php">Facturas</a></li>
                                    <li class="breadcrumb-item active">Editar Factura</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Formulario de Edición de Factura</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>
                            <div class="card-body">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Modificar datos en los campos</h5>

                                <form action="controller/invoice-update.php" method="POST" class="needs-validation mt-4 pt-2">

                                    <input type="hidden" name="id_Factura" value="<?php echo (int)$factura['id_Factura'] ?>">

                                    <div class="row mb-4">
                                        <label for="numero_Factura" class="col-sm-3 col-form-label">Ingrese el Número de Factura:</label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" id="numero_Factura" name="numero_Factura" value="<?php echo htmlspecialchars($factura['numero_Factura']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fecha_Factura" class="col-sm-3 col-form-label">Ingrese la Fecha de Factura:</label>
                                        <div class="col-sm-5">
                                            <input type="date" class="form-control" id="fecha_Factura" name="fecha_Factura" value="<?php echo htmlspecialchars($factura['fecha_Factura']) ?>" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="id_Cliente" class="col-sm-3 col-form-label">Seleccione el Cliente:</label>
                                        <div class="col-sm-5">
                                            <select name="id_Cliente" id="id_Cliente" class="form-select">
                                                <option value="">Seleccionar el Cliente</option>
                                                <?php
                                                $sqlClientes = "SELECT * FROM clientes ORDER BY nombre_Cliente ASC";
                                                $resultClientes = mysqli_query($link, $sqlClientes);
                                                $clientes = mysqli_fetch_all($resultClientes, MYSQLI_ASSOC);
                                                foreach ($clientes as $cliente) { ?>
                                                    <option value="<?php echo (int)$cliente['id_Cliente']; ?>" <?php echo ((int)$cliente['id_Cliente'] === (int)$factura['id_Cliente']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cliente['nombre_Cliente']) ?>
                                                    </option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Segundo select para contratos, precargado vía JS con el contrato actual -->
                                    <div class="row mb-4">
                                        <label for="id_Contrato" class="col-sm-3 col-form-label">Seleccione el Contrato:</label>
                                        <div class="col-sm-5">
                                            <select name="id_Contrato" id="id_Contrato" class="form-select">
                                                <!-- Opciones se cargarán dinámicamente con JavaScript -->
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="valor_Factura" class="col-sm-3 col-form-label">Ingrese el Monto de Factura:</label>
                                        <div class="col-sm-5">
                                            <input type="number" class="form-control" id="valor_Factura" name="valor_Factura" value="<?php echo (int)$factura['valor_Factura'] ?>" required>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button class="btn btn-primary" type="submit" style="width: 200px" name="editar">Guardar Cambios</button>
                                            </div>
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

<script>
	var idContratoActual = <?php echo json_encode((int)$factura['id_Contrato']); ?>;

	function cargarContratos(idCliente, seleccionar) {
		$.ajax({
			url: 'controller/obtener_contratos.php',
			type: 'POST',
			data: {idCliente: idCliente},
			success: function (response) {
				$('#id_Contrato').html(response);
				if (seleccionar) {
					$('#id_Contrato').val(seleccionar);
				}
			}
		});
	}

	$(document).ready(function () {
		cargarContratos($('#id_Cliente').val(), idContratoActual);

		$('#id_Cliente').change(function () {
			cargarContratos($(this).val(), null);
		});
	});
</script>

</body>

</html>
