<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>

    <title>Clientes | Blanco Servicios - Admin & Dashboard</title>

    <?php include 'layouts/head.php'; ?>

    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
          type="text/css"/>

    <?php include 'layouts/head-style.php'; ?>

    <style>
        #offcanvasNewCustomer.offcanvas {
            transition: transform .45s cubic-bezier(0.22, 1, 0.36, 1);
        }
        .offcanvas-backdrop {
            transition: opacity .45s ease;
        }
    </style>

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
                            <h4 class="mb-sm-0 font-size-18">Listado de Clientes</h4>

                        </div>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php
                            $query = "SELECT COUNT(*) AS total FROM clientes;";
                            $result_task = mysqli_query($link, $query);
                            while ($row = mysqli_fetch_Array($result_task)) {
                                ?>
                                <h5 class="card-title">Clientes <span
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
                                <button type="button" class="btn btn-light" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNewCustomer" aria-controls="offcanvasNewCustomer"><i class="bx bx-plus me-1"></i> Agregar Nuevo Cliente</button>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="table-responsive mb-4">
                    <table id="datatable-buttons"
                           class="table align-middle datatable dt-responsive table-check nowrap w-100"
                           style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">
                        <thead>
                        <tr>
                            <th scope="col" style="width: 40px;">
                                <div class="form-check font-size-16">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                    <label class="form-check-label" for="checkAll"></label>
                                </div>
                            </th>
                            <th scope="col">RUT</th>
                            <th scope="col">Nombre Cliente</th>
                            <th scope="col">Teléfono</th>
                            <th scope="col">Email</th>
                            <th style="width: 80px; min-width: 80px;">Acción</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $query = "SELECT * FROM clientes WHERE estado_Cliente = 1;";
                        $result_task = mysqli_query($link, $query);
                        while ($row = mysqli_fetch_Array($result_task)) {
                            ?>
                            <tr>
                                <th scope="row">
                                    <div class="form-check font-size-16">
                                        <input type="checkbox" class="form-check-input" id="contacusercheck1">
                                        <label class="form-check-label" for="contacusercheck1"></label>
                                    </div>
                                </th>
                                <td>
                                    <a href="dash-customers-item.php?id_Cliente=<?php echo (int)$row['id_Cliente'] ?>"
                                       class="text-body"><?php echo htmlspecialchars($row['rut_Cliente']) ?></a>
                                </td>
                                <td><?php echo htmlspecialchars($row['nombre_Cliente']) ?></td>
                                <td><?php echo htmlspecialchars($row['telefono_Cliente']) ?></td>
                                <td><?php echo htmlspecialchars($row['email_Cliente']) ?></td>
                                <td>
                                    <a href="dash-customers-item.php?id_Cliente=<?php echo (int)$row['id_Cliente'] ?>" class="btn btn-outline-secondary btn-sm" title="Ver">
                                        <i class="fas fas fa-eye"></i>
                                    </a>
                                    <a href="controller/customer-inactive.php?id_Cliente=<?php echo (int)$row['id_Cliente'] ?>" class="btn btn-outline-secondary btn-sm" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>

                </div>

                <!-- Offcanvas: Nuevo Cliente -->
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNewCustomer" aria-labelledby="offcanvasNewCustomerLabel">
                    <div class="offcanvas-header border-bottom">
                        <h5 id="offcanvasNewCustomerLabel" class="offcanvas-title">Nuevo Cliente</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                    </div>
                    <div class="offcanvas-body">
                        <p class="text-muted small mb-4">Los campos con <span class="text-danger">*</span> son obligatorios.</p>

                        <form action="controller/customer-new.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="oc_rut_Cliente" class="form-label">RUT del Cliente <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="oc_rut_Cliente" name="rut_Cliente" placeholder="Ingrese el Rut del cliente" required>
                            </div>

                            <div class="mb-3">
                                <label for="oc_nombre_Cliente" class="form-label">Nombre del Cliente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="oc_nombre_Cliente" name="nombre_Cliente" placeholder="Ingrese el nombre del cliente" required>
                            </div>

                            <div class="mb-3">
                                <label for="oc_telefono_Cliente" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="oc_telefono_Cliente" name="telefono_Cliente" placeholder="Ingrese el número de teléfono" required>
                            </div>

                            <div class="mb-3">
                                <label for="oc_email_Cliente" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="oc_email_Cliente" name="email_Cliente" placeholder="Ingrese un correo electrónico válido" required>
                            </div>

                            <div class="mb-3">
                                <label for="oc_direccion_Cliente" class="form-label">Dirección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="oc_direccion_Cliente" name="direccion_Cliente" placeholder="Ingrese la dirección" required>
                            </div>

                            <div class="mb-3">
                                <label for="oc_region_Cliente" class="form-label">Región</label>
                                <select class="form-select" id="oc_region_Cliente" name="region_Cliente">
                                    <option value="Ninguna" selected>Regiones</option>
                                    <option value="Región de Arica y Parinacota">Región de Arica y Parinacota</option>
                                    <option value="Región de Tarapacá">Región de Tarapacá</option>
                                    <option value="Región de Antofagasta">Región de Antofagasta</option>
                                    <option value="Región de Atacama">Región de Atacama</option>
                                    <option value="Región de Coquimbo">Región de Coquimbo</option>
                                    <option value="Región de Valparaíso">Región de Valparaíso</option>
                                    <option value="Región Metropolitana">Región Metropolitana</option>
                                    <option value="Región de O’Higgins">Región de O’Higgins</option>
                                    <option value="Región del Maule">Región del Maule</option>
                                    <option value="Región del Ñuble">Región del Ñuble</option>
                                    <option value="Región del Biobío">Región del Biobío</option>
                                    <option value="Región de La Araucanía">Región de La Araucanía</option>
                                    <option value="Región de Los Ríos">Región de Los Ríos</option>
                                    <option value="Región de Los Lagos">Región de Los Lagos</option>
                                    <option value="Región de Aysén">Región de Aysén</option>
                                    <option value="Región de Magallanes">Región de Magallanes</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="oc_ciudad_Cliente" class="form-label">Ciudad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="oc_ciudad_Cliente" name="ciudad_Cliente" placeholder="Ingrese la ciudad" required>
                            </div>

                            <div class="mb-3">
                                <label for="oc_comuna_Cliente" class="form-label">Comuna <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="oc_comuna_Cliente" name="comuna_Cliente" placeholder="Ingrese la comuna" required>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" name="crear" class="btn btn-primary">Registrar</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

        </div>
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