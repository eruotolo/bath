<?php global $link;
include 'layouts/session.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Nuevo Certificado | Blanco Servicios - Admin & Dashboard</title>
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
                    <h1>Agregar Nuevo Certificado</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-certificates.php">Certificado</a></li>
                        <li class="active">Agregar Certificado</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Registro de Nuevo Certificado</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form class="needs-validation mt-4 pt-2" method="post" action="controller/certificate-new.php">
                            <input type="date" id="fechahoy_Certificado" name="fechahoy_Certificado" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" hidden>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="id_Cliente" class="dt-label">Cliente</label>
                                    <select name="id_Cliente" id="id_Cliente" class="dt-select" data-enhanced-select data-search-placeholder="Buscar cliente...">
                                        <option value="" selected>Seleccionar el cliente</option>
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
                                    <label for="mts_Certificado" class="dt-label">Metros Cúbicos</label>
                                    <input class="dt-input" type="number" id="mts_Certificado" name="mts_Certificado" placeholder="Ingrese los metros cúbicos" required>
                                </div>

                                <div class="mb-4">
                                    <label for="fecha_Servicio" class="dt-label">Fecha Servicio</label>
                                    <input class="dt-input" type="text" id="fecha_Servicio" name="fecha_Servicio" placeholder="Ingrese las Fecha del Servicio" required>
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
