<?php

include '../layouts/config.php';
global $link;

if (isset($_POST['editar'])) {
    $id_Factura = intval($_POST['id_Factura']);
    $id_Cliente = intval($_POST['id_Cliente']);
    $id_Contrato = intval($_POST['id_Contrato']);
    $numero_Factura = $_POST['numero_Factura'];
    $fecha_Factura = $_POST['fecha_Factura'];
    $valor_Factura = intval($_POST['valor_Factura']);

    $sql = "UPDATE facturas SET id_Cliente = ?, id_Contrato = ?, numero_Factura = ?, fecha_Factura = ?, valor_Factura = ? WHERE id_Factura = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "iissii", $id_Cliente, $id_Contrato, $numero_Factura, $fecha_Factura, $valor_Factura, $id_Factura);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ../dash-invoices-list.php");
} else {
    header("Location: ../index.php");
}
$link->close();
