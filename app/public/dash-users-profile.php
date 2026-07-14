<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Perfil del Usuario | Blanco Servicios - Admin & Dashboard Template</title>
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
                    <h1>Perfil</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-users-list.php">Usuarios</a></li>
                        <li class="active">Perfil</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-body py-8">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                            <div class="order-2 sm:order-1 sm:col-span-9">
                                <div class="flex items-start gap-4 mt-3 sm:mt-0">
                                    <img src="uploads/users/<?php echo htmlspecialchars($_SESSION['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen de Usuario" class="!h-24 !w-24 shrink-0 rounded-full object-cover">
                                    <div class="flex-1 min-w-0">
                                        <h5 class="font-sans text-lg font-bold text-slate-900 mb-1"><?php echo htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['lastname'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h5>
                                        <p class="font-mono mb-3 text-xs uppercase tracking-wider text-primary-600 font-bold">
                                            <?php echo ($_SESSION['category'] ?? 0) == 1 ? 'Administrador' : 'Usuario'; ?>
                                        </p>
                                        <div class="font-sans flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                            <span class="inline-flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                                <?php echo htmlspecialchars($_SESSION['useremail'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="order-1 sm:order-2 sm:col-span-3">
                                <div class="flex justify-end gap-2">
                                    <a href="javascript:void(0)" class="dt-btn-secondary editarCliente mt-3" data-bs-toggle="modal" data-bs-target="#nuevoPassword" title="Nuevo Password">
                                        <i data-lucide="key" class="!mr-1.5 !h-3.5 !w-3.5"></i> Cambiar el Password
                                    </a>
                                    <?php include 'layouts/modal-new-password.php'; ?>
                                </div>
                            </div>
                        </div>
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
