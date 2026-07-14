<?php

require '../layouts/config.php';
global $link;

$id_Cliente = intval($_GET['id_Cliente']);
$estado_Cliente = 0;

$sql = "UPDATE clientes SET estado_Cliente = ? WHERE id_Cliente = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, "ii", $estado_Cliente, $id_Cliente);

if (mysqli_stmt_execute($stmt) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-customers.php");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();