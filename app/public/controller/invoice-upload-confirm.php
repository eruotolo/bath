<?php

session_start();
include '../layouts/config.php';
global $link;

if (!isset($_SESSION['carga_facturas'])) {
    header('Location: ../dash-invoices-upload.php');
    exit();
}

$filas = $_SESSION['carga_facturas'];
$obras_seleccionadas = $_POST['obra'] ?? [];

$mensajes_error = [
    'rut_no_encontrado' => 'RUT no encontrado en Clientes',
    'numero_factura_vacio' => 'Falta el número de factura',
    'fecha_invalida' => 'Fecha inválida',
    'monto_invalido' => 'Monto inválido',
];

$cargadas = 0;
$rechazadas = [];

foreach ($filas as $indice => $fila) {
    if ($fila['error'] !== null) {
        $rechazadas[] = [
            'numero_Factura' => $fila['numero_Factura'],
            'motivo' => $mensajes_error[$fila['error']] ?? 'Error desconocido',
        ];
        continue;
    }

    $id_Contrato = isset($obras_seleccionadas[$indice]) ? intval($obras_seleccionadas[$indice]) : 0;

    if ($id_Contrato <= 0) {
        $rechazadas[] = [
            'numero_Factura' => $fila['numero_Factura'],
            'motivo' => 'No se seleccionó una obra',
        ];
        continue;
    }

    $stmtCount = mysqli_prepare($link, "SELECT COUNT(*) AS total FROM facturas WHERE numero_Factura = ?");
    mysqli_stmt_bind_param($stmtCount, "s", $fila['numero_Factura']);
    mysqli_stmt_execute($stmtCount);
    $existe = mysqli_stmt_get_result($stmtCount)->fetch_assoc()['total'] > 0;
    mysqli_stmt_close($stmtCount);

    if ($existe) {
        $rechazadas[] = [
            'numero_Factura' => $fila['numero_Factura'],
            'motivo' => 'Ya existe una factura con ese número',
        ];
        continue;
    }

    $estado_Factura = 1;
    $stmt = mysqli_prepare($link, "INSERT INTO facturas (id_Cliente, id_Contrato, numero_Factura, fecha_Factura, valor_Factura, estado_Factura) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param(
        $stmt,
        "iissii",
        $fila['id_Cliente'],
        $id_Contrato,
        $fila['numero_Factura'],
        $fila['fecha_Factura'],
        $fila['valor_Factura'],
        $estado_Factura
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $cargadas++;
}

$_SESSION['carga_resultado'] = [
    'cargadas' => $cargadas,
    'rechazadas' => $rechazadas,
];

unset($_SESSION['carga_facturas']);

header('Location: ../dash-invoices-upload-result.php');

$link->close();
