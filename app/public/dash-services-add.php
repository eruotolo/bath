<?php global $link;
include 'layouts/session.php'; ?>
<?php include('layouts/config.php'); ?>
<?php include 'layouts/head-main.php'; ?>

<head>
    <title>Nuevo Servicio | Seguimiento | Blanco Servicios - Admin & Dashboard</title>
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
                    <h1>Agregar Nuevo Servicio | Seguimiento</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-services.php">Servicio | Seguimiento</a></li>
                        <li class="active">Agregar Nuevo Servicio</li>
                    </ol>
                </div>

                <div class="dt-card">
                    <div class="dt-card-header">
                        <h4 class="dt-card-title">Formulario de Registro de Nuevo Servicio | Seguimiento</h4>
                        <p class="dt-card-desc">Los campos con <code>*</code> son campos requeridos/obligatorios.</p>
                    </div>

                    <div class="dt-card-body py-8">
                        <h5 class="mb-6 flex items-center gap-2 font-sans text-sm font-bold text-slate-700">
                            <i data-lucide="arrow-right" class="!h-4 !w-4 text-primary-600"></i>
                            Ingresar datos en los campos
                        </h5>

                        <form class="needs-validation mt-4 pt-2" method="post" action="controller/service-new.php">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-0">
                                <div class="mb-4">
                                    <label for="id_Cliente" class="dt-label">Cliente</label>
                                    <select name="id_Cliente" id="id_Cliente" class="dt-select" data-enhanced-select data-search-placeholder="Buscar cliente...">
                                        <option value="">Seleccionar el Cliente</option>
                                        <?php
                                        $sql = "SELECT * FROM clientes ORDER BY nombre_Cliente ASC";
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

                                <div class="mb-4 md:col-span-2">
                                    <label class="dt-label">Tipo de Servicios</label>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5">
                                        <?php
                                        $tipos = [
                                            'instalacion_Tipo' => 'Instalación',
                                            'reparacion_Tipo' => 'Reparación',
                                            'limpieza_Tipo' => 'Limpieza',
                                            'desinfeccion_Tipo' => 'Desinfección',
                                            'sanitizacion_Tipo' => 'Sanitización',
                                            'higienico_Tipo' => 'Entrega Papel Higiénico',
                                            'jabon_Tipo' => 'Entrega de Jabón Liquido',
                                            'otros_Tipo' => 'Otros',
                                            'retiro_Tipo' => 'Retiro de Baños',
                                        ];
                                        foreach ($tipos as $name => $label): ?>
                                            <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 cursor-pointer hover:bg-slate-50 hover:border-primary-300 transition-colors">
                                                <input type="checkbox" value="1" id="<?php echo $name; ?>" name="<?php echo $name; ?>" class="!h-4 !w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                                <span class="font-sans text-xs text-slate-700"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="fecha_Servicio" class="dt-label">Fecha del Servicio</label>
                                    <input class="dt-input" type="date" id="fecha_Servicio" name="fecha_Servicio" required data-datepicker-min="today">
                                </div>

                                <div class="mb-4 md:col-span-2">
                                    <label for="observaciones_Servicio" class="dt-label">Observaciones</label>
                                    <textarea class="dt-input" id="observaciones_Servicio" name="observaciones_Servicio" rows="4" required></textarea>
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
