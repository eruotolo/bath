<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>

<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Invoice\FindInvoice;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

global $link;
include 'layouts/config.php';

if (!isset($_GET['id_Factura']) || !is_numeric($_GET['id_Factura'])) {
    header("Location: dash-invoices-list.php");
    exit();
}

$id_Factura = (int) $_GET['id_Factura'];

$factura = (new FindInvoice(new MysqliInvoiceRepository($link)))->handle($id_Factura);

if ($factura === null) {
    header("Location: dash-invoices-list.php");
    exit();
}
?>

<head>
    <title>Editar Factura | Blanco Servicios - Admin & Dashboard Template</title>
    <?php include 'layouts/head.php'; ?>
    <?php include 'layouts/head-style.php'; ?>
</head>

<?php include 'layouts/body.php'; ?>

<div id="layout-wrapper">
    <?php include 'layouts/menu.php'; ?>

    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <div class="dt-page-title">
                    <h1>Editar Factura</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-invoices-list.php">Facturas</a></li>
                        <li class="active">Editar Factura</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Edición de Factura</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>
                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Modificar datos en los campos
                        </h5>

                        <form action="controller/invoice-update.php" method="POST" class="needs-validation mt-4 pt-2">
                            <input type="hidden" name="id_Factura" value="<?php echo (int) $factura->id; ?>">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="numero_Factura" class="dt-label">Número de Factura</label>
                                    <input type="text" class="dt-input" id="numero_Factura" name="numero_Factura" value="<?php echo htmlspecialchars($factura->number, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label for="fecha_Factura" class="dt-label">Fecha de Factura</label>
                                    <input type="date" class="dt-input" id="fecha_Factura" name="fecha_Factura" value="<?php echo htmlspecialchars($factura->date, ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>

                                <div class="mb-4">
                                    <label for="id_Cliente" class="dt-label">Cliente</label>
                                    <select name="id_Cliente" id="id_Cliente" class="dt-select" data-enhanced-select data-search-placeholder="Buscar cliente...">
                                        <option value="">Seleccionar el Cliente</option>
                                        <?php
                                        $sqlClientes = "SELECT * FROM clientes ORDER BY nombre_Cliente ASC";
                                        $resultClientes = mysqli_query($link, $sqlClientes);
                                        $clientes = mysqli_fetch_all($resultClientes, MYSQLI_ASSOC);
                                        foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo (int) $cliente['id_Cliente']; ?>" <?php echo ((int) $cliente['id_Cliente'] === $factura->customerId) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cliente['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="id_Contrato" class="dt-label">Contrato</label>
                                    <select name="id_Contrato" id="id_Contrato" class="dt-select" data-enhanced-select data-search-placeholder="Buscar contrato...">
                                        <!-- Opciones se cargarán dinámicamente con JavaScript -->
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="valor_Factura" class="dt-label">Monto de Factura</label>
                                    <input type="number" class="dt-input" id="valor_Factura" name="valor_Factura" value="<?php echo (int) $factura->value; ?>" required>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button class="dt-btn-add" type="submit" name="editar">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'layouts/vendor-scripts.php'; ?>
<script src="assets/js/app.js"></script>
<script>
    var idContratoActual = <?php echo (int) $factura->contractId; ?>;

    function cargarContratos(idCliente, seleccionar) {
        $.ajax({
            url: 'controller/obtener_contratos.php',
            type: 'POST',
            data: {idCliente: idCliente},
            success: function (response) {
                SelectEnhanced.setOptionsFromHtml('id_Contrato', response, seleccionar);
            }
        });
    }

    $(document).ready(function () {
        cargarContratos($('#id_Cliente').val(), idContratoActual);

        $('#id_Cliente').change(function () {
            cargarContratos($(this).val(), null);
        });
    });
</script>

</body>
</html>
