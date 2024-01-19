<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>

    <title>Contratos | Chubby - Admin & Dashboard</title>
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
                            <h4 class="mb-sm-0 font-size-18">Listado de contratos</h4>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php
                                $query = "SELECT COUNT(*) AS total FROM contratos;";
                                $result_task1 = mysqli_query($link, $query);
                                while ($row = mysqli_fetch_Array($result_task1)) {
                                    ?>
                                    <h5 class="card-title">Contratos <span
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
                                <a href="dash-contracts-add.php" class="btn btn-light"><i class="bx bx-plus me-1"></i> Agregar Nuevo Contrato</a>
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
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Obra</th>
                                        <th scope="col" style="text-align: center;">Estado</th>
                                        <th scope="col" style="text-align: center;">Fecha de Inicio</th>
                                        <th scope="col" style="text-align: center;">Fecha de Fin</th>
                                        <th scope="col" style="text-align: center;">Valor Mensual</th>
                                        <th scope="col" style="text-align: center;">Valor Total</th>
                                        <th style="width: 80px; min-width: 80px; text-align: center;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $query = "SELECT * FROM contratos CO JOIN clientes CL on CO.id_Cliente = CL.id_Cliente WHERE estado_Contrato IN (1, 2)  ORDER BY id_Contrato";
                                        $result_task = mysqli_query($link, $query);
                                        while ($row = mysqli_fetch_array($result_task)){
                                    ?>
                                    <tr>

                                        <td><?php echo $row['nombre_Cliente'] ?></td>
                                        <td><?php echo $row['obra_Contrato'] ?></td>

                                        <?php
                                            if ($row['estado_Contrato'] == 2){ ?>
                                                <td class="text-center"><div class="badge item-activo">Activo</div></td>
                                        <?php
                                            }else{
                                        ?>
                                                <td class="text-center"><div class="badge item-inactivo">Inactivo</div></td>
                                        <?php
                                            }
                                        ?>

                                        <td class="text-center"><?php echo date("d/m/Y", strtotime($row['fechaInicio_Contrato'])); ?></td>
                                        <td class="text-center"><?php echo date("d/m/Y", strtotime($row['fechaFin_Contrato'])); ?></td>
                                        <td class="text-center">$ <?php echo $row['valorMensual_Contrato'] ?></td>
                                        <td class="text-center">$ <?php echo $row['valorTotal_Contrato'] ?></td>
                                        <td class="text-center">
                                            <a href="dash-contracts-item.php?id_Contrato=<?php echo $row['id_Contrato'] ?>" class="btn btn-outline-secondary btn-sm" title="Agregar Baños Químicos">
                                                <i class="fas fa-toilet"></i>
                                            </a>
                                            <a href="dash-contracts-edit.php?id_Contrato=<?php echo $row['id_Contrato'] ?>" class="btn btn-outline-secondary btn-sm" title="Editar">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            <a href="controller/contract-inactive.php?id_Contrato=<?php echo $row['id_Contrato'] ?>" class="btn btn-outline-secondary btn-sm" title="Inactivar">
                                                <i class="fas fa-lock"></i>
                                            </a>
                                            <a href="controller/contract-active.php?id_Contrato=<?php echo $row['id_Contrato'] ?>" class="btn btn-outline-secondary btn-sm" title="Activar">
                                                <i class="fas fa-lock-open"></i>
                                            </a>
                                            <a href="controller/contract-down.php?id_Contrato=<?php echo $row['id_Contrato'] ?>" class="btn btn-outline-secondary btn-sm" title="Eliminar">
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