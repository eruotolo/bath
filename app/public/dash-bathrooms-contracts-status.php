<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include('layouts/native-table.php'); ?>

<head>
    <title>Baños & Contratos | Blanco Servicios - Admin & Dashboard</title>
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
                    $query_contratos_activos = "SELECT BT.codigo_Bath, CT.fechaInicio_Contrato,
                                                        CT.obra_Contrato, CL.nombre_Cliente
                                                 FROM contratos CT
                                                     JOIN contrato_bathroom CB ON CB.id_Contrato = CT.id_Contrato
                                                     JOIN bathrooms BT ON BT.id_Bath = CB.id_Bath
                                                     JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente
                                                 WHERE CT.estado_Contrato = 2
                                                   AND CT.fechaInicio_Contrato <= CURDATE()
                                                 ORDER BY BT.codigo_Bath ASC";
                    $result_contratos_activos = mysqli_query($link, $query_contratos_activos);
                    $total_contratos_activos = mysqli_num_rows($result_contratos_activos);

                    $query_banos_disponibles = "SELECT BT.codigo_Bath, BT.fechaCompra_Bath
                                                 FROM bathrooms BT
                                                 WHERE BT.estado_Bath = 1
                                                   AND NOT EXISTS (
                                                       SELECT 1 FROM contrato_bathroom CB
                                                           JOIN contratos CT ON CT.id_Contrato = CB.id_Contrato
                                                       WHERE CB.id_Bath = BT.id_Bath
                                                         AND CT.estado_Contrato = 2
                                                         AND CT.fechaInicio_Contrato <= CURDATE()
                                                   )
                                                 ORDER BY BT.codigo_Bath ASC";
                    $result_banos_disponibles = mysqli_query($link, $query_banos_disponibles);
                    $total_banos_disponibles = mysqli_num_rows($result_banos_disponibles);
                ?>

                <div class="space-y-4">
                    <ul class="flex items-center gap-2" role="tablist">
                        <li>
                            <button type="button" class="active group flex items-center gap-1.5 rounded-xl bg-slate-100 px-4 py-2 font-sans text-xs font-semibold text-slate-500 transition-all duration-200 hover:text-slate-800 [&.active]:bg-slate-900 [&.active]:text-white" data-bs-toggle="tab" data-bs-target="#tab-contratos-activos">
                                Baños y Contratos Activos
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700 group-[.active]:bg-white/15 group-[.active]:text-white"><?php echo (int) $total_contratos_activos; ?></span>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="group flex items-center gap-1.5 rounded-xl bg-slate-100 px-4 py-2 font-sans text-xs font-semibold text-slate-500 transition-all duration-200 hover:text-slate-800 [&.active]:bg-slate-900 [&.active]:text-white" data-bs-toggle="tab" data-bs-target="#tab-banos-disponibles">
                                Todos los Baños Disponibles
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700 group-[.active]:bg-white/15 group-[.active]:text-white"><?php echo (int) $total_banos_disponibles; ?></span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">
                        <div class="tab-pane active show" id="tab-contratos-activos" role="tabpanel">
                            <?php
                                table_native_open([
                                    'table_id' => 'tabla-contratos-activos',
                                    'search_placeholder' => 'Buscar por código, obra, cliente...',
                                    'item_label' => 'Contratos',
                                    'per_page' => 9,
                                    'actions_html' => table_native_export_buttons(
                                        'controller/bathroom-contract-status-export.php?format=csv&tab=contratos',
                                        'controller/bathroom-contract-status-export.php?format=pdf&tab=contratos',
                                        'contratos-activos'
                                    ),
                                    'columns' => [
                                        ['label' => 'Código de Baño'],
                                        ['label' => 'Fecha de Inicio de Contrato'],
                                        ['label' => 'Nombre de la Obra'],
                                        ['label' => 'Nombre del Cliente'],
                                    ],
                                ]);
                            ?>
                            <?php while ($row = mysqli_fetch_array($result_contratos_activos)): ?>
                                <?php
                                    $codigo = htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8');
                                    $fecha = htmlspecialchars(date('d-m-Y', strtotime($row['fechaInicio_Contrato'])), ENT_QUOTES, 'UTF-8');
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
                            <?php if ($total_contratos_activos === 0): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                        No hay contratos activos en este momento.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php table_native_close(); ?>
                        </div>

                        <div class="tab-pane" id="tab-banos-disponibles" role="tabpanel">
                            <?php
                                table_native_open([
                                    'table_id' => 'tabla-banos-disponibles',
                                    'search_placeholder' => 'Buscar por código...',
                                    'item_label' => 'Baños',
                                    'per_page' => 9,
                                    'actions_html' => table_native_export_buttons(
                                        'controller/bathroom-contract-status-export.php?format=csv&tab=banos',
                                        'controller/bathroom-contract-status-export.php?format=pdf&tab=banos',
                                        'banos-disponibles'
                                    ),
                                    'columns' => [
                                        ['label' => 'Código del Baño'],
                                        ['label' => 'Fecha de Compra'],
                                        ['label' => 'Estado'],
                                    ],
                                ]);
                            ?>
                            <?php while ($row = mysqli_fetch_array($result_banos_disponibles)): ?>
                                <?php
                                    $codigo = htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8');
                                    $fecha = htmlspecialchars(date('d-m-Y', strtotime($row['fechaCompra_Bath'])), ENT_QUOTES, 'UTF-8');
                                    $searchable = htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8');
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
                                        <span class="badge-status is-info">Disponible</span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($total_banos_disponibles === 0): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-10 text-center text-slate-400 font-sans text-sm">
                                        No hay baños disponibles en este momento.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php table_native_close(); ?>
                        </div>
                    </div>
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
