<?php

include ('../layouts/config.php');
global $link;

$id_Factura = intval($_GET['id_Factura']);
$estado_Factura = intval($_GET['estado_Factura']);

if (!in_array($estado_Factura, [1, 2, 3], true)) {
    $estado_Factura = 3;
}

$sql = "UPDATE facturas SET estado_Factura = ? WHERE id_Factura = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "ii", $estado_Factura, $id_Factura);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    if ($estado_Factura === 3) {
        // Al anular la factura, se liberan los servicios asociados para que puedan volver a facturarse
        $sqlLiberar = "DELETE FROM factura_servicio WHERE id_Factura = ?";
        $stmtLiberar = mysqli_prepare($link, $sqlLiberar);
        mysqli_stmt_bind_param($stmtLiberar, "i", $id_Factura);
        mysqli_stmt_execute($stmtLiberar);
        mysqli_stmt_close($stmtLiberar);
    }

    header('Location: ../dash-invoices-list.php?status=success&msg=' . urlencode('Estado de la factura actualizado'));
} else {
    header('Location: ../dash-invoices-list.php?status=error&msg=' . urlencode('No se pudo actualizar el estado de la factura'));
}

$link->close();
