<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>



<head>

    <title>Listado de Baños | Blanco Servicios - Admin & Dashboard</title>

    <?php include 'layouts/head.php'; ?>

    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
          type="text/css"/>

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
                            <h4 class="mb-sm-0 font-size-18">Listado de Baños</h4>

                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php
                            $query = "SELECT COUNT(*) AS total FROM bathrooms;";
                            $result_task = mysqli_query($link, $query);
                            while ($row = mysqli_fetch_Array($result_task)) {
                                ?>
                                <h5 class="card-title">Cantidad de Baños <span
                                            class="text-muted fw-normal ms-2">(<?php echo $row['total'] ?>)</span>
                                </h5>
                                <?php
                            }
                            ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                            <div>
                                <a href="dash-bathrooms-add.php" class="btn btn-light"><i
                                            class="bx bx-plus me-1"></i> Agregar Nuevo Baño</a>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="btn-group" role="group" aria-label="Filtro por estado">
                            <button type="button" class="btn btn-outline-secondary btn-sm filter-estado active" data-estado="all">Todos</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm filter-estado" data-estado="1">Activo</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm filter-estado" data-estado="0">Inactivo</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="btn-group" role="group" aria-label="Filtro por asignación">
                            <button type="button" class="btn btn-outline-secondary btn-sm filter-asignado active" data-asignado="all">Todos</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm filter-asignado" data-asignado="1">Asignado</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm filter-asignado" data-asignado="0">Disponible</button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table id="datatable-buttons"
                           class="table align-middle datatable dt-responsive table-check nowrap w-100"
                           style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;"
                           data-dt-state="true">
                        <thead>
                        <tr>
                            <th scope="col" style="width: 40px;">
                                <div class="form-check font-size-16">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                    <label class="form-check-label" for="checkAll"></label>
                                </div>
                            </th>
                            <th scope="col">Código</th>
                            <th scope="col">Fecha de compra</th>
                            <th scope="col">Observaciones</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Asignado a Obra</th>
                            <th style="width: 80px; min-width: 80px;">Acción</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = "SELECT * FROM bathrooms WHERE estado_Bath IN (0, 1) ORDER BY fechaCompra_Bath DESC";
                        $result_task = mysqli_query($link, $query);
                        while ($row = mysqli_fetch_Array($result_task)) {
                            ?>
                            <tr data-estado="<?php echo (int)$row['estado_Bath'] ?>" data-asignado="<?php echo (int)$row['asignado_Bath'] ?>">
                                <th scope="row">
                                    <div class="form-check font-size-16">
                                        <input type="checkbox" class="form-check-input" id="contacusercheck1">
                                        <label class="form-check-label" for="contacusercheck1"></label>
                                    </div>
                                </th>
                                <td><?php echo htmlspecialchars($row['codigo_Bath']) ?></td>
                                <td><?php echo htmlspecialchars($row['fechaCompra_Bath']) ?></td>
                                <td><?php echo htmlspecialchars($row['observacion_Bath']) ?></td>
                                <?php
                                if ($row['estado_Bath'] == 1) { ?>
                                    <td>
                                        <div class="badge item-activo">Activo</div>
                                    </td>
                                    <?php
                                } else {
                                    ?>
                                    <td>
                                        <div class="badge item-inactivo">Inactivo</div>
                                    </td>
                                    <?php
                                }
                                ?>

                                <?php
                                    if ($row['asignado_Bath'] == 0) {
                                ?>
                                    <td>
                                        <div class="badge item-disponible">Disponible</div>
                                    </td>
                                <?php }else{ ?>
                                    <td>
                                        <div class="badge item-activo">Asignado</div>
                                    </td>
                                <?php } ?>

                                <td>

                                    <a href="dash-bathrooms-edit.php?id_Bath=<?php echo $row['id_Bath'] ?>" class="btn btn-outline-secondary btn-sm" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="controller/bath-active.php?id_Bath=<?php echo $row['id_Bath'] ?>" class="btn btn-outline-secondary btn-sm" title="Activar">
                                        <i class="fas fa-lock-open"></i>
                                    </a>
                                    <a href="controller/bath-inactive.php?id_Bath=<?php echo $row['id_Bath'] ?>" class="btn btn-outline-secondary btn-sm" title="Inactivar">
                                        <i class="fas fa-lock"></i>
                                    </a>
                                    <a href="controller/bath-notassign.php?id_Bath=<?php echo $row['id_Bath'] ?>" class="btn btn-outline-secondary btn-sm" title="Deshacer la asignación">
                                        <i class="fas fa-level-down-alt"></i>
                                    </a>
                                    <a href="controller/bath-delete.php?id_Bath=<?php echo $row['id_Bath'] ?>" class="btn btn-outline-secondary btn-sm" title="Eliminar"
                                       data-confirm-delete
                                       data-confirm-title="¿Eliminar este baño?"
                                       data-confirm-text="Esta acción no se puede deshacer.">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>

                </div>

            </div>

        </div>
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

<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>



<script src="assets/js/app.js"></script>

<script src="assets/js/components/datatable.js"></script>
<script>
	$(document).ready(function () {
		var table = DataTable.init('#datatable-buttons', {
			order: [[ 2, "desc" ]], //Ordenar por columna Fecha Seguimiento (la 5ta columna)
			columnDefs: [ {
				targets: 2, //La columna Fecha Seguimiento
				type: 'date' // Asignarle el tipo de dato Date
			} ],
		});

		var filtroEstado = 'all';
		var filtroAsignado = 'all';

		$.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex, rowData, counter) {
			if (settings.nTable.id !== 'datatable-buttons') {
				return true;
			}
			var $row = $(table.row(dataIndex).node());
			if (filtroEstado !== 'all' && $row.data('estado').toString() !== filtroEstado) {
				return false;
			}
			if (filtroAsignado !== 'all' && $row.data('asignado').toString() !== filtroAsignado) {
				return false;
			}
			return true;
		});

		$('.filter-estado').on('click', function () {
			$('.filter-estado').removeClass('active');
			$(this).addClass('active');
			filtroEstado = $(this).data('estado').toString();
			table.draw();
		});

		$('.filter-asignado').on('click', function () {
			$('.filter-asignado').removeClass('active');
			$(this).addClass('active');
			filtroAsignado = $(this).data('asignado').toString();
			table.draw();
		});
	});

</script>

</body>

</html>

