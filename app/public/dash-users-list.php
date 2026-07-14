<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\User\ListActiveUsers;
use App\Infrastructure\Persistence\MysqliUserRepository;

include 'layouts/config.php';

$useCase = new ListActiveUsers(new MysqliUserRepository($link));
$usuarios = $useCase->handle();
?>

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

                <div class="space-y-4">
                    <div class="table-toolbar">
                        <h5 class="table-toolbar-title">Usuarios <span class="count">(<?php echo count($usuarios); ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="table-toolbar-search"></div>
                            <a href="dash-users-add.php" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Nuevo Usuario</a>
                        </div>
                    </div>

                    <div class="table-card mb-4">
                        <table id="datatable-buttons" class="table align-middle datatable dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th scope="col">Nombre de Usuario</th>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Categoría</th>
                                    <th style="width: 80px; min-width: 80px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <img src="uploads/users/<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="!h-8 !w-8 rounded-full object-cover">
                                                <span class="font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                        </td>
                                        <td class="font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($row['lastname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="font-sans text-sm text-slate-600"><?php echo htmlspecialchars($row['useremail'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge-status is-info"><?php echo htmlspecialchars($row['name_category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="dash-users-edit.php?id_User=<?php echo (int) $row['id']; ?>" class="dt-cell-action" title="Editar">
                                                    <i data-lucide="square-pen"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                        <i data-lucide="more-horizontal"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <?php if ($_SESSION['category'] == 1): ?>
                                                            <li><a class="dropdown-item" href="controller/user-default-pass.php?id_User=<?php echo (int) $row['id']; ?>"><i data-lucide="key" class="!mr-2 !h-[14px] !w-[14px]"></i>Password Default</a></li>
                                                            <li><a class="dropdown-item cat-admin" href="controller/user-setadmin.php?id_User=<?php echo (int) $row['id']; ?>&category=<?php echo (int) $row['category']; ?>"><i data-lucide="shield" class="!mr-2 !h-[14px] !w-[14px]"></i>Set Admin/User</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="controller/user-inactive.php?id_User=<?php echo (int) $row['id']; ?>" data-confirm-delete data-confirm-title="¿Inactivar este usuario?" data-confirm-text="No podrá iniciar sesión hasta que se reactive."><i data-lucide="lock" class="!mr-2 !h-[14px] !w-[14px]"></i>Inactivar</a></li>
                                                        <?php else: ?>
                                                            <li><span class="dropdown-item disabled"><i data-lucide="square-pen" class="!mr-2 !h-[14px] !w-[14px]"></i>Editar</span></li>
                                                            <li><span class="dropdown-item disabled"><i data-lucide="lock" class="!mr-2 !h-[14px] !w-[14px]"></i>Inactivar</span></li>
                                                            <li><span class="dropdown-item disabled"><i data-lucide="key" class="!mr-2 !h-[14px] !w-[14px]"></i>Reset Password</span></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script src="assets/js/app.js"></script>
<script src="assets/js/components/datatable.js"></script>
<script>
    $(document).ready(function () {
        DataTable.init('#datatable-buttons', {
            buttons: [
                { extend: 'collection', text: 'Exportar', buttons: ['copy', 'excel', 'pdf'] },
                { extend: 'colvis', text: 'Visibilidad de columnas' },
            ],
        });
    });
</script>

</body>
</html>
