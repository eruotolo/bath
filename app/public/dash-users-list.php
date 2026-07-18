<?php global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\User\ListActiveUsers;
use App\Application\User\FindUser;
use App\Infrastructure\Persistence\MysqliUserRepository;

include 'layouts/config.php';

$useCase = new ListActiveUsers(new MysqliUserRepository($link));
$usuarios = $useCase->handle();

// --- Drawer Nuevo/Editar Usuario (calco de dash-services.php:71-100) ---
// Reemplaza a dash-users-add.php / dash-users-edit.php (no se borran, quedan sin uso desde la UI).
$drawerAction = $_GET['action'] ?? '';
$drawerMode = in_array($drawerAction, ['new', 'edit'], true) && ($drawerAction === 'new' || isset($_GET['id_User']))
    ? $drawerAction
    : null;
$drawerError = isset($_GET['err']) ? (string) $_GET['err'] : '';
$isNew = $drawerMode === 'new';
$isEdit = $drawerMode === 'edit';

$editUser = null;
if ($isEdit && isset($_GET['id_User']) && ctype_digit((string) $_GET['id_User'])) {
    $editUser = (new FindUser(new MysqliUserRepository($link)))->handle((int) $_GET['id_User']);
    if ($editUser === null) {
        $drawerMode = null;
        $isEdit = false;
    }
}

// Categorías disponibles (query de solo lectura, sin parámetros de usuario)
$categorias = mysqli_fetch_all(
    mysqli_query($link, 'SELECT id_category, name_category FROM category ORDER BY id_category ASC'),
    MYSQLI_ASSOC
);

$closeDrawerQs = baseQueryString(['action', 'err', 'id_User']);
$closeDrawerUrl = 'dash-users-list.php' . ($closeDrawerQs !== '' ? '?' . ltrim($closeDrawerQs, '&') : '');

function baseQueryString(array $excludes = ['page']): string {
    $params = [];
    foreach ($_GET as $k => $v) {
        if (!in_array($k, $excludes, true) && $v !== '' && $v !== null) {
            $params[] = $k . '=' . urlencode((string) $v);
        }
    }
    return $params ? '&' . implode('&', $params) : '';
}
?>

