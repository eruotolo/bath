<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

include('layouts/config.php');
global $link;

$id = $_GET['id_User'];

$query = "SELECT * FROM users U JOIN category C ON U.category = C.id_category WHERE id = $id";

$query_run = mysqli_query($link, $query);

if ($query_run){
    while ($row = mysqli_fetch_array($query_run)) { ?>

        <head>

            <title>Editar Usuario | Blanco Servicios - Admin & Dashboard</title>

            <?php include 'layouts/head.php'; ?>
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

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0 font-size-18">Editar Usuarios</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dash-users-list.php">Usuarios</a></li>
                                            <li class="breadcrumb-item active">Editar Usuarios</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">

                                    <div class="card-header">
                                        <div class="col-6">
                                            <h4 class="card-title">Formulario de Edición de Usuario</h4>
                                            <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                                        </div>
                                    </div>

                                    <div class="card-body py-4">
                                        <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Todos los campos obligatorios</h5>

                                        <form action="controller/user-update.php" method="post" enctype="multipart/form-data"  class="needs-validation mt-4 pt-2">

                                            <input type="number" class="form-control" id="id" name="id" value="<?php echo $row['id'];?>" hidden>

                                            <div class="row">

                                                <div class="col-4 justify-content-center align-items-center img-perfil" style="margin-right: 40px">
                                                    <img src="uploads/users/<?php echo $row['image']?>" alt="Imagen de Perfil" class="img-fluid rounded-circle d-block" style="margin-bottom: 20px">
                                                    <input type="file" class="form-control" id="file" name="file" >
                                                </div>

                                                <div class="col-6">
                                                    <div class="row mb-4">
                                                        <label for="useremail" class="col-sm-5 col-form-label">Email Usuario:</label>
                                                        <div class="col-sm-6">
                                                            <input type="email" class="form-control" name="useremail" id="useremail" value="<?php echo $row['useremail'];?>">
                                                        </div>
                                                    </div>

                                                    <div class="row mb-4">
                                                        <label for="username" class="col-sm-5 col-form-label">Usuario:</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" class="form-control" name="username" id="username" value="<?php echo $row['username'];?>">
                                                        </div>
                                                    </div>

                                                    <div class="row mb-4">
                                                        <label for="name" class="col-sm-5 col-form-label">Nombre del Usuario:</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" class="form-control" name="name" id="name" value="<?php echo $row['name'];?>">
                                                        </div>
                                                    </div>

                                                    <div class="row mb-4">
                                                        <label for="lastname" class="col-sm-5 col-form-label">Apellido del Usuario:</label>
                                                        <div class="col-sm-6">
                                                            <input type="text" class="form-control" name="lastname" id="lastname" value="<?php echo $row['lastname'];?>">
                                                        </div>
                                                    </div>

                                                    <div class="row justify-content-end">
                                                        <div class="col-sm-7">
                                                            <div>
                                                                <button type="submit" name="update" class="btn btn-primary w-md">Actualizar</button>
                                                            </div>
                                                        </div>
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

        <!-- Right Sidebar -->
        <?php include 'layouts/right-sidebar.php'; ?>
        <!-- /Right-bar -->

        <!-- JAVASCRIPT -->
        <?php include 'layouts/vendor-scripts.php'; ?>
        <script src="assets/js/app.js"></script>

        </body>

        </html>

<?php
    }
}else{
    echo '<script>alert ("Problema al cargar el Servicio")</script>';
}
?>