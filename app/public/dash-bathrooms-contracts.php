<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>



<head>

    <title>Listado de Baños con Contratos | Blanco Servicios - Admin & Dashboard</title>

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
                            <h4 class="mb-sm-0 font-size-18">Listado de Baños & Contratos</h4>

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

                <div class="table-responsive mb-4">
                    <table id="datatable-buttons"
                           class="table align-middle datatable dt-responsive table-check nowrap w-100"
                           style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                        <thead>
                        <tr>
                            <th scope="col" style="width: 40px;">
                                <div class="form-check font-size-16">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                    <label class="form-check-label" for="checkAll"></label>
                                </div>
                            </th>
                            <th scope="col">Código</th>
                            <th scope="col">Fecha Inicio de Contrato</th>
                            <th scope="col">Observaciones</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Asignado a Obra</th>
                            <th scope="col">Nombre de Obra</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = "SELECT * FROM bathrooms BT
                                 JOIN contrato_bathroom CB ON BT.id_Bath = CB.id_Bath
                                 JOIN contratos CT ON CB.id_Contrato = CT.id_Contrato
                                 WHERE BT.estado_Bath = 1 ORDER BY fechaCompra_Bath DESC";
                        $result_task = mysqli_query($link, $query);
                        while ($row = mysqli_fetch_Array($result_task)) {
                            ?>
                            <tr>
                                <th scope="row">
                                    <div class="form-check font-size-16">
                                        <input type="checkbox" class="form-check-input" id="contacusercheck1">
                                        <label class="form-check-label" for="contacusercheck1"></label>
                                    </div>
                                </th>
                                <td><?php echo $row['codigo_Bath'] ?></td>
                                <td><?php echo date("d/m/Y", strtotime($row['fechaInicio_Contrato'])); ?></td>
                                <td><?php echo $row['observacion_Bath'] ?></td>
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
                                        <div class="badge item-inactivo">No Asignado</div>
                                    </td>
                                <?php }else{ ?>
                                    <td>
                                        <div class="badge item-activo">Asignado</div>
                                    </td>
                                <?php } ?>

                                <td><?php echo $row['obra_Contrato'] ?></td>
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

<!-- init js -->
<script src="assets/js/pages/datatables.init.js"></script>

<script src="assets/js/app.js"></script>

</body>

</html>

