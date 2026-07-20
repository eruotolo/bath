<?php

session_start();
include '../layouts/config.php';
include '../layouts/helpers.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;
require_permission('create', 'Invoice');

if (!isset($_FILES['archivo_facturas']) || $_FILES['archivo_facturas']['error'] !== UPLOAD_ERR_OK) {
    log_activity_ctx($link, 'IMPORT', [
        'entidad' => 'Invoice',
        'descripcion' => 'Error al parsear carga de facturas: no se adjuntó archivo',
        'resultado' => 'error',
    ]);
    header('Location: ../dash-invoices-list.php?action=upload&err=sin_archivo');
    exit();
}

$archivo_tmp = $_FILES['archivo_facturas']['tmp_name'];
$extension = strtolower(pathinfo($_FILES['archivo_facturas']['name'], PATHINFO_EXTENSION));

if ($extension !== 'xlsx') {
    log_activity_ctx($link, 'IMPORT', [
        'entidad' => 'Invoice',
        'descripcion' => "Error al parsear carga de facturas: formato inválido ($extension)",
        'resultado' => 'error',
    ]);
    header('Location: ../dash-invoices-list.php?action=upload&err=formato_invalido');
    exit();
}

$filas = leer_xlsx($archivo_tmp);

if ($filas === false || count($filas) < 2) {
    log_activity_ctx($link, 'IMPORT', [
        'entidad' => 'Invoice',
        'descripcion' => 'Error al parsear carga de facturas: archivo sin filas válidas',
        'resultado' => 'error',
    ]);
    header('Location: ../dash-invoices-list.php?action=upload&err=sin_filas');
    exit();
}

// Fila 0 = encabezado, se ignora
array_shift($filas);

// Traer todos los clientes una sola vez y armar un mapa por RUT normalizado
$clientes_por_rut = [];
$result_clientes = mysqli_query($link, "SELECT id_Cliente, nombre_Cliente, rut_Cliente FROM clientes");
while ($cliente = mysqli_fetch_assoc($result_clientes)) {
    $clientes_por_rut[normalizar_rut($cliente['rut_Cliente'])] = $cliente;
}

$filas_procesadas = [];

foreach ($filas as $fila) {
    $rut_original = trim((string)($fila[0] ?? ''));
    $numero_Factura = trim((string)($fila[1] ?? ''));
    $fecha_original = $fila[2] ?? '';
    $monto_original = trim((string)($fila[3] ?? ''));

    $rut_normalizado = normalizar_rut($rut_original);
    $fecha_Factura = excel_a_fecha($fecha_original);
    $valor_Factura = str_replace(['.', ' '], '', $monto_original);

    $error = null;
    $cliente = $clientes_por_rut[$rut_normalizado] ?? null;

    if ($rut_original === '' && $numero_Factura === '') {
        continue; // fila completamente vacía, se ignora sin marcarla como error
    }

    if (!$cliente) {
        $error = 'rut_no_encontrado';
    } elseif ($numero_Factura === '') {
        $error = 'numero_factura_vacio';
    } elseif ($fecha_Factura === null) {
        $error = 'fecha_invalida';
    } elseif ($valor_Factura === '' || !is_numeric($valor_Factura)) {
        $error = 'monto_invalido';
    }

    $contratos = [];
    if ($cliente) {
        $stmt = mysqli_prepare($link, "SELECT id_Contrato, obra_Contrato FROM contratos WHERE id_Cliente = ? AND estado_Contrato = 2");
        mysqli_stmt_bind_param($stmt, "i", $cliente['id_Cliente']);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $contratos = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
    }

    $filas_procesadas[] = [
        'rut_original' => $rut_original,
        'numero_Factura' => $numero_Factura,
        'fecha_original' => (string)$fecha_original,
        'fecha_Factura' => $fecha_Factura,
        'valor_Factura' => $valor_Factura,
        'id_Cliente' => $cliente['id_Cliente'] ?? null,
        'nombre_Cliente' => $cliente['nombre_Cliente'] ?? null,
        'contratos' => $contratos,
        'id_Contrato' => null,
        'error' => $error,
    ];
}

if (count($filas_procesadas) === 0) {
    log_activity_ctx($link, 'IMPORT', [
        'entidad' => 'Invoice',
        'descripcion' => 'Error al parsear carga de facturas: ninguna fila procesada',
        'resultado' => 'error',
    ]);
    header('Location: ../dash-invoices-list.php?action=upload&err=sin_filas');
    exit();
}

$_SESSION['carga_facturas'] = $filas_procesadas;

$filas_ok = 0;
$filas_error = 0;
foreach ($filas_procesadas as $fila_p) {
    if ($fila_p['error'] === null) {
        $filas_ok++;
    } else {
        $filas_error++;
    }
}

log_activity_ctx($link, 'IMPORT', [
    'entidad' => 'Invoice',
    'descripcion' => "Preview de carga de facturas generado (archivo xlsx, $filas_ok filas OK, $filas_error con error)",
    'datos' => ['filas_ok' => $filas_ok, 'filas_error' => $filas_error],
]);

header('Location: ../dash-invoices-list.php');

$link->close();
