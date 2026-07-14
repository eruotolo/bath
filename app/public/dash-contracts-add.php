<?php global $link;
include 'layouts/session.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Nuevo Contrato | Blanco Servicios - Admin & Dashboard</title>
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
                    <h1>Agregar Nuevo Contrato</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-contracts.php">Contratos</a></li>
                        <li class="active">Agregar Contrato</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Registro de Nuevo Contrato</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form class="needs-validation mt-4 pt-2" method="post" action="controller/contract-new.php">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="id_Cliente" class="dt-label">Cliente</label>
                                    <select name="id_Cliente" id="id_Cliente" class="dt-select" data-enhanced-select data-search-placeholder="Buscar cliente...">
                                        <option value="" selected>Seleccionar un Cliente</option>
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
                                    <label for="obra_Contrato" class="dt-label">Nombre del contrato / OC compra</label>
                                    <input class="dt-input" type="text" id="obra_Contrato" name="obra_Contrato" placeholder="Ingrese el nombre de la obra" required>
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="direccion_Contrato" class="dt-label">Dirección contrato / OC compra</label>
                                    <input class="dt-input" type="text" id="direccion_Contrato" name="direccion_Contrato" placeholder="Ingrese la dirección de la obra" required>
                                </div>

                                <div class="mb-4">
                                    <label for="estado_Contrato" class="dt-label">Estado del contrato / OC compra</label>
                                    <select name="estado_Contrato" id="estado_Contrato" class="dt-select" data-enhanced-select>
                                        <option value="">Seleccione el estado</option>
                                        <option value="2">Activo</option>
                                        <option value="1">Terminado</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="fechaInicio_Contrato" class="dt-label">Fecha de inicio</label>
                                    <input class="dt-input" type="date" id="fechaInicio_Contrato" name="fechaInicio_Contrato" required>
                                </div>

                                <div class="mb-4">
                                    <label for="fechaFin_Contrato" class="dt-label">Fecha de fin</label>
                                    <input class="dt-input" type="date" id="fechaFin_Contrato" name="fechaFin_Contrato" required data-datepicker-min-from="#fechaInicio_Contrato">
                                </div>

                                <div class="mb-4">
                                    <label for="valorMensual_Contrato" class="dt-label">Valor mensual</label>
                                    <input class="dt-input" type="number" id="valorMensual_Contrato" name="valorMensual_Contrato" required>
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="valorTotal_Contrato" class="dt-label">Valor total</label>
                                    <input class="dt-input" type="number" id="valorTotal_Contrato" name="valorTotal_Contrato" required>
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="observacion_Contrato" class="dt-label">Observaciones</label>
                                    <textarea name="observacion_Contrato" id="observacion_Contrato" rows="4" class="dt-input"></textarea>
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
</body>
</html>
