<?php
$current_script = basename($_SERVER['PHP_SELF']);

// Título dinámico por vista — mismo mapeo que $nav_sections del sidebar
$page_titles = [
    'index.php' => 'Tablero Analítico',
    'dash-customers.php' => 'Directorio de Clientes',
    'dash-customers-item.php' => 'Directorio de Clientes',
    'dash-bathrooms.php' => 'Inventario de Baños Químicos',
    'dash-bathrooms-contracts.php' => 'Inventario de Baños Químicos',
    'dash-bathrooms-contracts-status.php' => 'Inventario de Baños Químicos',
    'dash-contracts.php' => 'Gestión de Obras & Contratos',
    'dash-services.php' => 'Servicios en Terreno & Ruta',
    'dash-invoices-list.php' => 'Control de Facturación',
    'dash-certificates.php' => 'Certificados de Disposición m³',
    'dash-users-list.php' => 'Personal de Operaciones',
    'dash-users-profile.php' => 'Personal de Operaciones',
];

$page_title = isset($page_titles[$current_script]) ? $page_titles[$current_script] : 'Blanco Servicios';

// $_SESSION['category'] es int FK a tabla category (auth-login.php)
// 3 roles: 1=Administrador (nivel 2), 2=Usuario (nivel 1), 3=SuperAdministrador (nivel 3).
$category_names = [1 => 'Administrador', 2 => 'Usuario', 3 => 'SuperAdministrador'];
$user_category = $category_names[$_SESSION['category'] ?? 0] ?? '';
?>
<!--
    Header branding v1.1 - migrado a Tailwind 4 (plan-migracion-bootstrap-tailwind, Fase 2).
    Cambios:
      - Clases .hdr-* (header.css) reemplazadas por utilities Tailwind.
      - IDs y data-bs-toggle conservados (Bootstrap JS opera el dropdown hasta Fase 8).
-->

<header class="hdr-header sticky top-0 z-30 flex h-20 items-center justify-between border-b border-slate-100 bg-white px-6 font-sans shadow-sm shadow-slate-100/50 lg:ml-72">
    <div class="flex min-w-0 items-center gap-4">
        <button type="button" id="vertical-menu-btn" aria-label="Abrir menú" class="rounded-xl border-0 bg-transparent p-2 text-slate-500 transition-colors hover:bg-slate-50 hover:text-slate-900 lg:hidden">
            <i data-lucide="menu" class="!h-5 !w-5"></i>
        </button>
        <div class="min-w-0">
            <h1 class="m-0 text-xl font-bold tracking-tight text-slate-900"><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h1>
            <div class="hidden items-center gap-2 sm:flex">
                <span class="hdr-status-dot h-2 w-2 rounded-full bg-primary-500"></span>
                <span class="font-mono text-[10px] font-semibold uppercase tracking-wider text-slate-500">Operaciones Chiloé • Servidor Activo</span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <div class="dropdown hdr-user pl-2">
            <button type="button" class="hdr-user-trigger flex items-center gap-3 border-0 bg-transparent p-0" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="hidden text-right sm:block">
                    <span class="block text-xs font-bold leading-tight text-slate-800"><?php echo htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['lastname'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php if ($user_category): ?>
                        <span class="font-mono mt-0.5 block text-[10px] font-bold uppercase tracking-wider leading-tight text-primary-600"><?php echo htmlspecialchars($user_category, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </span>
                <img class="h-10 w-10 rounded-xl object-cover shadow-md shadow-slate-100 ring-2 ring-primary-500/10" src="uploads/users/<?php echo htmlspecialchars($_SESSION['image'] ?? 'avatar-1.jpg', ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de perfil">
            </button>
            <ul class="dropdown-menu dropdown-menu-end hdr-user-menu m-0 list-none overflow-hidden rounded-2xl border border-slate-100 bg-white p-2 shadow-xl shadow-slate-200/50">
                <li><a class="dropdown-item flex items-center gap-2 rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="dash-users-profile.php"><i data-lucide="user" class="!h-4 !w-4"></i> Perfil</a></li>
                <li><a class="dropdown-item flex items-center gap-2 rounded-lg px-3 py-2 font-sans text-[13px] text-slate-700 hover:bg-slate-50 hover:text-slate-900" href="auth-lock-screen.php"><i data-lucide="lock" class="!h-4 !w-4"></i> Bloquear</a></li>
                <li><hr class="dropdown-divider m-1 border-slate-100"></li>
                <li><a class="dropdown-item flex items-center gap-2 rounded-lg px-3 py-2 font-sans text-[13px] text-rose-500 hover:bg-rose-50 hover:text-rose-500" href="logout.php"><i data-lucide="log-out" class="!h-4 !w-4"></i> Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</header>
