<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>



<head>

    <title>Baños & Contratos | Blanco Servicios - Admin & Dashboard</title>

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
                            <h4 class="mb-sm-0 font-size-18">Baños & Contratos</h4>
                        </div>
                    </div>
                </div>

                <?php
                    $query_contratos_activos = "SELECT BT.codigo_Bath, CT.fechaInicio_Contrato, CT.estado_Contrato,
                                                        CT.obra_Contrato, CL.nombre_Cliente
                                                 FROM contratos CT
                                                     JOIN contrato_bathroom CB ON CB.id_Contrato = CT.id_Contrato
                                                     JOIN bathrooms BT ON BT.id_Bath = CB.id_Bath
                                                     JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente
                                                 WHERE CT.estado_Contrato = 2
                                                   AND CT.fechaInicio_Contrato <= CURDATE()
                                                 ORDER BY BT.codigo_Bath ASC";
                    $result_contratos_activos = mysqli_query($link, $query_contratos_activos);
                    $total_contratos_activos = mysqli_num_rows($result_contratos_activos);

                    $query_banos_disponibles = "SELECT BT.codigo_Bath, BT.fechaCompra_Bath
                                                 FROM bathrooms BT
                                                 WHERE BT.estado_Bath = 1
                                                   AND NOT EXISTS (
                                                       SELECT 1 FROM contrato_bathroom CB
                                                           JOIN contratos CT ON CT.id_Contrato = CB.id_Contrato
                                                       WHERE CB.id_Bath = BT.id_Bath
                                                         AND CT.estado_Contrato = 2
                                                         AND CT.fechaInicio_Contrato <= CURDATE()
                                                   )
                                                 ORDER BY BT.codigo_Bath ASC";
                    $result_banos_disponibles = mysqli_query($link, $query_banos_disponibles);
                    $total_banos_disponibles = mysqli_num_rows($result_banos_disponibles);
                ?>

                <div class="card">
                    <div class="card-body">

                        <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#tab-contratos-activos" role="tab">
                                    Todos los contratos activos
                                    <span class="badge bg-primary rounded-pill ms-1"><?php echo $total_contratos_activos ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-banos-disponibles" role="tab">
                                    Todos los baños disponibles
                                    <span class="badge bg-primary rounded-pill ms-1"><?php echo $total_banos_disponibles ?></span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content pt-4">

                            <!-- Tab: Todos los contratos activos -->
                            <div class="tab-pane active" id="tab-contratos-activos" role="tabpanel">
                                <p class="text-muted mb-3">Cantidad de contratos activos: <strong><?php echo $total_contratos_activos ?></strong></p>
                                <div class="table-responsive mb-4">
                                    <table id="datatable-contratos-activos"
                                           class="table align-middle datatable dt-responsive table-check nowrap w-100"
                                           style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                                        <thead>
                                        <tr>
                                            <th scope="col">Código de Baño</th>
                                            <th scope="col">Fecha de Inicio de Contrato</th>
                                            <th scope="col">Estado de Contrato</th>
                                            <th scope="col">Asignado a Obra</th>
                                            <th scope="col">Nombre de la Obra</th>
                                            <th scope="col">Nombre del Cliente</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $result_task = $result_contratos_activos;
                                            while ($row = mysqli_fetch_array($result_task)) {
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['codigo_Bath']) ?></td>
                                                <td><?php echo htmlspecialchars($row['fechaInicio_Contrato']) ?></td>
                                                <td><div class="badge item-activo">Activo</div></td>
                                                <td><div class="badge item-activo">Asignado</div></td>
                                                <td><?php echo htmlspecialchars($row['obra_Contrato']) ?></td>
                                                <td><?php echo htmlspecialchars($row['nombre_Cliente']) ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab: Todos los baños disponibles -->
                            <div class="tab-pane" id="tab-banos-disponibles" role="tabpanel">
                                <p class="text-muted mb-3">Cantidad de baños disponibles: <strong><?php echo $total_banos_disponibles ?></strong></p>
                                <div class="table-responsive mb-4">
                                    <table id="datatable-banos-disponibles"
                                           class="table align-middle datatable dt-responsive table-check nowrap w-100"
                                           style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                                        <thead>
                                        <tr>
                                            <th scope="col">Código del Baño</th>
                                            <th scope="col">Fecha de Compra</th>
                                            <th scope="col">Estado</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $result_task = $result_banos_disponibles;
                                            while ($row = mysqli_fetch_array($result_task)) {
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['codigo_Bath']) ?></td>
                                                <td><?php echo htmlspecialchars($row['fechaCompra_Bath']) ?></td>
                                                <td><div class="badge item-disponible">Disponible</div></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>
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
<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script src="assets/js/app.js"></script>

<script>
    $(document).ready(function () {
        var dtLanguage = {
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ entradas',
            info: 'Mostrando _PAGE_ de _PAGES_ páginas',
            infoEmpty: 'Mostrando 0 a 0 de 0 elementos',
            infoFiltered: '(filtrado de _MAX_ elementos en total)',
            emptyTable: 'No hay datos disponibles en la tabla',
            loadingRecords: 'Cargando...',
            zeroRecords: 'No se encontraron registros coincidentes',
            paginate: {
                first: 'Primero',
                previous: 'Anterior',
                next: 'Siguiente',
                last: 'Último',
            },
        };

        $('#datatable-contratos-activos').DataTable({
            lengthMenu: [[50, 100, -1], [50, 100, 'All']],
            order: [[0, 'asc']], // Ordenar por Código de Baño
            language: dtLanguage,
        });

        $('#datatable-banos-disponibles').DataTable({
            lengthMenu: [[50, 100, -1], [50, 100, 'All']],
            order: [[0, 'asc']], // Ordenar por Código de Baño
            language: dtLanguage,
        });

        $('.dataTables_length select').addClass('form-select form-select-sm');
    });
</script>

</body>

</html>
