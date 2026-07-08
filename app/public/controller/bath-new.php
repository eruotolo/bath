<?php

global $link;

include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $codigo_Bath = $_POST['codigo_Bath'];
    $fechaCompra_Bath = $_POST['fechaCompra_Bath'];
    $observacion_Bath = $_POST['observacion_Bath'];
    $estado_Bath = $_POST['estado_Bath'];

    $stmt_check = $link->prepare('SELECT COUNT(*) AS total FROM bathrooms WHERE codigo_Bath = ?');
    $stmt_check->bind_param('s', $codigo_Bath);
    $stmt_check->execute();
    $existe = $stmt_check->get_result()->fetch_assoc()['total'] > 0;
    $stmt_check->close();

    if ($existe) {
        echo '<script>alert("Ya existe un baño con el código \'' . addslashes($codigo_Bath) . '\'. Ingresá un código distinto.")</script>';
        echo '<script>window.location.href = "../dash-bathrooms-add.php";</script>';
        $link->close();
        exit;
    }

    $stmt = $link->prepare('INSERT INTO bathrooms (codigo_Bath, fechaCompra_Bath, observacion_Bath, estado_Bath) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $codigo_Bath, $fechaCompra_Bath, $observacion_Bath, $estado_Bath);
    $stmt->execute();
    $stmt->close();

    header('Location: ../dash-bathrooms.php');

}else{
    echo '<script>alert("No se pudo crear el cliente")</script>';
}
// Cerrar la conexión
$link->close();