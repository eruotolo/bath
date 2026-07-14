<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Application\Certificate\ListCertificates;
use App\Infrastructure\Persistence\MysqliCertificateRepository;

global $link;
include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php include('layouts/config.php');

$certificates = (new ListCertificates(new MysqliCertificateRepository($link)))->handle();
?>

<head>
    <title>Certificados | Blanco Servicios - Admin & Dashboard</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css"/>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="space-y-4">
                    <div class="table-toolbar">
                        <h5 class="table-toolbar-title">Certificados <span class="count">(<?php echo (int) $certificates['total']; ?>)</span></h5>
                        <div class="table-toolbar-actions">
                            <div class="table-toolbar-search"></div>
                            <a href="dash-certificates-add.php" class="dt-btn-add"><i data-lucide="plus"></i> Agregar Nuevo Certificado</a>
                        </div>
                    </div>

                    <div class="table-card mb-4">
                        <table id="datatable-buttons" class="table align-middle datatable dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th scope="col">Nro. Certificado</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">RUT Cliente</th>
                                    <th scope="col">Obra</th>
                                    <th scope="col">Fecha del Servicio</th>
                                    <th scope="col" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($certificates['items'] as $row): ?>
                                    <tr>
                                        <td class="font-mono text-sm font-semibold text-slate-700">#<?php echo htmlspecialchars($row['certificado'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="font-sans text-sm text-slate-700"><?php echo htmlspecialchars($row['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="font-mono text-xs text-slate-500"><?php echo htmlspecialchars($row['rut_Cliente'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($row['fecha_Servicio'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="dash-certificates-item.php?id_Certificado=<?php echo (int) $row['id_Certificado']; ?>&id_Contrato=<?php echo (int) $row['id_Contrato']; ?>" class="dt-cell-action" title="Ver">
                                                    <i data-lucide="eye"></i>
                                                </a>
                                                <div class="dropdown">
                                                    <button class="dt-cell-action dropdown-toggle dropdown-toggle-split" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                                        <i data-lucide="more-horizontal"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="controller/certificate-pdf.php?id_Certificado=<?php echo (int) $row['id_Certificado']; ?>&id_Contrato=<?php echo (int) $row['id_Contrato']; ?>" data-glightbox-preview data-type="external" data-width="900px" data-height="90vh"><i data-lucide="printer" class="!mr-2 !h-[14px] !w-[14px]"></i>Imprimir</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="controller/certificate-remove.php?id_Certificado=<?php echo (int) $row['id_Certificado']; ?>" data-confirm-delete data-confirm-title="¿Eliminar este certificado?" data-confirm-text="Esta acción no se puede deshacer.">
                                                                <i data-lucide="trash-2" class="!mr-2 !h-[14px] !w-[14px]"></i>Eliminar
                                                            </a>
                                                        </li>
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
            order: [],
            buttons: [
                { extend: 'collection', text: 'Exportar', buttons: ['copy', 'excel', 'pdf'] },
                { extend: 'colvis', text: 'Visibilidad de columnas' },
            ],
        });
    });
</script>

</body>
</html>
