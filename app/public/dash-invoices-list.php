<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>

    <title>Facturas List | Chubby - Admin & Dashboard Template</title>

    <?php include 'layouts/head.php'; ?>

    <!-- flatpickr css -->
    <link href="assets/libs/flatpickr/flatpickr.min.css" rel="stylesheet" type="text/css">

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
                            <h4 class="mb-sm-0 font-size-18">Invoice List</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Invoices</a></li>
                                    <li class="breadcrumb-item active">Invoice List</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="mb-4">
                                            <a href="dash-invoices-add.php">
                                                <button type="button" class="btn btn-light waves-effect waves-light"><i
                                                            class="bx bx-plus me-1"></i> Agregar Nueva Factura
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-sm-auto">
                                        <div class="d-flex align-items-center gap-1 mb-4">
                                            <div class="input-group datepicker-range">
                                                <input type="text" class="form-control flatpickr-input" data-input
                                                       aria-describedby="date1">
                                                <button class="input-group-text" id="date1" data-toggle><i
                                                            class="bx bx-calendar-event"></i></button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!-- end row -->

                                <div class="table-responsive mb-4">
                                    <table id="datatable-buttons"
                                           class="table align-middle datatable dt-responsive table-check nowrap w-100"
                                           style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                                        <thead>
                                        <tr>
                                            <th style="width: 120px;">Nro. Factura</th>
                                            <th style="text-align: center">Fecha Factura</th>
                                            <th>Cliente</th>
                                            <th>Obra</th>
                                            <th style="text-align: center">Monto Factura</th>
                                            <th style="text-align: center">Estado</th>
                                            <th style="width: 150px; text-align: center">Otros</th>
                                            <th style="width: 90px;" >Acciones</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $query = "SELECT * FROM facturas FT 
                                                        JOIN clientes CL ON FT.id_Cliente = CL.id_Cliente 
                                                        JOIN contratos CT ON CL.id_Cliente = CT.id_Cliente    
                                                        order by fecha_Factura";
                                            $result_task = mysqli_query($link, $query);
                                            while ($row = mysqli_fetch_array($result_task)){
                                        ?>
                                        <tr>
                                            <td>#<?php echo $row['numero_Factura'] ?></td>
                                            <td style="text-align: center"><?php echo date("d/m/Y", strtotime($row['fecha_Factura'])); ?></td>
                                            <td><?php echo $row['nombre_Cliente'] ?></td>
                                            <td><?php echo $row['obra_Contrato'] ?></td>
                                            <td style="text-align: center"><?php echo $row['valor_Factura'] ?></td>
                                            <td style="text-align: center">
                                                <?php
                                                    if ($row['estado_Factura'] == 1){
                                                ?>
                                                     <div class="badge badge-soft-warning font-size-12">Pendiente</div>
                                                <?php }elseif ($row['estado_Factura'] == 2){ ?>
                                                     <div class="badge bg-success font-size-12">Pagado</div>
                                                <?php }else{ ?>
                                                     <div class="badge bg-secondary font-size-12">Anulado</div>
                                                <?php } ?>

                                            </td>
                                            <td style="text-align: center">
                                                <a href="dash-invoices-print.php?id_Factura=<?php echo $row['id_Factura'] ?>" class="btn btn-outline-secondary btn-sm" title="Imprimir">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <a href="dash-invoices-detail.php?id_Factura=<?php echo $row['id_Factura'] ?>" class="btn btn-outline-secondary btn-sm" title="Editar">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                            </td>

                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="controller/invoice-estado.php?id_Factura=<?php echo $row['id_Factura'] ?>&estado_Factura=1">Pendiente</a></li>
                                                        <li><a class="dropdown-item" href="controller/invoice-estado.php?id_Factura=<?php echo $row['id_Factura'] ?>&estado_Factura=2">Pagado</a></li>
                                                        <li><a class="dropdown-item" href="controller/invoice-estado.php?id_Factura=<?php echo $row['id_Factura'] ?>&estado_Factura=3">Anulado</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php } ?>

                                        </tbody>
                                    </table>
                                </div>
                                <!-- end table responsive -->
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->

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

<!-- flatpickr js -->
<script src="assets/libs/flatpickr/flatpickr.min.js"></script>

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
