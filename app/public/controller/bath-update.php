<?php

include "../layouts/config.php";
global $link;

if (isset($_POST['update'])){
    $id_Bath = $_POST['id_Bath'];
    $codigo_Bath = $_POST['codigo_Bath'];
    $fechaCompra_Bath = $_POST['fechaCompra_Bath'];
    $observacion_Bath = $_POST['observacion_Bath'];
    $estado_Bath = $_POST['estado_Bath'];

    $stmt_check = $link->prepare('SELECT COUNT(*) AS total FROM bathrooms WHERE codigo_Bath = ? AND id_Bath != ?');
    $stmt_check->bind_param('si', $codigo_Bath, $id_Bath);
    $stmt_check->execute();
    $existe = $stmt_check->get_result()->fetch_assoc()['total'] > 0;
    $stmt_check->close();

    if ($existe) {
        header('Location: ../dash-bathrooms-edit.php?id_Bath=' . (int) $id_Bath . '&status=error&msg=' . urlencode("Ya existe un baño con el código '$codigo_Bath'. Ingresá un código distinto."));
        $link->close();
        exit;
    }

    $stmt = $link->prepare('UPDATE bathrooms SET codigo_Bath = ?, fechaCompra_Bath = ?, observacion_Bath = ?, estado_Bath = ? WHERE id_Bath = ?');
    $stmt->bind_param('sssii', $codigo_Bath, $fechaCompra_Bath, $observacion_Bath, $estado_Bath, $id_Bath);
    $stmt->execute();
    $stmt->close();

    header('Location: ../dash-bathrooms.php?status=success&msg=' . urlencode('Baño actualizado correctamente'));
}else{
    header('Location: ../dash-bathrooms.php?status=error&msg=' . urlencode('No se pudo actualizar el baño'));
}
// Cerrar la conexión
$link->close();