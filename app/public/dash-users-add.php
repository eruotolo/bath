<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>
<?php global $link; ?>

<head>

    <title>Usuarios | Blanco Servicios - Admin & Dashboard</title>

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

    <!-- Start right Content Here -->

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Agregar Nuevo Usuario</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dash-users-list.php">Usuarios</a></li>
                                    <li class="breadcrumb-item active">Nuevo Usuario</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">

                            <div class="card-header">
                                <h4 class="card-title">Formulario de Registro de Nuevo Usuario</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>

                            <div class="card-body py-4">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                <form action="controller/user-new.php" method="post" enctype="multipart/form-data"  class="needs-validation mt-4 pt-2">

                                    <div class="row mb-4">
                                        <label for="useremail" class="col-sm-3 col-form-label">Email</label>
                                        <div class="col-sm-5">
                                            <div class="input-group">
                                                <span class="input-group-text" id="basic-addon1">@</span>
                                                <input type="email" class="form-control" id="useremail" placeholder="Ingrese su email" required name="useremail">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="username" class="col-sm-3 col-form-label">Nombre de Usuario</label>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" id="username" placeholder="Ingrese su nombre de usuario" required name="username">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="name" class="col-sm-3 col-form-label">Nombre</label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" id="name" placeholder="Ingrese su nombre" required name="name">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="lastname" class="col-sm-3 col-form-label">Apellido</label>
                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" id="lastname" placeholder="Ingrese su apellido" required name="lastname">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="password" class="col-sm-3 col-form-label">Password</label>
                                        <div class="col-sm-5">
                                            <input type="password" class="form-control" id="password" placeholder="Ingresar password" required name="password">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="file" class="col-sm-3 col-form-label">Imagen de Perfil</label>
                                        <div class="col-sm-5">
                                            <input type="file" class="form-control" id="file" name="file">
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button class="btn btn-primary w-md" type="submit" name="crear">Registrar</button>
                                            </div>
                                        </div>
                                    </div>

                                </form>

                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- End right Content Here -->

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