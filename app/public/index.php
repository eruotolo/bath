<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title><?php echo $language["Dashboard"]; ?> | Chubby - Admin & Dashboard Template</title>

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
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Chubby</a></li>
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
                                    <div class="col-6">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">My Wallet</span>
                                        <h4 class="mb-3">
                                            $<span class="counter-value" data-target="865.7">0</span>k
                                        </h4>
                                    </div>

                                    <div class="col-6">
                                        <div id="mini-chart1" data-colors='["#2D5C6C"]' class="apex-charts mb-2"></div>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+$20.9k</span>
                                    <span class="ms-1 text-muted font-size-13">Since last week</span>
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
                                    <div class="col-6">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Number of Trades</span>
                                        <h4 class="mb-3">
                                            <span class="counter-value" data-target="6258">0</span>
                                        </h4>
                                    </div>
                                    <div class="col-6">
                                        <div id="mini-chart2" data-colors='["#2D5C6C"]' class="apex-charts mb-2"></div>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-danger-subtle text-danger">-29 Trades</span>
                                    <span class="ms-1 text-muted font-size-13">Since last week</span>
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
                                    <div class="col-6">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Invested Amount</span>
                                        <h4 class="mb-3">
                                            $<span class="counter-value" data-target="4.32">0</span>M
                                        </h4>
                                    </div>
                                    <div class="col-6">
                                        <div id="mini-chart3" data-colors='["#2D5C6C"]' class="apex-charts mb-2"></div>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+ $2.8k</span>
                                    <span class="ms-1 text-muted font-size-13">Since last week</span>
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
                                    <div class="col-6">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Profit Ration</span>
                                        <h4 class="mb-3">
                                            <span class="counter-value" data-target="12.57">0</span>%
                                        </h4>
                                    </div>
                                    <div class="col-6">
                                        <div id="mini-chart4" data-colors='["#2D5C6C"]' class="apex-charts mb-2"></div>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+2.95%</span>
                                    <span class="ms-1 text-muted font-size-13">Since last week</span>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div><!-- end row-->

                <div class="row">
                    <div class="col-xl-6">
                        <!-- card -->
                        <div class="card card-h-100">
                            <!-- card body -->
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center mb-4">
                                    <h5 class="card-title me-2">Wallet Balance</h5>
                                    <div class="ms-auto">
                                        <div>
                                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                                ALL
                                            </button>
                                            <button type="button" class="btn btn-soft-primary btn-sm">
                                                1M
                                            </button>
                                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                                6M
                                            </button>
                                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                                1Y
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center">
                                    <div class="col-sm">
                                        <div id="wallet-balance" data-colors='["#777aca", "#2D5C6C", "#a8aada"]'
                                             class="apex-charts"></div>
                                    </div>
                                    <div class="col-sm align-self-center">
                                        <div class="mt-4 mt-sm-0">
                                            <div>
                                                <p class="mb-2"><i
                                                            class="mdi mdi-circle align-middle font-size-10 me-2 text-success"></i> Bitcoin
                                                </p>
                                                <h6>0.4412 BTC = <span
                                                            class="text-muted font-size-14 fw-normal">$ 4025.32</span>
                                                </h6>
                                            </div>

                                            <div class="mt-4 pt-2">
                                                <p class="mb-2"><i
                                                            class="mdi mdi-circle align-middle font-size-10 me-2 text-primary"></i> Ethereum
                                                </p>
                                                <h6>4.5701 ETH = <span
                                                            class="text-muted font-size-14 fw-normal">$ 1123.64</span>
                                                </h6>
                                            </div>

                                            <div class="mt-4 pt-2">
                                                <p class="mb-2"><i
                                                            class="mdi mdi-circle align-middle font-size-10 me-2 text-info"></i> Litecoin
                                                </p>
                                                <h6>35.3811 LTC = <span
                                                            class="text-muted font-size-14 fw-normal">$ 2263.09</span>
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                    <div class="col-xl-6">
                        <div class="row">
                            <div class="col-xl-12">
                                <!-- card -->
                                <div class="card card-h-100">
                                    <!-- card body -->
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap align-items-center mb-4">
                                            <h5 class="card-title me-2">Invested Overview</h5>
                                            <div class="ms-auto">
                                                <select class="form-select form-select-sm">
                                                    <option value="MAY" selected="">May</option>
                                                    <option value="AP">April</option>
                                                    <option value="MA">March</option>
                                                    <option value="FE">February</option>
                                                    <option value="JA">January</option>
                                                    <option value="DE">December</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center">
                                            <div class="col-sm">
                                                <div id="invested-overview" data-colors='["#2D5C6C", "#34c38f"]'
                                                     class="apex-charts"></div>
                                            </div>
                                            <div class="col-sm align-self-center">
                                                <div class="mt-4 mt-sm-0">
                                                    <p class="mb-1">Invested Amount</p>
                                                    <h4>$ 6134.39</h4>

                                                    <p class="text-muted mb-4"> + 0.0012.23 ( 0.2 % ) <i
                                                                class="mdi mdi-arrow-up ms-1 text-success"></i></p>

                                                    <div class="row g-0">
                                                        <div class="col-6">
                                                            <div>
                                                                <p class="mb-2 text-muted text-uppercase font-size-11">Income</p>
                                                                <h5 class="fw-medium">$ 2632.46</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div>
                                                                <p class="mb-2 text-muted text-uppercase font-size-11">Expenses</p>
                                                                <h5 class="fw-medium">-$ 924.38</h5>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mt-2">
                                                        <a href="#" class="btn btn-primary btn-sm">View more <i
                                                                    class="mdi mdi-arrow-right ms-1"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end col -->
                </div> <!-- end row-->

                <div class="row">
                    <div class="col-xl-8">
                        <!-- card -->
                        <div class="card">
                            <!-- card body -->
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center mb-4">
                                    <h5 class="card-title me-2">Market Overview</h5>
                                    <div class="ms-auto">
                                        <div>
                                            <button type="button" class="btn btn-soft-primary btn-sm">
                                                ALL
                                            </button>
                                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                                1M
                                            </button>
                                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                                6M
                                            </button>
                                            <button type="button" class="btn btn-soft-secondary btn-sm">
                                                1Y
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center">
                                    <div class="col-xl-8">
                                        <div>
                                            <div id="market-overview" data-colors='["#2D5C6C", "#34c38f"]'
                                                 class="apex-charts"></div>
                                        </div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="p-4">
                                            <div class="mt-0">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm m-auto">
                                                        <span class="avatar-title rounded-circle bg-light-subtle text-dark font-size-16">
                                                            1
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <span class="font-size-16">Coinmarketcap</span>
                                                    </div>

                                                    <div class="flex-shrink-0">
                                                        <span class="badge rounded-pill bg-success-subtle text-success fw-medium">+2.5%</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm m-auto">
                                                        <span class="avatar-title rounded-circle bg-light-subtle text-dark font-size-16">
                                                            2
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <span class="font-size-16">Binance</span>
                                                    </div>

                                                    <div class="flex-shrink-0">
                                                        <span class="badge rounded-pill bg-success-subtle text-success fw-medium">+8.3%</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm m-auto">
                                                        <span class="avatar-title rounded-circle bg-light-subtle text-dark font-size-16">
                                                            3
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <span class="font-size-16">Coinbase</span>
                                                    </div>

                                                    <div class="flex-shrink-0">
                                                        <span class="badge rounded-pill bg-danger-subtle text-danger font-size-12 fw-medium">-3.6%</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm m-auto">
                                                        <span class="avatar-title rounded-circle bg-light-subtle text-dark font-size-16">
                                                            4
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <span class="font-size-16">Yobit</span>
                                                    </div>

                                                    <div class="flex-shrink-0">
                                                        <span class="badge rounded-pill bg-success-subtle text-success fw-medium">+7.1%</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm m-auto">
                                                        <span class="avatar-title rounded-circle bg-light-subtle text-dark font-size-16">
                                                            5
                                                        </span>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <span class="font-size-16">Bitfinex</span>
                                                    </div>

                                                    <div class="flex-shrink-0">
                                                        <span class="badge rounded-pill bg-danger-subtle text-danger font-size-12 fw-medium">-0.9%</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-4 pt-2">
                                                <a href="" class="btn btn-primary w-100">See All Balances <i
                                                            class="mdi mdi-arrow-right ms-1"></i></a>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end col -->
                    </div>
                    <!-- end row-->

                    <div class="col-xl-4">
                        <!-- card -->
                        <div class="card">
                            <!-- card body -->
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center mb-4">
                                    <h5 class="card-title me-2">Sales by Locations</h5>
                                    <div class="ms-auto">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle text-reset" href="#" id="dropdownMenuButton1"
                                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="text-muted font-size-12">Sort By:</span> <span
                                                        class="fw-medium">World<i class="mdi mdi-chevron-down ms-1"></i></span>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-end"
                                                 aria-labelledby="dropdownMenuButton1">
                                                <a class="dropdown-item" href="#">USA</a>
                                                <a class="dropdown-item" href="#">Russia</a>
                                                <a class="dropdown-item" href="#">Australia</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="sales-by-locations" data-colors='["#2D5C6C"]' style="height: 250px"></div>

                                <div class="px-2 py-2">
                                    <p class="mb-1">USA <span class="float-end">75%</span></p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                             style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="75">
                                        </div>
                                    </div>

                                    <p class="mt-3 mb-1">Russia <span class="float-end">55%</span></p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                             style="width: 55%" aria-valuenow="55" aria-valuemin="0" aria-valuemax="55">
                                        </div>
                                    </div>

                                    <p class="mt-3 mb-1">Australia <span class="float-end">85%</span></p>
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar progress-bar-striped bg-primary" role="progressbar"
                                             style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="85">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row-->


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