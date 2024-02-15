<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<head>
    <title>Dashboard | Blanco Servicios - Admin & Dashboard Template</title>

    <?php include 'layouts/head.php'; ?>

    <link href="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet"
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
                            <h4 class="mb-sm-0 font-size-18">Tablero</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Blanco Servicios</a></li>
                                    <li class="breadcrumb-item active">Tablero</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-h-100">
                            <!-- card body -->
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Mis Baños</span>
                                        <h4 class="mb-3">
                                            <?php
                                                $query = "SELECT COUNT(*) AS total FROM bathrooms;";
                                                $result_task = mysqli_query($link, $query);
                                                while ($row = mysqli_fetch_Array($result_task)) {
                                            ?>
                                            <span>Total de <?php echo $row['total'] ?></span>
                                            <?php } ?>
                                        </h4>
                                    </div>
                                </div>

                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-h-100">
                            <!-- card body -->
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Clientes</span>
                                        <h4 class="mb-3">
                                            <?php
                                            $query = "SELECT COUNT(*) AS clientes FROM clientes;";
                                            $result_task = mysqli_query($link, $query);
                                            while ($row = mysqli_fetch_Array($result_task)) {
                                            ?>
                                            <span>Total de <?php echo $row['clientes'] ?></span>
                                            <?php } ?>
                                        </h4>
                                    </div>

                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col-->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-h-100">
                            <!-- card body -->
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Contratos</span>
                                        <h4 class="mb-3">
                                            <?php
                                            $query = "SELECT COUNT(*) AS contratos FROM contratos;";
                                            $result_task = mysqli_query($link, $query);
                                            while ($row = mysqli_fetch_Array($result_task)) {
                                                ?>
                                                <span>Total de <?php echo $row['contratos'] ?></span>
                                            <?php } ?>
                                        </h4>
                                    </div>

                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-h-100">
                            <!-- card body -->
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Servicios</span>
                                        <h4 class="mb-3">
                                            <?php
                                            $query = "SELECT COUNT(*) AS servicios FROM servicios;";
                                            $result_task = mysqli_query($link, $query);
                                            while ($row = mysqli_fetch_Array($result_task)) {
                                                ?>
                                                <span>Total de <?php echo $row['servicios'] ?></span>
                                            <?php } ?>
                                        </h4>
                                    </div>
                                </div>

                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div><!-- end row-->


            </div>
            <!-- container-fluid -->
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

<!-- apexcharts -->
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>

<!-- Plugins js-->
<script src="assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="assets/libs/admin-resources/jquery.vectormap/maps/jquery-jvectormap-world-mill-en.js"></script>

<!-- dashboard init -->
<script src="assets/js/pages/dashboard.init.js"></script>

<!-- App js -->
<script src="assets/js/app.js"></script>

</body>

</html>