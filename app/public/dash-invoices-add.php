<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php global $link;  include 'layouts/config.php'; ?>

<head>

    <title>Nueva Factura | Blanco Servicios - Admin & Dashboard Template</title>
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
                            <h4 class="mb-sm-0 font-size-18">Nueva Factura</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-invoices-list.php">Facturas</a></li>
                                    <li class="breadcrumb-item active">Nueva Factura</li>
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
                                <h4 class="card-title">Formulario de Registro de Nueva Factura</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>
                            <div class="card-body">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                <form action="controller/invoice-new.php" method="POST" enctype="multipart/form-data" class="needs-validation mt-4 pt-2">

                                    <div class="row mb-4">
                                        <label for="numero_Factura" class="col-sm-3 col-form-label">Ingrese el Número de Factura:</label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" id="numero_Factura" name="numero_Factura" placeholder="Ingrese el Número de Factura" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="fecha_Factura" class="col-sm-3 col-form-label">Ingrese la Fecha de Factura:</label>
                                        <div class="col-sm-5">
                                            <input type="date" class="form-control" id="fecha_Factura" name="fecha_Factura" placeholder="Ingrese la Fecha de Factura" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="id_Cliente" class="col-sm-3 col-form-label">Seleccione el Cliente:</label>
                                        <div class="col-sm-5">
                                            <select name="id_Cliente" id="id_Cliente" class="form-select">
                                                <option value="">Seleccionar el Cliente</option>
                                                <?php
                                                $sql = "SELECT * FROM clientes";
                                                $result = mysqli_query($link, $sql);
                                                $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                                foreach ($clientes as $cliente) { ?>
                                                    <option value="<?php echo $cliente['id_Cliente']; ?>" >
                                                        <?php echo $cliente['nombre_Cliente']?>
                                                    </option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Segundo select para contratos (inicialmente vacío) -->
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
                                            <input type="number" class="form-control" id="valor_Factura" name="valor_Factura" placeholder="Ingrese el Monto de Factura" required>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button class="btn btn-primary" type="submit" style="width: 200px" name="crear">Crear</button>
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

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>


<<script>
	$(document).ready(function () {
		$('#id_Cliente').change(function () {
			var idCliente = $(this).val();
			// Realizar una solicitud AJAX para obtener contratos basados en el idCliente
			$.ajax({
				url: 'controller/obtener_contratos.php', // Reemplaza con la ruta correcta de tu archivo PHP
				type: 'POST',
				data: {idCliente: idCliente},
				success: function (response) {
					// Actualizar las opciones del segundo select
					$('#id_Contrato').html(response);
				}
			});
		});
	});
</script>

</body>

</html>