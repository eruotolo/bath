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


    <!-- DataTables -->
    <link href="assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css"/>

    <!-- Responsive datatable examples -->
    <link href="assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"
          type="text/css"/>

</head>

<?php include 'layouts/body.php'; ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include 'layouts/menu.php'; ?>

        <!-- Start right Content here -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Listado de certificados</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-center">
                        <div class="col-6">
                            <div class="mb-3">
                                <h5 class="card-title">Certificados <span
                                        class="text-muted fw-normal ms-2">(<?php echo $certificates['total'] ?>)</span>
                                </h5>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
                                <div>
                                    <a href="dash-certificates-add.php" class="btn btn-light"><i class="bx bx-plus me-1"></i> Agregar Nuevo Certificado</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="table-responsive mb-4">
                                <table id="datatable-buttons" class="table align-middle datatable dt-responsive table-check nowrap w-100" style="border-collapse: collapse; border-spacing: 0 8px; width: 100%;">

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
                                            <td>#<?php echo htmlspecialchars($row['certificado']) ?></td>
                                            <td><?php echo htmlspecialchars($row['nombre_Cliente']) ?></td>
                                            <td><?php echo htmlspecialchars($row['rut_Cliente']) ?></td>
                                            <td><?php echo htmlspecialchars($row['obra_Contrato']) ?></td>
                                            <td><?php echo htmlspecialchars($row['fecha_Servicio']) ?></td>
                                            <td class="text-center">
                                                <a href="dash-certificates-item.php?id_Certificado=<?php echo (int) $row['id_Certificado'] ?>&id_Contrato=<?php echo (int) $row['id_Contrato'] ?>" class="btn btn-outline-secondary btn-sm" title="Ver">
                                                    <i class="fas fas fa-eye"></i>
                                                </a>
                                                <a href="controller/certificate-pdf.php?id_Certificado=<?php echo (int) $row['id_Certificado'] ?>&id_Contrato=<?php echo (int) $row['id_Contrato'] ?>" class="btn btn-outline-secondary btn-sm" title="Imprimir" data-glightbox-preview data-type="external" data-width="900px" data-height="90vh">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <a href="controller/certificate-remove.php?id_Certificado=<?php echo (int) $row['id_Certificado'] ?>" class="btn btn-outline-secondary btn-sm" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
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


    </div>
<!-- END layout-wrapper -->


<!-- Right Sidebar -->
<?php include 'layouts/right-sidebar.php'; ?>
<!-- /Right-bar -->

<!-- JAVASCRIPT -->

<?php include 'layouts/vendor-scripts.php'; ?>

<!-- Required datatable js -->
<script src="assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- Buttons examples -->
<script src="assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
<script src="assets/libs/jszip/jszip.min.js"></script>
<script src="assets/libs/pdfmake/build/pdfmake.min.js"></script>
<script src="assets/libs/pdfmake/build/vfs_fonts.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

<!-- Responsive examples -->
<script src="assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script src="assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<script>
    $(document).ready(function () {
        var table = $('#datatable-buttons').DataTable({
            lengthMenu: [
                [50, 100, -1],
                [50, 100, 'All'],
            ],
            order: [], // Preservar el orden por created_at DESC que ya viene del SQL
            buttons: [
                {
                    extend: 'collection',
                    text: 'Exportar',
                    buttons: ['copy', 'excel', 'pdf'],
                },
                {
                    extend: 'colvis',
                    text: 'Visibilidad de columnas',
                },
            ],
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ entradas',
                info: 'Mostrando _PAGE_ de _PAGES_ páginas',
                infoEmpty: 'Mostrando 0 a 0 de 0 elementos',
                infoFiltered: '(filtrado de _MAX_ elementos en total)',
                emptyTable: 'No hay datos disponibles en la tabla',
                loadingRecords: 'Cargando...',
                zeroRecords: 'No se encontraron registros coincidentes',
                aria: {
                    sortAscending: ': permite ordenar la columna en orden ascendente',
                    sortDescending: ': habilita ordenar la columna en orden descendente',
                },
                paginate: {
                    first: 'Primero',
                    previous: 'Anterior',
                    next: 'Siguiente',
                    last: 'Último',
                },
            },
        });

        table.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');

        $('.dataTables_length select').addClass('form-select form-select-sm');
    });
</script>

<script src="assets/js/app.js"></script>

</body>

</html>
