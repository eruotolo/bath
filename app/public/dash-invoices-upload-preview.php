<?php
session_start();
include 'layouts/session.php';
?>
<?php include 'layouts/head-main.php'; ?>
<?php global $link; include 'layouts/config.php'; ?>
<?php include 'layouts/helpers.php'; ?>

<?php
if (!isset($_SESSION['carga_facturas']) || count($_SESSION['carga_facturas']) === 0) {
    header('Location: dash-invoices-upload.php');
    exit();
}

$filas = $_SESSION['carga_facturas'];

$mensajes_error = [
    'rut_no_encontrado' => 'RUT no encontrado en Clientes',
    'numero_factura_vacio' => 'Falta el número de factura',
    'fecha_invalida' => 'Fecha inválida',
    'monto_invalido' => 'Monto inválido',
];

$cantidad_validas = count(array_filter($filas, fn($f) => $f['error'] === null));
$cantidad_error = count($filas) - $cantidad_validas;
?>

<head>
    <title>Previsualizar Carga de Facturas | Blanco Servicios - Admin & Dashboard Template</title>
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
                    <h1>Previsualizar Carga de Facturas</h1>
                    <ol class="dt-breadcrumb">
                        <li><a href="dash-invoices-list.php">Facturas</a></li>
                        <li class="active">Previsualizar Carga</li>
                    </ol>
                </div>

                <div class="dt-alert dt-alert-info">
                    <strong><?php echo (int) $cantidad_validas; ?></strong> factura(s) lista(s) para cargar.
                    <?php if ($cantidad_error > 0): ?>
                        <strong class="text-rose-600"><?php echo (int) $cantidad_error; ?></strong> fila(s) con error no se van a cargar (marcadas en rojo).
                    <?php endif; ?>
                </div>

                <div class="dt-card">
                    <div class="dt-card-body py-8">

                        <form action="controller/invoice-upload-confirm.php" method="POST" id="form-confirmar">

                            <div class="table-card mb-6 overflow-x-auto">
                                <table class="table align-middle mb-0 w-full">
                                    <thead>
                                        <tr>
                                            <th>Nro Factura</th>
                                            <th>Fecha Factura</th>
                                            <th>Cliente Nombre</th>
                                            <th>Obra</th>
                                            <th class="text-end">Monto Factura</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($filas as $indice => $fila): ?>
                                            <tr class="<?php echo $fila['error'] ? 'bg-rose-50' : ''; ?>">
                                                <td><?php echo htmlspecialchars($fila['numero_Factura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($fila['fecha_Factura'] ?? $fila['fecha_original'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <?php if ($fila['error']): ?>
                                                        <span class="text-rose-600"><?php echo htmlspecialchars($mensajes_error[$fila['error']] ?? 'Error desconocido', ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php if ($fila['error'] === 'rut_no_encontrado'): ?>
                                                            <div class="text-xs text-slate-500">RUT en el archivo: <?php echo htmlspecialchars($fila['rut_original'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <?php echo htmlspecialchars($fila['nombre_Cliente'], ENT_QUOTES, 'UTF-8'); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!$fila['error']): ?>
                                                        <?php if (count($fila['contratos']) === 0): ?>
                                                            <span class="text-rose-600">Sin obras activas para este cliente</span>
                                                        <?php else: ?>
                                                            <select name="obra[<?php echo (int) $indice; ?>]" class="dt-select select-obra" required>
                                                                <option value="">Seleccionar la obra</option>
                                                                <?php foreach ($fila['contratos'] as $contrato): ?>
                                                                    <option value="<?php echo (int) $contrato['id_Contrato']; ?>"><?php echo htmlspecialchars($contrato['obra_Contrato'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end font-mono text-sm"><?php echo is_numeric($fila['valor_Factura']) ? format_clp($fila['valor_Factura']) : htmlspecialchars($fila['valor_Factura'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex gap-2">
                                <a href="controller/invoice-upload-cancel.php" class="dt-btn-secondary">Cancelar</a>
                                <button type="submit" class="dt-btn-add" id="btn-confirmar" disabled>Confirmar Carga</button>
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
    function actualizarBotonConfirmar() {
        var selects = document.querySelectorAll('.select-obra');
        var todasCompletas = true;
        selects.forEach(function (select) {
            if (select.value === '') {
                todasCompletas = false;
            }
        });
        document.getElementById('btn-confirmar').disabled = !todasCompletas || selects.length === 0;
    }

    document.querySelectorAll('.select-obra').forEach(function (select) {
        select.addEventListener('change', actualizarBotonConfirmar);
    });

    actualizarBotonConfirmar();
</script>

</body>
</html>
