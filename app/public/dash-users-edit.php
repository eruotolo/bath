<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\User\FindUser;
use App\Infrastructure\Persistence\MysqliUserRepository;

include('layouts/config.php');
global $link;

$id = (int) $_GET['id_User'];

$useCase = new FindUser(new MysqliUserRepository($link));
$user = $useCase->handle($id);

if ($user !== null) { ?>

<head>
    <title>Editar Usuario | Blanco Servicios - Admin & Dashboard</title>
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
                    <h1>Editar Usuario</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-users-list.php">Usuarios</a></li>
                        <li class="active">Editar Usuario</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Edición de Usuario</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Todos los campos obligatorios
                        </h5>

                        <form action="controller/user-update.php" method="post" enctype="multipart/form-data" class="needs-validation mt-4 pt-2">
                            <input type="number" class="dt-input" id="id" name="id" value="<?php echo (int) $user->id; ?>" hidden>

                            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                <div class="md:col-span-4 text-center">
                                    <img src="uploads/users/<?php echo htmlspecialchars($user->image ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen de Perfil" class="mx-auto mb-3 block h-32 w-32 rounded-full object-cover">
                                    <div class="mb-4">
                                        <div class="dropzone" data-dropzone-target="#file"></div>
                                        <input type="file" id="file" name="file" hidden>
                                    </div>
                                </div>

                                <div class="md:col-span-7 md:col-start-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                        <div class="mb-4">
                                            <label for="useremail" class="dt-label">Email Usuario</label>
                                            <input type="email" class="dt-input" name="useremail" id="useremail" value="<?php echo htmlspecialchars($user->useremail, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>

                                        <div class="mb-4">
                                            <label for="username" class="dt-label">Usuario</label>
                                            <input type="text" class="dt-input" name="username" id="username" value="<?php echo htmlspecialchars($user->username, ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>

                                        <div class="mb-4">
                                            <label for="name" class="dt-label">Nombre</label>
                                            <input type="text" class="dt-input" name="name" id="name" value="<?php echo htmlspecialchars($user->name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>

                                        <div class="mb-4">
                                            <label for="lastname" class="dt-label">Apellido</label>
                                            <input type="text" class="dt-input" name="lastname" id="lastname" value="<?php echo htmlspecialchars($user->lastname ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <button type="submit" name="update" class="dt-btn-add">Actualizar</button>
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

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>

</body>

</html>
<?php
} else {
    echo '<script>alert("Problema al cargar el Servicio")</script>';
}
?>
