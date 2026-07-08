<?php

include '../layouts/config.php';
global $link;

$id_Factura = intval($_POST['id_Factura']);
$fecha_Pago = trim($_POST['fecha_Pago']);

if ($fecha_Pago !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_Pago)) {
    header("Location: ../dash-invoices-list.php");
    exit();
}

if ($fecha_Pago === '') {
    $sql = "UPDATE facturas SET fecha_Pago = NULL WHERE id_Factura = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_Factura);
} else {
    $sql = "UPDATE facturas SET fecha_Pago = ? WHERE id_Factura = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "si", $fecha_Pago, $id_Factura);
}

mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: ../dash-invoices-list.php");
$link->close();
