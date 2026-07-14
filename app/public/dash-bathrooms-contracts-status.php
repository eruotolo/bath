<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>
    <title>Baños & Contratos | Blanco Servicios - Admin & Dashboard</title>
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

                <?php
                    $query_contratos_activos = "SELECT BT.codigo_Bath, CT.fechaInicio_Contrato, CT.estado_Contrato,
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
                    <ul class="flex gap-2 border-b border-slate-200" role="tablist">
                        <li>
                            <button type="button" class="flex items-center gap-2 border-b-2 border-primary-600 px-4 py-3 font-sans text-sm font-semibold text-primary-600" data-bs-toggle="tab" data-bs-target="#tab-contratos-activos">
                                Todos los contratos activos
                                <span class="rounded-full bg-primary-100 px-2 py-0.5 font-mono text-[10px] font-bold text-primary-700"><?php echo (int) $total_contratos_activos; ?></span>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="flex items-center gap-2 border-b-2 border-transparent px-4 py-3 font-sans text-sm font-semibold text-slate-500 transition-colors hover:text-slate-700" data-bs-toggle="tab" data-bs-target="#tab-banos-disponibles">
                                Todos los baños disponibles
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 font-mono text-[10px] font-bold text-slate-700"><?php echo (int) $total_banos_disponibles; ?></span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">
                        <div class="tab-pane active" id="tab-contratos-activos" role="tabpanel">
                            <p class="mb-3 text-sm text-slate-500">Cantidad de contratos activos: <strong class="font-bold text-slate-700"><?php echo (int) $total_contratos_activos; ?></strong></p>
                            <div class="table-card mb-4 overflow-hidden">
                                <table id="datatable-contratos-activos" class="table align-middle datatable dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th scope="col">Código de Baño</th>
                                            <th scope="col">Fecha de Inicio de Contrato</th>
                                            <th scope="col">Estado de Contrato</th>
                                            <th scope="col">Asignado a Obra</th>
                                            <th scope="col">Nombre de la Obra</th>
                                            <th scope="col">Nombre del Cliente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_array($result_contratos_activos)): ?>
                                            <tr>
                                                <td class="font-mono text-xs font-semibold text-slate-700"><?php echo htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($row['fechaInicio_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="badge-status is-success">Activo</span></td>
                                                <td><span class="badge-status is-success">Asignado</span></td>
                                                <td><?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane" id="tab-banos-disponibles" role="tabpanel">
                            <p class="mb-3 text-sm text-slate-500">Cantidad de baños disponibles: <strong class="font-bold text-slate-700"><?php echo (int) $total_banos_disponibles; ?></strong></p>
                            <div class="table-card mb-4 overflow-hidden">
                                <table id="datatable-banos-disponibles" class="table align-middle datatable dt-responsive nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th scope="col">Código del Baño</th>
                                            <th scope="col">Fecha de Compra</th>
                                            <th scope="col">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_array($result_banos_disponibles)): ?>
                                            <tr>
                                                <td class="font-mono text-xs font-semibold text-slate-700"><?php echo htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($row['fechaCompra_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="badge-status is-info">Disponible</span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>

<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script src="assets/js/app.js"></script>
<script>
    $(document).ready(function () {
        var dtLanguage = {
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ entradas',
            info: 'Mostrando _PAGE_ de _PAGES_ páginas',
            infoEmpty: 'Mostrando 0 a 0 de 0 elementos',
            infoFiltered: '(filtrado de _MAX_ elementos en total)',
            emptyTable: 'No hay datos disponibles en la tabla',
            loadingRecords: 'Cargando...',
            zeroRecords: 'No se encontraron registros coincidentes',
            paginate: {
                first: 'Primero',
                previous: 'Anterior',
                next: 'Siguiente',
                last: 'Último',
            },
        };

        $('#datatable-contratos-activos').DataTable({
            lengthMenu: [[50, 100, -1], [50, 100, 'All']],
            order: [[0, 'asc']],
            language: dtLanguage,
        });
        $('#datatable-banos-disponibles').DataTable({
            lengthMenu: [[50, 100, -1], [50, 100, 'All']],
            order: [[0, 'asc']],
            language: dtLanguage,
        });
    });
</script>

</body>
</html>
