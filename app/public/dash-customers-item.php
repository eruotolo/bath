<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
include('layouts/config.php');

$id_Cliente = $_GET['id_Cliente'];

$query = "SELECT * FROM  clientes WHERE id_Cliente = $id_Cliente";

$query_run = mysqli_query($link, $query);

if ($query_run) {
    while ($row = mysqli_fetch_array($query_run)) {
        ?>

        <head>

            <title>Perfil Cliente | Chubby - Admin & Dashboard</title>
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
                                    <h4 class="mb-sm-0 font-size-18">Perfil Cliente</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dash-customers.php">Clientes</a></li>
                                            <li class="breadcrumb-item active">Perfil Cliente</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header d-flex">
                                        <div class="col-6">
                                            <h5 class="card-title">Información del Cliente</h5>
                                            <p class="card-title-desc">Datos relevantes del cliente</p>
                                        </div>
                                        <div class="col-6">
                                            <a href="javascript:void(0)"
                                               class="btn btn-light float-end editarCliente"
                                               data-bs-toggle="modal"
                                               data-bs-target="#editarCliente"
                                               data-id="<?php echo $row['id_Cliente']?>"
                                               title="Editar">
                                                <i class='bx bx-edit'></i> Editar Cliente</a>
                                        </div>
                                        <!-- MODAL EDITA CLIENTE-->
                                        <?php include 'layouts/modal-edit-customer.php'; ?>

                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <table>
                                                    <tbody class="table-cliente">
                                                    <tr>
                                                        <td><b>Nombre:</b></td>
                                                        <td><?php echo $row['nombre_Cliente'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>RUT:</b></td>
                                                        <td><?php echo $row['rut_Cliente'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Email:</b></td>
                                                        <td><?php echo $row['email_Cliente'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Teléfono:</b></td>
                                                        <td><?php echo $row['telefono_Cliente'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Dirección:</b></td>
                                                        <td><?php echo $row['direccion_Cliente'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Comuna:</b></td>
                                                        <td><?php echo $row['comuna_Cliente'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Ciudad:</b></td>
                                                        <td><?php echo $row['ciudad_Cliente'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Región:</b></td>
                                                        <td><?php echo $row['region_Cliente'] ?></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header d-flex">
                                        <div class="col-6">
                                            <h5 class="card-title">Contactos del Cliente</h5>
                                            <p class="card-title-desc">Contactos relacionados al cliente</p>
                                        </div>
                                        <div class="col-6 ">
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#nuevoContacto"
                                               data-bs-whatever="@fat" class="btn btn-light float-end"><i class="bx bx-plus me-1"></i> Agregar Nuevo Contacto</a>
                                        </div>
                                        <!-- MODAL NUEVO CONTACTO-->
                                        <?php include 'layouts/modal-nuevo-contacto.php'; ?>

                                    </div>

                                    <div class="card-body">
                                        <div class="row">

                                            <table class="table table-nowrap align-middle">
                                                <thead>
                                                <tr>
                                                    <th>RUT</th>
                                                    <th>Nombre</th>
                                                    <th>Teléfono</th>
                                                    <th>Acciones</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $sql = "SELECT * FROM contactos WHERE id_Cliente = $id_Cliente and estado_Contacto = 1";
                                                        $result_task = mysqli_query($link, $sql);
                                                        while ($row = mysqli_fetch_Array($result_task)) {
                                                            $id_Contacto = $row['id_Contacto'];
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $row['rut_Contacto'] ?></td>
                                                        <td><?php echo $row['nombre_Contacto'] ?> <?php echo $row['apellido_Contacto'] ?></td>
                                                        <td><?php echo $row['telefono_Contacto'] ?></td>
                                                        <td style="width: 100px">

                                                            <!-- Botón para ver el contacto -->
                                                            <a href="javascript:void(0)"
                                                               class="btn btn-outline-secondary btn-sm view"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#verContacto"
                                                               data-id="<?php echo $row['id_Contacto']?>"
                                                               title="Ver">
                                                                <i class="fas fas fa-eye"></i>
                                                            </a>

                                                            <!-- Botón para editar el contacto -->
                                                            <a href="javascript:void(0)"
                                                               class="btn btn-outline-secondary btn-sm editar"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#editarContacto"
                                                               data-id="<?php echo $row['id_Contacto']?>"
                                                               title="Editar">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </a>

                                                            <!-- Botón para eliminar el contacto -->
                                                            <a href="controller/contact-remove.php?id_Contacto=<?php echo $row['id_Contacto'] ?>&id_Cliente=<?php echo $row['id_Cliente'] ?>"
                                                               class="btn btn-outline-secondary btn-sm delete-contacto" title="Eliminar">
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

        <!-- MODAL AGREGAR NUEVO CONTACTO -->

        <?php include 'layouts/modal-ver-contacto.php'; ?>
        <?php include 'layouts/modal-editar-contacto.php'; ?>

        <!-- JAVASCRIPT -->

        <?php include 'layouts/vendor-scripts.php'; ?>
        <script src="assets/js/app.js"></script>

        <script src="assets/js/contactoVer.js"></script>
        <script src="assets/js/contactoEdit.js"></script>
        <script src="assets/js/customer-edit.js"></script>

        </body>

        </html>
        <?php
    }
} else {
    echo '<script>alert ("Problema al cargar el cliente")</script>';
}
?>