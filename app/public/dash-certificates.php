<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>

    <title>Certificados | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>


    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
          type="text/css"/>

</head>

<?php include 'layouts/body.php'; ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'layouts/menu.php'; ?>

        <!-- Start right Content here -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Listado de certificados</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="mb-3">
                                <?php
                                $query = "SELECT COUNT(*) AS total FROM certificados;";
                                $result_task1 = mysqli_query($link, $query);
                                while ($row = mysqli_fetch_Array($result_task1)) {
                                    ?>
                                    <h5 class="card-title">Certificados <span
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
                                    <a href="dash-certificates-add.php" class="btn btn-light"><i class="bx bx-plus me-1"></i> Agregar Nuevo Certificado</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="table-responsive mb-4">
                                <table id="datatable-buttons" class="table align-middle datatable dt-responsive table-check nowrap w-100" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">

                                    <thead>
                                        <tr>
                                            <th scope="col">Nro. Certificado</th>
                                            <th scope="col">Cliente</th>
                                            <th scope="col">RUT Cliente</th>
                                            <th scope="col">Obra</th>
                                            <th scope="col">Fecha del Servicio</th>
                                            <th scope="col" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $query = "SELECT * FROM certificados CR
                                                            JOIN clientes CL ON CR.id_Cliente = CL.id_Cliente
                                                            JOIN contratos CT ON CR.id_Contrato = CT.id_Contrato 
                                                        ORDER BY nro_Certificado";
                                            $result_task = mysqli_query($link, $query);
                                            while ($row = mysqli_fetch_array($result_task)){
                                                //$certificado = $row['fechahoy_Certificado'];
                                                // Generar el número de certificado
                                                $fechaHoy = date("dmY", strtotime($row['fechahoy_Certificado']));
                                                $certificado = $fechaHoy . 'A' . $row['nro_Certificado'];
                                        ?>
                                        <tr>
                                            <td>#<?php echo $certificado ?></td>
                                            <td><?php echo $row['nombre_Cliente'] ?></td>
                                            <td><?php echo $row['rut_Cliente'] ?></td>
                                            <td><?php echo $row['obra_Contrato'] ?></td>
                                            <td><?php echo $row['fecha_Servicio'] ?></td>
                                            <td class="text-center">
                                                <a href="dash-certificates-item.php?id_Certificado=<?php echo $row['id_Certificado'] ?>&id_Contrato=<?php echo $row['id_Contrato'] ?>" class="btn btn-outline-secondary btn-sm" title="Ver">
                                                    <i class="fas fas fa-eye"></i>
                                                </a>
                                                <a href="controller/certificate-remove.php?id_Certificado=<?php echo $row['id_Certificado'] ?>" class="btn btn-outline-secondary btn-sm" title="Eliminar">
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

                </div>
            </div>
        </div>


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
