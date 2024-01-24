<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>

    <title>Listado de Servicios | Seguimientos | Chubby - Admin & Dashboard</title>

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

    <!-- Start right Content here -->

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <!-- start page title -->

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Listado de Servicios | Seguimientos</h4>
                        </div>
                    </div>
                </div>

                <!-- start page sub-title -->

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php
                                $query = "SELECT COUNT(*) AS total FROM servicios WHERE estado_Servicio = 1";
                                $result_task1 = mysqli_query($link, $query);
                            while ($row = mysqli_fetch_Array($result_task1)) {
                                ?>
                                <h5 class="card-title">Cantidad de Servicios <span
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
                                <a href="dash-services-add.php" class="btn btn-light"><i
                                        class="bx bx-plus me-1"></i> Agregar Nuevo Servicio</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- start page tabla -->

                <div class="table-responsive mb-4">
                    <table id="datatable-buttons"
                           class="table align-middle datatable dt-responsive table-check nowrap w-100"
                           style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">

                        <thead>
                            <tr>
                                <th scope="col">Número de Servicio</th>
                                <th scope="col">Nombre Cliente</th>
                                <th scope="col">Nombre de la Obra</th>
                                <th scope="col">Factura</th>
                                <th scope="col" class="text-center">Fecha Seguimiento</th>
                                <th style="width:140px; min-width: 140px;" class="text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php
                            $query = "SELECT SR.*, CT.*, CL.*, FT.* -- Selecciona todas las columnas
                                        FROM servicios SR
                                                 JOIN contratos CT ON SR.id_Contrato = CT.id_Contrato
                                                 JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
                                                 LEFT JOIN facturas FT ON CT.id_Contrato = FT.id_Contrato
                                        WHERE estado_Servicio = 1
                                        GROUP BY SR.id_Servicio;";
                            $result_task = mysqli_query($link, $query);
                            while ($row = mysqli_fetch_array($result_task)){
                        ?>

                            <tr>
                                <td>#<?php echo $row['nro_Servicio'] ?></td>
                                <td><?php echo $row['nombre_Cliente'] ?></td>
                                <td><?php echo $row['obra_Contrato'] ?></td>

                                <?php
                                if ($row['id_Factura'] == null){ ?>
                                    <td><div class="badge item-inactivo">No tiene factura</div></td>
                                    <?php
                                }else{
                                    ?>
                                    <td><div class="badge item-activo">Tiene factura</div></td>
                                    <?php
                                }
                                ?>

                                <td style="text-align: center"><?php echo date("d/m/Y", strtotime($row['fecha_Servicio'])); ?></td>
                                <td style="width: 140px; text-align: center">
                                    <a href="dash-services-bath.php?id_Servicio=<?php echo $row['id_Servicio'] ?>" class="btn btn-outline-secondary btn-sm" title="Asignar Baños a Servicios">
                                        <i class="fas fa-toilet"></i>
                                    </a>
                                    <a href="dash-services-print.php?id_Servicio=<?php echo $row['id_Servicio'] ?>" class="btn btn-outline-secondary btn-sm" title="Imprimir">
                                        <i class="fa fa-print"></i>
                                    </a>

                                    <a href="controller/service-inactive.php?id_Servicio=<?php echo $row['id_Servicio'] ?>" class="btn btn-outline-secondary btn-sm" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>

                        <?php
                            }
                        ?>

                        </tbody>

                    </table>
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