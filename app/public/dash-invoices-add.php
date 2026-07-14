<?php include 'layouts/session.php'; ?>
<?php include 'layouts/head-main.php'; ?>
<?php global $link; include 'layouts/config.php'; ?>

<head>
    <title>Nueva Factura | Blanco Servicios - Admin & Dashboard Template</title>
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
                    <h1>Nueva Factura</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-invoices-list.php">Facturas</a></li>
                        <li class="active">Nueva Factura</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Registro de Nueva Factura</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>
                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form action="controller/invoice-new.php" method="POST" enctype="multipart/form-data" class="needs-validation mt-4 pt-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="numero_Factura" class="dt-label">Número de Factura</label>
                                    <input type="text" class="dt-input" id="numero_Factura" name="numero_Factura" placeholder="Ingrese el Número de Factura" required>
                                </div>

                                <div class="mb-4">
                                    <label for="fecha_Factura" class="dt-label">Fecha de Factura</label>
                                    <input type="date" class="dt-input" id="fecha_Factura" name="fecha_Factura" placeholder="Ingrese la Fecha de Factura" required>
                                </div>

                                <div class="mb-4">
                                    <label for="id_Cliente" class="dt-label">Cliente</label>
                                    <select name="id_Cliente" id="id_Cliente" class="dt-select" data-enhanced-select data-search-placeholder="Buscar cliente...">
                                        <option value="">Seleccionar el Cliente</option>
                                        <?php
                                        $sql = "SELECT * FROM clientes";
                                        $result = mysqli_query($link, $sql);
                                        $clientes = mysqli_fetch_all($result, MYSQLI_ASSOC);
                                        foreach ($clientes as $cliente): ?>
                                            <option value="<?php echo (int) $cliente['id_Cliente']; ?>"><?php echo htmlspecialchars($cliente['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?></option>
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
                                    <input type="number" class="dt-input" id="valor_Factura" name="valor_Factura" placeholder="Ingrese el Monto de Factura" required>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button class="dt-btn-add" type="submit" name="crear">Crear</button>
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
    $(document).ready(function () {
        SelectEnhanced.cascade({
            parent: 'id_Cliente',
            child: 'id_Contrato',
            endpoint: 'controller/obtener_contratos.php',
            paramName: 'idCliente',
        });
    });
</script>

</body>
</html>
