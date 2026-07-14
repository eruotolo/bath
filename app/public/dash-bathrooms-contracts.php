<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php'); ?>

<head>
    <title>Histórico de Baños con Contratos | Blanco Servicios - Admin & Dashboard</title>
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
                $query = "SELECT COUNT(*) AS total FROM bathrooms;";
                $result_task = mysqli_query($link, $query);
                $total_banos = 0;
                while ($row = mysqli_fetch_array($result_task)) {
                    $total_banos = $row['total'];
                }
                ?>

                <div class="space-y-4">
                    <div class="table-toolbar">
                        <h5 class="table-toolbar-title">Cantidad de Baños <span class="count">(<?php echo (int) $total_banos; ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="table-toolbar-search"></div>
                            <a href="dash-bathrooms-add.php" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Nuevo Baño</a>
                        </div>
                    </div>

                    <div class="table-card mb-4">
                        <table id="datatable-buttons" class="table align-middle datatable dt-responsive nowrap w-100" data-dt-state="true">
                            <thead>
                                <tr>
                                    <th scope="col">Código</th>
                                    <th scope="col">Fecha Inicio de Contrato</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col">Asignado a Obra</th>
                                    <th scope="col">Nombre de Obra</th>
                                    <th scope="col">Cliente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM bathrooms BT
                                         JOIN contrato_bathroom CB ON BT.id_Bath = CB.id_Bath
                                         JOIN contratos CT ON CB.id_Contrato = CT.id_Contrato
                                        JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
                                         WHERE BT.estado_Bath = 1 ORDER BY fechaCompra_Bath DESC";
                                $result_task = mysqli_query($link, $query);
                                while ($row = mysqli_fetch_array($result_task)):
                                ?>
                                    <tr>
                                        <td class="font-mono text-xs font-semibold text-slate-700"><?php echo htmlspecialchars($row['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['fechaInicio_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <?php if ($row['estado_Bath'] == 1): ?>
                                                <span class="badge-status is-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge-status is-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['asignado_Bath'] == 0): ?>
                                                <span class="badge-status is-info">Disponible</span>
                                            <?php else: ?>
                                                <span class="badge-status is-warn">Asignado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
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
            order: [[2, "desc"]],
            columnDefs: [{ targets: 2, type: 'date' }],
        });
    });
</script>

</body>
</html>
