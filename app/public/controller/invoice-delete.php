<?php

include "../layouts/config.php";
global $link;

$id_Factura = intval($_GET['id_Factura']);

$sql = "UPDATE facturas SET estado_Factura = 3 WHERE id_Factura = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_Factura);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    // Al anular la factura, se liberan los servicios asociados para que puedan volver a facturarse
    $sqlLiberar = "DELETE FROM factura_servicio WHERE id_Factura = ?";
    $stmtLiberar = mysqli_prepare($link, $sqlLiberar);
    mysqli_stmt_bind_param($stmtLiberar, "i", $id_Factura);
    mysqli_stmt_execute($stmtLiberar);
    mysqli_stmt_close($stmtLiberar);

    header("Location: ../dash-invoices-list.php");
} else {
    echo '<script>alert("No se pudo anular la factura")</script>';
}

$link->close();
