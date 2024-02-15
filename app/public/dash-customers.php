<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>

    <title>Clientes | Blanco Servicios - Admin & Dashboard</title>

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
                            <h4 class="mb-sm-0 font-size-18">Listado de Clientes</h4>

                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php
                            $query = "SELECT COUNT(*) AS total FROM clientes;";
                            $result_task = mysqli_query($link, $query);
                            while ($row = mysqli_fetch_Array($result_task)) {
                                ?>
                                <h5 class="card-title">Clientes <span
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
                                <a href="dash-customers-add.php" class="btn btn-light"><i class="bx bx-plus me-1"></i> Agregar Nuevo Cliente</a>
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
                            <th scope="col">RUT</th>
                            <th scope="col">Nombre Cliente</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Email</th>
                            <th style="width: 80px; min-width: 80px;">Acción</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = "SELECT * FROM clientes WHERE estado_Cliente = 1;";
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
                                <td>
                                    <a href="dash-customers-item.php?id_Cliente=<?php echo $row['id_Cliente'] ?>"
                                       class="text-body"><?php echo $row['rut_Cliente'] ?></a>
                                </td>
                                <td><?php echo $row['nombre_Cliente'] ?></td>
                                <td><?php echo $row['telefono_Cliente'] ?></td>
                                <td><?php echo $row['email_Cliente'] ?></td>
                                <td>
                                    <a href="dash-customers-item.php?id_Cliente=<?php echo $row['id_Cliente'] ?>" class="btn btn-outline-secondary btn-sm" title="Ver">
                                        <i class="fas fas fa-eye"></i>
                                    </a>
                                    <a href="controller/customer-inactive.php?id_Cliente=<?php echo $row['id_Cliente'] ?>" class="btn btn-outline-secondary btn-sm" title="Eliminar">
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

<!-- init js -->
<script src="assets/js/pages/datatables.init.js"></script>

<script src="assets/js/app.js"></script>


</body>

</html>