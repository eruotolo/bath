<?php
$current_script = basename($_SERVER['PHP_SELF']);

function is_nav_item_active(array $item, string $current_script): bool {
    if (!in_array($current_script, $item['match'], true)) {
        return false;
    }
    if (isset($item['match_get'])) {
        $key = $item['match_get']['key'];
        $value = $item['match_get']['value'];
        return isset($_GET[$key]) && (string) $_GET[$key] === $value;
    }
    if (isset($item['exclude_get'])) {
        $key = $item['exclude_get']['key'];
        $values = $item['exclude_get']['values'];
        $actual = isset($_GET[$key]) ? (string) $_GET[$key] : null;
        return !in_array($actual, $values, true);
    }
    return true;
}

$nav_sections = [
    'General' => [
        [
            'label' => 'Tablero Principal',
            'icon' => 'layout-dashboard',
            'url' => 'index.php',
            'match' => ['index.php'],
        ],
    ],
    'Operaciones' => [
        [
            'label' => 'Clientes',
            'icon' => 'users',
            'url' => 'dash-customers.php',
            'match' => ['dash-customers.php', 'dash-customers-add.php', 'dash-customers-item.php'],
        ],
        [
            'label' => 'Baños Químicos',
            'icon' => 'bath',
            'url' => 'dash-bathrooms.php',
            'match' => ['dash-bathrooms.php', 'dash-bathrooms-add.php', 'dash-bathrooms-edit.php'],
        ],
        [
            'label' => 'Baños & Contratos',
            'icon' => 'link',
            'url' => 'dash-bathrooms-contracts-status.php',
            'match' => ['dash-bathrooms-contracts-status.php'],
        ],
        [
            'label' => 'Histórico Baños',
            'icon' => 'history',
            'url' => 'dash-bathrooms-contracts.php',
            'match' => ['dash-bathrooms-contracts.php'],
        ],
    ],
    'Servicios' => [
        [
            'label' => 'Obras & Contratos',
            'icon' => 'file-text',
            'url' => 'dash-contracts.php',
            'match' => ['dash-contracts.php', 'dash-contracts-add.php', 'dash-contracts-edit.php', 'dash-contracts-item.php'],
        ],
        [
            'label' => 'Servicios',
            'icon' => 'clipboard-check',
            'url' => 'dash-services.php',
            'match' => ['dash-services.php', 'dash-services-add.php', 'dash-services-edit.php', 'dash-services-bath.php', 'dash-services-print.php'],
        ],
    ],
    'Finanzas' => [
        [
            'label' => 'Facturas',
            'icon' => 'receipt',
            'url' => 'dash-invoices-list.php',
            'match' => ['dash-invoices-list.php', 'dash-invoices-add.php', 'dash-invoices-edit.php', 'dash-invoices-upload.php', 'dash-invoices-upload-preview.php', 'dash-invoices-upload-result.php', 'dash-invoices-detail.php'],
        ],
        [
            'label' => 'Certificados',
            'icon' => 'file-check-2',
            'url' => 'dash-certificates.php',
            'match' => ['dash-certificates.php', 'dash-certificates-add.php', 'dash-certificates-item.php'],
        ],
    ],
    'Administración' => [
        [
            'label' => 'Personal & Roles',
            'icon' => 'users-2',
            'url' => 'dash-users-list.php',
            'match' => ['dash-users-list.php', 'dash-users-add.php', 'dash-users-edit.php', 'dash-users-profile.php'],
        ],
    ],
];
?>
<!--
    Sidebar branding v1.1 - migrado a Tailwind 4 (plan-migracion-bootstrap-tailwind, Fase 2).
    Cambios respecto a la version vanilla:
      - Clases .sb-* (sidebar.css) reemplazadas por utilities Tailwind en el HTML.
      - IDs #sb-sidebar, #sb-backdrop, #sb-close-btn conservados (sidebar.js los usa).
      - Clases is-open/is-active conservadas (sidebar.js las togglea).
      - Keyframes sb-pulse y regla .main-content margen viven en assets/css/tw/layout.css.
-->

<div id="sb-backdrop" class="fixed inset-0 z-30 bg-slate-900/40 opacity-0 invisible backdrop-blur-sm transition-opacity duration-300 lg:hidden"></div>

<aside id="sb-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full transform flex-col border-r border-slate-200 bg-white font-sans text-slate-500 transition-transform duration-300 ease-out lg:translate-x-0 lg:transition-[cubic-bezier(0.4,0,0.2,1)]">
    <!-- Brand -->
    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/50 px-6 py-5">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-600 text-white shadow-lg shadow-primary-600/20">
                <i data-lucide="bath" class="!h-5 !w-5"></i>
            </div>
            <div>
                <span class="block text-lg font-bold leading-tight tracking-tight text-slate-900">Blanco</span>
                <span class="font-mono mt-0.5 block text-[9px] font-bold uppercase tracking-wider text-primary-600">Servicios</span>
            </div>
        </div>
        <button type="button" id="sb-close-btn" aria-label="Cerrar menú" class="rounded-lg border-0 bg-transparent p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-900 lg:hidden">
            <i data-lucide="x" class="!h-5 !w-5"></i>
        </button>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto px-4 py-6">
        <?php
        $first_section = true;
        foreach ($nav_sections as $category => $items):
            $section_classes = $first_section ? '' : 'mt-7';
            $first_section = false;
        ?>
            <div class="<?php echo $section_classes; ?>">
                <h3 class="mb-2 px-3 font-mono text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></h3>
                <ul class="flex flex-col gap-1">
                    <?php foreach ($items as $item):
                        $is_active = is_nav_item_active($item, $current_script);
                        $item_classes = $is_active
                            ? 'bg-primary-50 text-primary-700 shadow-sm'
                            : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900';
                        $icon_classes = $is_active
                            ? 'text-primary-600'
                            : 'text-slate-400 group-hover:text-primary-500';
                    ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" class="sb-nav-item group relative flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 <?php echo $item_classes; ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                                <i data-lucide="<?php echo htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8'); ?>" class="!h-4 !w-4 shrink-0 transition-transform group-hover:scale-110 <?php echo $icon_classes; ?>"></i>
                                <span class="flex-1 truncate pr-4"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if ($is_active): ?>
                                    <span class="absolute right-3 h-1.5 w-1.5 rounded-full bg-primary-600 animate-pulse"></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </nav>

    <!-- Footer (plan-sidebar-rebranding §2 footer, traduccion 1:1 de Sidebar.tsx:138-145) -->
    <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-4 text-center">
        <p class="font-mono m-0 text-[9px] text-slate-400">&copy; 2026 Blanco Servicios.</p>
        <p class="font-mono mt-0.5 text-[8px] font-bold text-primary-500/80">Plataforma Eco-Sostenible v2.0</p>
    </div>
</aside>
