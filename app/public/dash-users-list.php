<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>

<head>

    <title>Usuarios | Chubby - Admin & Dashboard</title>

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
                            <h4 class="mb-sm-0 font-size-18">Lista de Usuarios</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Usuarios</a></li>
                                    <li class="breadcrumb-item active">Lista de Usuarios</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- end page title -->

                <div class="row align-items-center">


                    <div class="col-md-12">
                        <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                            <div>
                                <a href="dash-users-add.php" class="btn btn-light"><i class="bx bx-plus me-1"></i> Agregar Nuevo Usuario</a>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- end row -->

                <div class="table-responsive mb-4">
                    <table class="table align-middle datatable dt-responsive table-check nowrap"
                           style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                        <thead>
                        <tr>
                            <th scope="col" style="width: 50px;">
                                <div class="form-check font-size-16">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                    <label class="form-check-label" for="checkAll"></label>
                                </div>
                            </th>
                            <th scope="col">Nombre de Usuario</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Email</th>
                            <th scope="col">Categoría</th>
                            <th style="width: 80px; min-width: 80px;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = "SELECT *,U.id as 'uid' FROM users U
                                    JOIN category C
                                    on U.category = C.id_category
                                    WHERE state = 1 ORDER BY name asc";
                        $result_task = mysqli_query($link, $query);
                        while ($row = mysqli_fetch_Array($result_task))  {
                            ?>
                        <tr>
                            <th scope="row">
                                <div class="form-check font-size-16">
                                    <input type="checkbox" class="form-check-input" id="contacusercheck1">
                                    <label class="form-check-label" for="contacusercheck1"></label>
                                </div>
                            </th>
                            <td>
                                <img src="uploads/users/<?php echo $row['image'];?>" alt="" class="avatar-sm rounded-circle me-2">
                                <a href="#" class="text-body"><?php echo $row['username'];?></a>
                            </td>
                            <td><?php echo $row['name'];?> <?php echo $row['lastname'];?></td>
                            <td><?php echo $row['useremail'];?></td>
                            <td><?php echo $row['name_category'];?></td>

                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle"
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">

                                        <?php
                                        if ($_SESSION['category'] == 1 ){
                                            ?>
                                            <li><a class="dropdown-item" href="dash-users-edit.php?id_User=<?php echo $row['id'] ?>">Editar</a></li>
                                            <li><a class="dropdown-item" href="controller/user-inactive.php?id_User=<?php echo $row['id'] ?>">Inactivar</a></li>
                                            <li><a class="dropdown-item" href="controller/user-default-pass.php?id_User=<?php echo $row['id'] ?>">Password Default</a></li>
                                            <li><a class="dropdown-item cat-admin" href="controller/user-setadmin.php?id_User=<?php echo $row['id'] ?>&category=<?php echo $row['id_category'] ?>" style="color: #5156be">Set Admin/User</a></li>
                                            <?php
                                        }else{
                                            ?>
                                            <li><a class="dropdown-item disabled" href="">Editar</a></li>
                                            <li><a class="dropdown-item disabled" href="">Inactivar</a></li>
                                            <li><a class="dropdown-item disabled" href="">Reset Password</a></li>
                                        <?php
                                            }
                                        ?>

                                    </ul>
                                </div>
                            </td>

                        </tr>
                        <?php
                            }
                        ?>
                        </tbody>
                    </table>
                    <!-- end table -->
                </div>
                <!-- end table responsive -->

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

<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<!-- init js -->
<script src="assets/js/pages/datatable-pages.init.js"></script>

<script src="assets/js/app.js"></script>

</body>

</html>