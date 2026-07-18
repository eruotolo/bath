<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php
// Rol del usuario en sesión — soporta los 3 roles (1=Admin, 2=Usuario, 3=SuperAdmin).
$roleCategory = (int) ($_SESSION['category'] ?? 0);
$roleLabel = match (true) {
    $roleCategory === 1 => 'Administrador',
    $roleCategory === 2 => 'Usuario',
    $roleCategory === 3 => 'SuperAdministrador',
    default => 'Usuario',
};
// SuperAdmin se distingue con badge esmeralda; el resto usa is-info (calco de dash-users-list.php).
$roleBadge = $roleCategory === 3 ? 'is-success' : 'is-info';
?>

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
            <div class="container-fluid px-10 py-10 bg-slate-50/50">

                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-8">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-center">
                        <div class="order-2 sm:order-1 sm:col-span-9">
                            <div class="flex items-center gap-5">
                                <img src="uploads/users/<?php echo htmlspecialchars($_SESSION['image'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="Imagen de Usuario" class="h-24 w-24 shrink-0 rounded-full object-cover">
                                <div class="flex-1 min-w-0">
                                    <h5 class="font-sans text-xl font-bold text-slate-900 mb-2"><?php echo htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['lastname'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <div class="mb-3">
                                        <span class="badge-status <?php echo $roleBadge; ?>"><?php echo htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
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
                            <div class="flex justify-end">
                                <a href="javascript:void(0)" class="dt-btn-secondary editarCliente" data-bs-toggle="modal" data-bs-target="#nuevoPassword" title="Nuevo Password">
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

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>

</body>
</html>