<head>
    <title>Personal y Roles | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid px-10 py-10 bg-slate-50/50">

                <div class="space-y-4">
                    <!-- Toolbar: título + buscador (native-table.js) + Agregar Usuario -->
                    <div class="table-toolbar">
                        <h5 class="table-toolbar-title">Personal y Roles <span class="count">(<?php echo count($usuarios); ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="table-toolbar-search">
                                <div class="relative">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                                    <input
                                        type="text"
                                        id="tabla-usuarios-search"
                                        data-table-search-input="#tabla-usuarios"
                                        placeholder="Buscar por usuario, nombre, email..."
                                        class="w-full sm:w-64 pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors font-sans"
                                    >
                                </div>
                            </div>
                            <a href="dash-users-list.php?action=new" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Usuario</a>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden" data-table-native-wrap>
                        <div class="overflow-x-auto">
                            <table id="tabla-usuarios" class="w-full text-left border-collapse" data-per-page="10" data-item-label="Usuarios">
                                <thead>
                                    <tr class="border-b border-slate-50 bg-slate-50/50">
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Usuario</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Nombre</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Email</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase">Categoría</th>
                                        <th scope="col" class="px-6 py-4 font-mono text-[10px] font-bold text-slate-400 tracking-wider uppercase text-right whitespace-nowrap">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <?php foreach ($usuarios as $row): ?>
                                        <?php
                                        $searchable = htmlspecialchars(
                                            $row['username'] . ' ' .
                                            $row['name'] . ' ' .
                                            $row['lastname'] . ' ' .
                                            $row['useremail'] . ' ' .
                                            $row['name_category'],
                                            ENT_QUOTES, 'UTF-8'
                                        );
                                        ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors group" data-search="<?php echo $searchable; ?>">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <img src="uploads/users/<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" class="!h-8 !w-8 rounded-full object-cover">
                                                    <span class="font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($row['lastname'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 font-sans text-sm text-slate-600"><?php echo htmlspecialchars($row['useremail'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="badge-status is-info"><?php echo htmlspecialchars($row['name_category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="inline-flex items-center justify-end gap-1">
                                                    <?php if ($_SESSION['category'] == 1): ?>
                                                        <a href="dash-users-list.php?action=edit&id_User=<?php echo (int) $row['id']; ?>" class="dt-cell-action" title="Editar">
                                                            <i data-lucide="square-pen"></i>
                                                        </a>
                                                        <div class="dropdown">
                                                            <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                                <i data-lucide="more-horizontal"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end m-0 min-w-[220px] list-none overflow-hidden rounded-2xl border border-slate-100 bg-white p-2 shadow-xl shadow-slate-200/50">
                                                                <li><a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="controller/user-default-pass.php?id_User=<?php echo (int) $row['id']; ?>"><i data-lucide="key" class="!h-[14px] !w-[14px] shrink-0"></i>Password Default</a></li>
                                                                <li><hr class="dropdown-divider m-1 border-slate-100"></li>
                                                                <li>
                                                                    <a class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-rose-500 hover:bg-rose-50 hover:text-rose-500" href="controller/user-inactive.php?id_User=<?php echo (int) $row['id']; ?>" data-confirm-delete data-confirm-title="¿Inactivar este usuario?" data-confirm-text="No podrá iniciar sesión hasta que se reactive.">
                                                                        <i data-lucide="lock" class="!h-[14px] !w-[14px] shrink-0"></i>Inactivar
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="dt-cell-action opacity-50 cursor-not-allowed pointer-events-none" title="Sin permisos para editar">
                                                            <i data-lucide="square-pen"></i>
                                                        </span>
                                                        <div class="dropdown">
                                                            <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                                <i data-lucide="more-horizontal"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end m-0 min-w-[220px] list-none overflow-hidden rounded-2xl border border-slate-100 bg-white p-2 shadow-xl shadow-slate-200/50">
                                                                <li><span class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-300 cursor-not-allowed"><i data-lucide="key" class="!h-[14px] !w-[14px] shrink-0"></i>Password Default</span></li>
                                                                <li><hr class="dropdown-divider m-1 border-slate-100"></li>
                                                                <li><span class="dropdown-item flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 font-sans text-[13px] text-slate-300 cursor-not-allowed"><i data-lucide="lock" class="!h-[14px] !w-[14px] shrink-0"></i>Inactivar</span></li>
                                                            </ul>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($usuarios) === 0): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                                No hay usuarios activos registrados.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-50 flex items-center justify-between" data-table-native-pagination>
                            <span class="font-mono text-[10px] text-slate-400 font-bold uppercase" data-table-native-summary></span>
                            <div class="flex items-center space-x-1" data-table-native-pages></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php if ($drawerMode !== null): ?>
    <!-- Drawer Nuevo/Editar Usuario (#user-drawer) -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 transition-opacity" onclick="window.location='<?php echo $closeDrawerUrl; ?>'"></div>
    <div class="fixed inset-y-0 right-0 w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transform transition-transform duration-300 ease-out translate-x-0 app-drawer" id="user-drawer">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl <?php echo $isEdit ? 'bg-indigo-100 text-indigo-700' : 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/10'; ?> flex items-center justify-center">
                    <i data-lucide="<?php echo $isEdit ? 'square-pen' : 'user-plus'; ?>" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-sans font-bold text-slate-900 text-sm">
                        <?php if ($isEdit): ?>
                            Editar Usuario
                        <?php else: ?>
                            Registrar Nuevo Usuario
                        <?php endif; ?>
                    </h3>
                    <span class="font-sans text-[10px] text-slate-400 block mt-0.5">
                        <?php echo $isEdit ? 'Modificar datos del usuario.' : 'Crear un nuevo usuario del sistema.'; ?>
                    </span>
                </div>
            </div>
            <a href="<?php echo $closeDrawerUrl; ?>" class="p-1.5 rounded-lg hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition-all" aria-label="Cerrar panel">
                <i data-lucide="x" class="w-5 h-5"></i>
            </a>
        </div>

        <?php if ($drawerError !== ''): ?>
            <div class="px-6 py-3 bg-rose-50 border-b border-rose-100 text-rose-700 font-sans text-xs">
                <?php echo htmlspecialchars($drawerError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo $isEdit ? 'controller/user-update.php' : 'controller/user-new.php'; ?>" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-4" id="user-drawer-form">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?php echo (int) $editUser->id; ?>">
            <?php endif; ?>

            <div class="space-y-1.5">
                <label class="font-sans text-xs font-bold text-slate-600 block">Avatar</label>
                <?php if ($isEdit): ?>
                    <img src="uploads/users/<?php echo htmlspecialchars($editUser->image ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="" class="mx-auto mb-3 block h-20 w-20 rounded-full object-cover">
                <?php endif; ?>
                <div class="dropzone" data-dropzone-target="#file"></div>
                <input type="file" id="file" name="file" hidden>
            </div>

            <div class="space-y-1.5">
                <label for="useremail" class="font-sans text-xs font-bold text-slate-600 block">Email <span class="text-rose-500">*</span></label>
                <input
                    type="email"
                    name="useremail"
                    id="useremail"
                    value="<?php echo $isEdit ? htmlspecialchars($editUser->useremail, ENT_QUOTES, 'UTF-8') : ''; ?>"
                    placeholder="ej. usuario@dominio.com"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    <?php echo $isNew ? 'required' : ''; ?>
                >
            </div>

            <div class="space-y-1.5">
                <label for="username" class="font-sans text-xs font-bold text-slate-600 block">Usuario <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    value="<?php echo $isEdit ? htmlspecialchars($editUser->username, ENT_QUOTES, 'UTF-8') : ''; ?>"
                    placeholder="Nombre de inicio de sesión"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    <?php echo $isNew ? 'required' : ''; ?>
                >
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="name" class="font-sans text-xs font-bold text-slate-600 block">Nombre <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="<?php echo $isEdit ? htmlspecialchars($editUser->name ?? '', ENT_QUOTES, 'UTF-8') : ''; ?>"
                        placeholder="Nombre"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                        <?php echo $isNew ? 'required' : ''; ?>
                    >
                </div>
                <div class="space-y-1.5">
                    <label for="lastname" class="font-sans text-xs font-bold text-slate-600 block">Apellido <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        name="lastname"
                        id="lastname"
                        value="<?php echo $isEdit ? htmlspecialchars($editUser->lastname ?? '', ENT_QUOTES, 'UTF-8') : ''; ?>"
                        placeholder="Apellido"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                        <?php echo $isNew ? 'required' : ''; ?>
                    >
                </div>
            </div>

            <?php if ($isNew): ?>
                <div class="space-y-1.5">
                    <label for="password" class="font-sans text-xs font-bold text-slate-600 block">Password <span class="text-rose-500">*</span></label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Contraseña inicial"
                        class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                        required
                    >
                </div>
            <?php endif; ?>

            <div class="space-y-1.5">
                <label for="category" class="font-sans text-xs font-bold text-slate-600 block">Categoría <span class="text-rose-500">*</span></label>
                <select
                    name="category"
                    id="category"
                    class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-500 transition-all font-sans"
                    required
                >
                    <option value="">Seleccione una categoría...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option
                            value="<?php echo (int) $cat['id_category']; ?>"
                            <?php echo ($isEdit && (int) $cat['id_category'] === (int) $editUser->category) ? 'selected' : ''; ?>
                        >
                            <?php echo htmlspecialchars($cat['name_category'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center space-x-3">
                <a
                    href="<?php echo $closeDrawerUrl; ?>"
                    class="flex-1 py-2.5 border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors rounded-xl text-xs font-semibold font-sans text-center"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    name="<?php echo $isEdit ? 'update' : 'crear'; ?>"
                    class="flex-1 py-2.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded-xl text-xs font-semibold font-sans transition-all shadow-lg shadow-indigo-500/10"
                >
                    <?php echo $isEdit ? 'Guardar Cambios' : 'Crear Usuario'; ?>
                </button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>
<script src="assets/js/components/native-table.js"></script>

<?php if ($drawerMode !== null): ?>
    <script>
        // Animación del drawer (calco de dash-services.php:635-654): translateX + Escape cierra
        (function () {
            var drawer = document.querySelector('.app-drawer');
            if (!drawer) return;
            drawer.style.transform = 'translateX(100%)';
            requestAnimationFrame(function () {
                drawer.style.transition = 'transform 300ms cubic-bezier(0.22, 1, 0.36, 1)';
                drawer.style.transform = 'translateX(0)';
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    window.location = '<?php echo $closeDrawerUrl; ?>';
                }
            });
        })();
    </script>
<?php endif; ?>

</body>
</html>
