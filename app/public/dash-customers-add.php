<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>

    <title>Nuevo Cliente | Blanco Servicios - Admin & Dashboard</title>

    <?php include 'layouts/head.php'; ?>

    <?php include 'layouts/head-style.php'; ?>

</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">

    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="dt-page-title">
                    <h1>Perfil Cliente</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-customers.php">Clientes</a></li>
                        <li class="active">Agregar Cliente</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Registro de Nuevo Cliente</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>
                    <div class="dt-card-body py-6">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form class="needs-validation mt-4 pt-2" action="controller/customer-new.php" method="post" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="rut_Cliente" class="dt-label">RUT del Cliente</label>
                                    <input type="text" class="dt-input" id="rut_Cliente" placeholder="12.345.678-9" required name="rut_Cliente" data-rut-mask>
                                </div>

                                <div class="mb-4">
                                    <label for="nombre_Cliente" class="dt-label">Nombre del Cliente</label>
                                    <input class="dt-input" type="text" id="nombre_Cliente" name="nombre_Cliente" placeholder="Ingrese el nombre del cliente" required>
                                </div>

                                <div class="mb-4">
                                    <label for="telefono_Cliente" class="dt-label">Teléfono del Cliente</label>
                                    <input class="dt-input" type="number" id="telefono_Cliente" name="telefono_Cliente" placeholder="Ingrese el número de teléfono" required>
                                </div>

                                <div class="mb-4">
                                    <label for="email_Cliente" class="dt-label">Email del Cliente</label>
                                    <input class="dt-input" type="email" id="email_Cliente" name="email_Cliente" placeholder="Ingrese un correo electrónico válido" required>
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="direccion_Cliente" class="dt-label">Dirección del Cliente</label>
                                    <input class="dt-input" type="text" id="direccion_Cliente" name="direccion_Cliente" placeholder="Ingrese la dirección" required>
                                </div>

                                <div class="mb-4">
                                    <label for="region_Cliente" class="dt-label">Región</label>
                                    <select class="dt-select" name="region_Cliente" id="region_Cliente" data-enhanced-select data-search-placeholder="Buscar región...">
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

                                <div class="mb-4">
                                    <label for="ciudad_Cliente" class="dt-label">Ciudad</label>
                                    <input class="dt-input" type="text" id="ciudad_Cliente" name="ciudad_Cliente" placeholder="Ingrese la ciudad" required>
                                </div>

                                <div class="mb-4">
                                    <label for="comuna_Cliente" class="dt-label">Comuna</label>
                                    <input class="dt-input" type="text" id="comuna_Cliente" name="comuna_Cliente" placeholder="Ingrese la comuna" required>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button class="dt-btn-add" type="submit" name="crear">Registrar</button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>

</body>

</html>
