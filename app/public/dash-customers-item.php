<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Customer\FindCustomer;
use App\Application\Contact\ListContactsByCustomer;
use App\Infrastructure\Persistence\MysqliCustomerRepository;
use App\Infrastructure\Persistence\MysqliContactRepository;

include('layouts/config.php');

$id_Cliente = (int) $_GET['id_Cliente'];

$customer = (new FindCustomer(new MysqliCustomerRepository($link)))->handle($id_Cliente);

if ($customer !== null) {
    $contactos = (new ListContactsByCustomer(new MysqliContactRepository($link)))->handle($id_Cliente);
    ?>

        <head>

            <title>Perfil Cliente | Blanco Servicios - Admin & Dashboard</title>
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
                                               data-id="<?php echo $customer->id?>"
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
                                                        <td><?php echo htmlspecialchars($customer->name) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>RUT:</b></td>
                                                        <td><?php echo htmlspecialchars($customer->rut) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Email:</b></td>
                                                        <td><?php echo htmlspecialchars($customer->email) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Teléfono:</b></td>
                                                        <td><?php echo htmlspecialchars($customer->phone) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Dirección:</b></td>
                                                        <td><?php echo htmlspecialchars($customer->address) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Comuna:</b></td>
                                                        <td><?php echo htmlspecialchars($customer->commune) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Ciudad:</b></td>
                                                        <td><?php echo htmlspecialchars($customer->city) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b>Región:</b></td>
                                                        <td><?php echo htmlspecialchars($customer->region) ?></td>
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
                                                        foreach ($contactos as $contacto) {
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($contacto->rut) ?></td>
                                                        <td><?php echo htmlspecialchars($contacto->name) ?> <?php echo htmlspecialchars($contacto->lastname) ?></td>
                                                        <td><?php echo htmlspecialchars($contacto->phone) ?></td>
                                                        <td style="width: 100px">

                                                            <!-- Botón para ver el contacto -->
                                                            <a href="javascript:void(0)"
                                                               class="btn btn-outline-secondary btn-sm view"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#verContacto"
                                                               data-id="<?php echo $contacto->id?>"
                                                               title="Ver">
                                                                <i class="fas fas fa-eye"></i>
                                                            </a>

                                                            <!-- Botón para editar el contacto -->
                                                            <a href="javascript:void(0)"
                                                               class="btn btn-outline-secondary btn-sm editar"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#editarContacto"
                                                               data-id="<?php echo $contacto->id?>"
                                                               title="Editar">
                                                                <i class="fas fa-pencil-alt"></i>
                                                            </a>

                                                            <!-- Botón para eliminar el contacto -->
                                                            <a href="controller/contact-remove.php?id_Contacto=<?php echo $contacto->id ?>&id_Cliente=<?php echo $contacto->customerId ?>"
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

        <script>
            ModalEditor.init({
                triggerSelector: '.editarCliente',
                endpoint: 'controller/customer-get.php',
                idParam: 'id_Cliente',
                fieldMap: {
                    idCliente: 'id_Cliente',
                    rutCliente: 'rut_Cliente',
                    nombreCliente: 'nombre_Cliente',
                    telefonoCliente: 'telefono_Cliente',
                    emailCliente: 'email_Cliente',
                    direccionCliente: 'direccion_Cliente',
                    comunaCliente: 'comuna_Cliente',
                    ciudadCliente: 'ciudad_Cliente',
                    regionCliente: 'region_Cliente',
                },
            });

            ModalEditor.init({
                triggerSelector: '.editar',
                endpoint: 'controller/contact-get.php',
                idParam: 'id_Contacto',
                fieldMap: {
                    idC: 'id_Contacto',
                    idCC: 'id_Cliente',
                    nombreC: 'nombre_Contacto',
                    apellidoC: 'apellido_Contacto',
                    rutC: 'rut_Contacto',
                    telefonoC: 'telefono_Contacto',
                    direccionC: 'direccion_Contacto',
                    observacionC: 'observacion_Contacto',
                },
            });

            ModalEditor.init({
                triggerSelector: '.view',
                endpoint: 'controller/contact-get.php',
                idParam: 'id_Contacto',
                fieldMap: {
                    id: 'id_Contacto',
                    nombre: 'nombre_Contacto',
                    apellido: 'apellido_Contacto',
                    rut: 'rut_Contacto',
                    telefono: 'telefono_Contacto',
                    direccion: 'direccion_Contacto',
                    observacion: 'observacion_Contacto',
                },
            });
        </script>

        </body>

        </html>
        <?php
} else {
    echo '<script>alert ("Problema al cargar el cliente")</script>';
}
?>