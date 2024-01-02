<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>

    <title>Nuevo Cliente | Chubby - Admin & Dashboard</title>

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
                                    <li class="breadcrumb-item active">Agregar Cliente</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Formulario de Registro de Nuevo Cliente</h4>
                                <p class="card-title-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                            </div>
                            <div class="card-body py-4">
                                <h5 class="font-size-14 mb-4"><i class="mdi mdi-arrow-right text-primary me-1"></i> Ingresar datos en los campos</h5>

                                <form class="needs-validation mt-4 pt-2" action="controller/customer-new.php" method="post" enctype="multipart/form-data" style="margin-left: 15%">

                                    <div class="row mb-4">
                                        <label for="rut_Cliente" class="col-sm-3 col-form-label">RUT del Cliente</label>
                                        <div class="col-sm-5">
                                            <input type="number" class="form-control" id="rut_Cliente" placeholder="Ingrese el Rut del cliente" required name="rut_Cliente">
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="nombre_Cliente" class="col-sm-3 col-form-label">Nombre del Cliente</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="nombre_Cliente" name="nombre_Cliente" placeholder="Ingrese el nombre del cliente" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="telefono_Cliente" class="col-sm-3 col-form-label">Teléfono del Cliente</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="number" id="telefono_Cliente" name="telefono_Cliente" placeholder="Ingrese el número de teléfono" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="email_Cliente" class="col-sm-3 col-form-label">Email del Cliente</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="email" id="email_Cliente" name="email_Cliente" placeholder="Ingrese el un correo electrónico valido" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="direccion_Cliente" class="col-sm-3 col-form-label">Dirección del Cliente</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="direccion_Cliente" name="direccion_Cliente" placeholder="Ingrese la dirección" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="region_Cliente" class="col-sm-3 col-form-label">Región</label>
                                        <div class="col-sm-5">
                                            <select class="form-select" name="region_Cliente" id="region_Cliente" >
                                                <option value="Ninguna" selected="selected">Regiones</option>
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
                                    </div>

                                    <div class="row mb-4">
                                        <label for="ciudad_Cliente" class="col-sm-3 col-form-label">Ciudad</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="ciudad_Cliente" name="ciudad_Cliente" placeholder="Ingrese la ciudad" required>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <label for="comuna_Cliente" class="col-sm-3 col-form-label">Comuna</label>
                                        <div class="col-sm-5">
                                            <input class="form-control" type="text" id="comuna_Cliente" name="comuna_Cliente" placeholder="Ingrese la comuna" required>
                                        </div>
                                    </div>

                                    <div class="row justify-content-end">
                                        <div class="col-sm-9">
                                            <div>
                                                <button class="btn btn-primary" type="submit" style="width: 200px" name="crear">Registrar</button>
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