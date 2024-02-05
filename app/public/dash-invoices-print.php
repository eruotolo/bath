<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

include('layouts/config.php');
global $link;

$id_Factura = $_GET['id_Factura'];

    $query = "SELECT * FROM facturas FT
        JOIN clientes CL ON FT.id_Cliente = CL.id_Cliente
        JOIN contratos CT ON CL.id_Cliente = CT.id_Cliente
    WHERE id_Factura = $id_Factura";
    $query_run = mysqli_query($link, $query);

    if ($query_run) {
    $row = mysqli_fetch_array($query_run);
?>

<head>

    <title>Detalle de Imprimir | Chubby - Admin & Dashboard Template</title>
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
                            <h4 class="mb-sm-0 font-size-18">Detalle de Factura</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-invoices-list.php">Facturas</a></li>
                                    <li class="breadcrumb-item active">Detalle de Factura</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="card">
                        <div class="card-body">

                            <div class="invoice-title">
                                <div class="d-flex align-items-start pt-4">
                                    <div class="flex-grow-1">
                                        <div class="mb-4 logo-zl">
                                            <img src="assets/images/logo_zl.png" alt="" height="120">
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="mb-4 logo-rc">
                                            <img src="assets/images/logo_rc.png" height="120" alt="Rata">
                                        </div>
                                    </div>

                                </div>

                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1 info-empresa" >
                                        <p class="mb-1">Blanco Servicios e Inversiones Spa</p>
                                        <p class="mb-1"><i class="mdi mdi-email align-middle me-1"></i> jsanchez@expanda.cl</p>
                                        <p><i class="mdi mdi-phone align-middle me-1"></i> +56 9 9293 7371</p>
                                    </div>
                                    <div class="flex-shrink-0 info-nrofactura" >
                                        <div class="mb-4">
                                            <h4 class="float-end">N° DE FACTURA: <?php echo $row['numero_Factura'] ?></h4>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <hr class="my-4">

                            <div class="row info-factura">
                                <div class="col-sm-6">
                                    <div>
                                        <h5 class="font-size-15 mb-3">Factura a:</h5>
                                        <h5 class="font-size-14 mb-2"><?php echo $row['nombre_Cliente'] ?></h5>
                                        <p class="mb-1">RUT: <?php echo $row['rut_Cliente'] ?></p>
                                        <p class="mb-1">Dir: <?php echo $row['direccion_Cliente'] ?></p>
                                        <p class="mb-1">Email: <?php echo $row['email_Cliente'] ?></p>
                                        <p>Teléfono: +<?php echo $row['telefono_Cliente'] ?></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div>
                                        <div>
                                            <h5 class="font-size-15">Fecha de factura:</h5>
                                            <p>
                                                <?php echo date("d/m/Y", strtotime($row['fecha_Factura'])); ?>
                                            </p>
                                        </div>

                                        <div class="mt-4">
                                            <h5 class="font-size-15">Contrato:</h5>
                                            <p class="mb-1"><?php echo $row['obra_Contrato'] ?></p>
                                            <p>Dir: <?php echo $row['direccion_Contrato'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="py-2 mt-3">
                                <h5 class="font-size-15">Detalle de Factura:</h5>
                            </div>

                            <div class="p-4 border rounded">

                                <div class="table-responsive pt-4 pb-4">
                                    <table class="table table-nowrap align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Número de Servicio</th>
                                                <th>Fecha de Servicio</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                $sql = "SELECT * FROM factura_servicio FS 
                                                            JOIN facturas FT ON FS.id_Factura = FT.id_Factura
                                                            JOIN servicios SR ON FS.id_Servicio = SR.id_Servicio
                                                        WHERE FS.id_Factura = $id_Factura";
                                                $result_tasks = mysqli_query($link, $sql);
                                                while ($rows = mysqli_fetch_Array($result_tasks)) {
                                            ?>
                                            <tr>
                                                <td><?php echo $rows['nro_Servicio'] ?></td>
                                                <td><?php echo date("d/m/Y", strtotime($rows['fecha_Servicio'])); ?></td>
                                                <td><?php echo $rows['observaciones_Servicio'] ?></td>
                                            </tr>
                                            <?php }?>
                                        </tbody>
                                    </table>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex justify-content-end">
                                    <h4><b>Total:</b> $<?php echo number_format($row['valor_Factura'], 0, ',', '.'); ?></h4>
                                </div>

                            </div>

                            <div class="d-print-none mt-3">
                                <div class="float-end">
                                    <a href="javascript:window.print()"
                                       class="btn btn-success waves-effect waves-light me-1"><i
                                                class="fa fa-print"></i></a>
                                </div>
                            </div>


                        </div>
                    </div>

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

<script src="assets/js/app.js"></script>

</body>

</html>

<?php
    } else {
        echo '<script>alert ("Problema al cargar la Factura")</script>';
    }
?>