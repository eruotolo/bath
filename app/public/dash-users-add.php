<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include 'layouts/config.php'; ?>
<?php global $link; ?>

<head>
    <title>Usuarios | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="dt-page-title">
                    <h1>Agregar Nuevo Usuario</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-users-list.php">Usuarios</a></li>
                        <li class="active">Nuevo Usuario</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Registro de Nuevo Usuario</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form action="controller/user-new.php" method="post" enctype="multipart/form-data" class="needs-validation mt-4 pt-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="useremail" class="dt-label">Email</label>
                                    <div class="dt-input-group">
                                        <span class="dt-input-prefix" id="basic-addon1">@</span>
                                        <input type="email" class="dt-input flex-1 rounded-l-none" id="useremail" placeholder="Ingrese su email" required name="useremail">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="username" class="dt-label">Nombre de Usuario</label>
                                    <input type="text" class="dt-input" id="username" placeholder="Ingrese su nombre de usuario" required name="username">
                                </div>

                                <div class="mb-4">
                                    <label for="name" class="dt-label">Nombre</label>
                                    <input type="text" class="dt-input" id="name" placeholder="Ingrese su nombre" required name="name">
                                </div>

                                <div class="mb-4">
                                    <label for="lastname" class="dt-label">Apellido</label>
                                    <input type="text" class="dt-input" id="lastname" placeholder="Ingrese su apellido" required name="lastname">
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="dt-label">Password</label>
                                    <input type="password" class="dt-input" id="password" placeholder="Ingresar password" required name="password">
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label class="dt-label">Imagen de Perfil</label>
                                    <div class="dropzone" data-dropzone-target="#file"></div>
                                    <input type="file" id="file" name="file" hidden>
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
