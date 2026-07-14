<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include('layouts/native-table.php'); ?>

<?php
    $sortColumns = [
        'codigo_Bath' => 'BT.codigo_Bath',
        'fechaInicio_Contrato' => 'CT.fechaInicio_Contrato',
    ];
    $allowedSortDir = ['ASC', 'DESC'];
    $sortBy = array_key_exists($_GET['sort'] ?? '', $sortColumns) ? $_GET['sort'] : null;
    $sortDir = in_array($_GET['dir'] ?? '', $allowedSortDir, true) ? $_GET['dir'] : 'ASC';
    $orderBy = $sortBy !== null ? $sortColumns[$sortBy] . ' ' . $sortDir : 'fechaCompra_Bath DESC';

    function sortUrl(string $column, ?string $currentSort, string $currentDir): string {
        $newDir = ($column === $currentSort && $currentDir === 'ASC') ? 'DESC' : 'ASC';
        return '?sort=' . $column . '&dir=' . $newDir;
    }

    function sort_header_html(string $label, string $column, ?string $currentSort, string $currentDir): string {
        $url = htmlspecialchars(sortUrl($column, $currentSort, $currentDir), ENT_QUOTES, 'UTF-8');
        $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $icon = $currentSort === $column
            ? '<i data-lucide="' . ($currentDir === 'ASC' ? 'chevron-up' : 'chevron-down') . '" class="w-3 h-3 text-indigo-600"></i>'
            : '<i data-lucide="chevrons-up-down" class="w-3 h-3 text-slate-300"></i>';
        return <<<HTML
            <a href="{$url}" class="inline-flex items-center gap-1 hover:text-slate-600 transition-colors">
                <span>{$label}</span>
                {$icon}
            </a>
            HTML;
    }
?>

<head>
    <title>Histórico de Baños con Contratos | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid px-10 py-10 bg-slate-50/50">

                <?php
                    $query = "SELECT * FROM bathrooms BT
                             JOIN contrato_bathroom CB ON BT.id_Bath = CB.id_Bath
                             JOIN contratos CT ON CB.id_Contrato = CT.id_Contrato
                            JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
                             WHERE BT.estado_Bath = 1 ORDER BY {$orderBy}";
                    $result_task = mysqli_query($link, $query);
                    $total_banos = mysqli_num_rows($result_task);
                ?>

                <div class="space-y-4">
                    <?php
                        table_native_open([
                            'table_id' => 'tabla-historico-banos',
                            'search_placeholder' => 'Buscar por código, obra, cliente...',
                            'item_label' => 'Baños',
                            'per_page' => 9,
                            'actions_html' => table_native_export_buttons(
                                'controller/bathroom-contract-history-export.php?format=csv',
                                'controller/bathroom-contract-history-export.php?format=pdf',
                                'historico-banos'
                            ),
                            'columns' => [
                                ['label' => sort_header_html('Código', 'codigo_Bath', $sortBy, $sortDir), 'html' => true],
                                ['label' => sort_header_html('Fecha de Inicio de Contrato', 'fechaInicio_Contrato', $sortBy, $sortDir), 'html' => true],
                                ['label' => 'Estado'],
                                ['label' => 'Asignado a Obra'],
                                ['label' => 'Nombre de Obra'],
                                ['label' => 'Cliente'],
                            ],
                        ]);
                    ?>
                    <?php while ($row = mysqli_fetch_array($result_task)): ?>
                        <?php
                            $codigo = htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8');
                            $fecha = htmlspecialchars($row['fechaInicio_Contrato'], ENT_QUOTES, 'UTF-8');
                            $obra = htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8');
                            $cliente = htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8');
                            $searchable = htmlspecialchars($row['codigo_Bath'] . ' ' . $row['obra_Contrato'] . ' ' . $row['nombre_Cliente'], ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr class="hover:bg-slate-50/75 transition-colors" data-search="<?php echo $searchable; ?>">
                            <td class="px-6 py-4.5">
                                <div class="flex items-center text-xs text-slate-700 font-mono font-semibold">
                                    <i data-lucide="bath" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                    <span><?php echo $codigo; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="flex items-center text-xs text-slate-600 font-sans">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                    <span><?php echo $fecha; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4.5">
                                <?php if ($row['estado_Bath'] == 1): ?>
                                    <span class="badge-status is-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge-status is-danger">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4.5">
                                <?php if ($row['asignado_Bath'] == 0): ?>
                                    <span class="badge-status is-info">Disponible</span>
                                <?php else: ?>
                                    <span class="badge-status is-warn">Asignado</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="flex items-center text-xs text-slate-600 font-sans">
                                    <i data-lucide="briefcase" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                    <span><?php echo $obra; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="flex items-center text-xs text-slate-600 font-sans">
                                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400 mr-1.5 shrink-0"></i>
                                    <span><?php echo $cliente; ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($total_banos === 0): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                No hay baños con contrato asignado en este momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php table_native_close(); ?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/js/app.js"></script>
<script src="assets/js/components/native-table.js"></script>

</body>
</html>
